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
 * LanguageFilesDataCollector
 *
 * @since  4.0.0
 */
class LanguageFilesCollector extends AbstractDataCollector
{
    /**
     * Collector name.
     *
     * @var   string
     * @since 4.0.0
     */
    private $name = 'languageFiles';

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
        $paths  = Factory::getLanguage()->getPaths();
        $loaded = [];

        foreach ($paths as $extension => $files) {
            $loaded[$extension] = [];

            foreach ($files as $file => $status) {
                $loaded[$extension][$file] = $status;

                if ($status) {
                    $this->count++;
                }
            }
        }

        return [
            'loaded'     => $loaded,
            'xdebugLink' => $this->getXdebugLinkTemplate(),
            'jroot'      => JPATH_ROOT,
            'count'      => $this->count,
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
            'loaded' => [
                'icon'    => 'file-code',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => $this->name . '.loaded',
                'default' => '{}',
            ],
            'loaded:badge' => [
                'map'     => $this->name . '.count',
                'default' => 'null',
            ],
        ];
    }

}
