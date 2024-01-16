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
use Joomla\Plugin\System\Debug\Extension\Debug;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * SessionDataCollector
 *
 * @since  4.0.0
 */
class SessionCollector extends AbstractDataCollector
{
    /**
     * Collector name.
     *
     * @var   string
     * @since 4.0.0
     */
    private $name = 'session';

    /**
     * Collected data.
     *
     * @var   array
     * @since 4.4.0
     */
    protected $sessionData;

    /**
     * Constructor.
     *
     * @param   Registry  $params   Parameters.
     * @param   bool      $collect  Collect the session data.
     *
     * @since 4.4.0
     */
    public function __construct($params, $collect = false)
    {
        parent::__construct($params);

        if ($collect) {
            $this->collect();
        }
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @param   bool  $overwrite  Overwrite the previously collected session data.
     *
     * @return array Collected data
     *
     * @since  4.0.0
     */
    public function collect($overwrite = false)
    {
        if ($this->sessionData === null || $overwrite) {
            $this->sessionData  = [];
            $data               = Factory::getApplication()->getSession()->all();

            // redact value of potentially secret keys
            array_walk_recursive($data, static function (&$value, $key) {
                if (!preg_match(Debug::PROTECTED_COLLECTOR_KEYS, $key)) {
                    return;
                }

                $value = '***redacted***';
            });

            foreach ($data as $key => $value) {
                $this->sessionData[$key] = $this->getDataFormatter()->formatVar($value);
            }
        }

        return ['data' => $this->sessionData];
    }

    /**
     * Returns the unique name of the collector
     *
     * @since  4.0.0
     *
     * @return string
     */
    public function getName()
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
    public function getWidgets()
    {
        return [
            'session' => [
                'icon'    => 'key',
                'widget'  => 'PhpDebugBar.Widgets.VariableListWidget',
                'map'     => $this->name . '.data',
                'default' => '[]',
            ],
        ];
    }
}
