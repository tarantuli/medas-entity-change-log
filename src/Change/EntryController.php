<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Jfcherng\Diff\{DiffHelper, Renderer\RendererConstant};
use Medas\Core\Attributes\Service;
use Medas\Json\{JsonEncoder, Settings};

#[Service]
readonly class EntryController
{
    private const DIFFER_OPTIONS = [
        'context' => 1,
        'cliColorization' => RendererConstant::CLI_COLOR_DISABLE,
    ];

    private Settings $settings;

    public function __construct(
        private JsonEncoder $jsonEncoder,
    )
    {
        $this->settings = new Settings(prettyPrint: true);
    }

    public function setChange(Entry $entry, mixed $previous, mixed $current): void
    {
        $previous = $this->jsonEncoder->encode($previous, $this->settings);
        $current = $this->jsonEncoder->encode($current, $this->settings);

        $diff = gzdeflate(DiffHelper::calculate(
            $previous . "\n",
            $current . "\n",
            differOptions: self::DIFFER_OPTIONS
        ));

        if (strlen($current) <= strlen($diff)) {
            $currentDeflated = gzdeflate($current);

            if (strlen($currentDeflated) < strlen($current)) {
                $entry->changeType = ChangeType::DeflatedValue;
                $entry->change = $currentDeflated;
            }
            else {
                $entry->changeType = ChangeType::NewValue;
                $entry->change = $current;
            }
        }
        else {
            $entry->changeType = ChangeType::Diff;
            $entry->change = $diff;
        }
    }

    public function getChange(Entry $entry): mixed
    {
        switch ($entry->changeType) {
            case ChangeType::NewValue:
                return $this->jsonEncoder->decode($entry->change);

            case ChangeType::DeflatedValue:
                return $this->jsonEncoder->decode(gzinflate($entry->change));

            case ChangeType::Diff:
                return gzinflate($entry->change);
        }

        throw new \Exception('unknown Entry change type ' . $entry->changeType->name);
    }
}
