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
        ];

        // cliColorization is a renderer option; left at its default (auto) it adds ANSI colour codes
        // whenever the change is logged from a terminal, e.g. while seeding from the console
        $rendererOptions = [
            'cliColorization' => RendererConstant::CLI_COLOR_DISABLE,
        ];

        $diff = gzdeflate(DiffHelper::calculate(
            $previousJson . "\n",
            $currentJson . "\n",
            differOptions: $differOptions,
            rendererOptions: $rendererOptions
        ));

        // The marker is deflated as well, so getChange() can inflate every stored value alike
        if (strlen($diff) > Binary::MAX_2_BYTE_LENGTH) {
            $diff = gzdeflate('too large to store');
        }

        $entry->change = $diff;
    }

    public function getChange(Entry $entry): string|null
    {
        if ($entry->change === null) {
            return null;
        }

        $change = gzinflate($entry->change);

        // Entries stored before the colourisation fix may still hold ANSI colour codes
        return preg_replace('/\e\[[0-9;]*m/', '', $change);
    }
}
