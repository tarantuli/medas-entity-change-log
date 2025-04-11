<?php

declare(strict_types=1);

namespace Medas\EntityChangeLog;

use Jfcherng\Diff\{DiffHelper, Renderer\RendererConstant};
use Medas\Core\Attributes\Service;
use Medas\EntityManager\Snapshots\PropertyChange;
use Medas\Json\{JsonEncoder, Settings};

#[Service]
readonly class PropertyChangeProcessor
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

    public function process(PropertyChange $propertyChange, Change\Entry $entry): void
    {
        $previous = $this->jsonEncoder->encode($propertyChange->previous, $this->settings);
        $current = $this->jsonEncoder->encode($propertyChange->current, $this->settings);

        $diff = gzdeflate(DiffHelper::calculate(
            $previous . "\n",
            $current . "\n",
            differOptions: self::DIFFER_OPTIONS
        ));

        if (strlen($current) <= strlen($diff)) {
            $entry->changeType = Change\ChangeType::NewValue;
            $entry->change = $current;
        }
        else {
            $entry->changeType = Change\ChangeType::Diff;
            $entry->change = $diff;
        }
    }
}
