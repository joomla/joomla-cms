<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\CMS\Factory;
use Joomla\Plugin\System\Debug\AbstractDataCollector;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * LanguageErrorsDataCollector
 *
 * @since  4.0.0
 */
class LanguageErrorsCollector extends AbstractDataCollector
{
    /**
     * Collector name.
     *
     * @var   string
     * @since 4.0.0
     */
    private $name = 'languageErrors';

    /**
     * The count.
     *
     * @var   integer
     * @since 4.0.0
     */
    private $count = 0;

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @since  4.0.0
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        return [
            'data' => [
                'files'      => $this->getData(),
                'jroot'      => JPATH_ROOT,
                'xdebugLink' => $this->getXdebugLinkTemplate(),
            ],
            'count' => $this->getCount(),
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @since  4.0.0
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
     *
     * @since  4.0.0
     *
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            'errors' => [
                'icon'    => 'bug',
                'widget'  => 'PhpDebugBar.Widgets.ListWidget',
                'map'     => $this->name . '.data.files',
                'default' => '[]',
            ],
            'errors:badge' => [
                'map'     => $this->name . '.count',
                'default' => 'null',
            ],
        ];
    }

    /**
     * Collect data.
     *
     * @return array
     *
     * @since 4.0.0
     */
    private function getData(): array
    {
        $errorFiles = Factory::getLanguage()->getErrorFiles();
        $errors     = [];

        if (\count($errorFiles)) {
            foreach ($errorFiles as $file => $lines) {
                foreach ($lines as $line) {
                    $errors[] = [$file, $line];
                    $this->count++;
                }
            }
        }

        return $errors;
    }

    /**
     * Get a count value.
     *
     * @return int
     *
     * @since 4.0.0
     */
    private function getCount(): int
    {
        return $this->count;
    }
}
