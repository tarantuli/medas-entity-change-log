<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog\Change;

use Jfcherng\Diff\{DiffHelper, Renderer\RendererConstant};
use Medas\Core\{Attributes\ConfigValue, Attributes\Service, Types\Binary};
use Medas\EntityChangeLog\ConfigOptions\DiffContextLines;
use Medas\Json\{JsonEncoder, Settings};

#[Service]
readonly class EntryController
{
    private Settings $settings;

    public function __construct(
        private JsonEncoder $jsonEncoder,

        #[ConfigValue(DiffContextLines::class)]
        private int         $diffContextLines,
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

        $differOptions = [
            'context' => $this->diffContextLines,
            'cliColorization' => RendererConstant::CLI_COLOR_DISABLE,
        ];

        $diff = gzdeflate(DiffHelper::calculate(
            $previousJson . "\n",
            $currentJson . "\n",
            differOptions: $differOptions
        ));

        if (strlen($diff) > Binary::MAX_2_BYTE_LENGTH) {
            $diff = 'too large to store';
        }

        $entry->change = $diff;
    }

    public function getChange(Entry $entry): string|null
    {
        return $entry->change === null ? null : gzinflate($entry->change);
    }
}
