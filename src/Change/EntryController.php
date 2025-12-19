<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Jfcherng\Diff\{DiffHelper, Renderer\RendererConstant};
use Medas\Core\{Attributes\Service, Types\Binary};
use Medas\Json\{JsonEncoder, Settings};

#[Service]
readonly class EntryController
{
    private const array DIFFER_OPTIONS = [
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
        $previousJson = $this->jsonEncoder->encode($previous, $this->settings);
        $previousJson = str_replace('\n', "\n", $previousJson);
        $currentJson = $this->jsonEncoder->encode($current, $this->settings);
        $currentJson = str_replace('\n', "\n", $currentJson);

        $diff = gzdeflate(DiffHelper::calculate(
            $previousJson . "\n",
            $currentJson . "\n",
            differOptions: self::DIFFER_OPTIONS
        ));

        if (strlen($diff) > Binary::MAX_2_BYTE_LENGTH) {
            $diff = 'too large to store';
        }

        $entry->change = $diff;
    }

    public function getChange(Entry $entry): string
    {
        return gzinflate($entry->change);
    }
}
