<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug\DataCollector;

use Joomla\Plugin\System\Debug\AbstractDataCollector;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Collects info about the request duration as well as providing
 * a way to log duration of any operations
 *
 * @since  4.4.0
 */
class MemoryCollector extends AbstractDataCollector
{
    /**
     * @var   boolean
     * @since 4.4.0
     */
    protected $realUsage = false;

    /**
     * @var    float
     * @since 4.4.0
     */
    protected $peakUsage = 0;

    /**
     * @param   Registry  $params Parameters.
     * @param   float     $peakUsage
     * @param   boolean   $realUsage
     *
     * @since 4.4.0
     */
    public function __construct(Registry $params, $peakUsage = null, $realUsage = null)
    {
        parent::__construct($params);

        if ($peakUsage !== null) {
            $this->peakUsage = $peakUsage;
        }

        if ($realUsage !== null) {
            $this->realUsage = $realUsage;
        }
    }

    /**
     * Returns whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
     *
     * @return boolean
     *
     * @since 4.4.0
     */
    public function getRealUsage()
    {
        return $this->realUsage;
    }

    /**
     * Sets whether total allocated memory page size is used instead of actual used memory size
     * by the application.  See $real_usage parameter on memory_get_peak_usage for details.
     *
     * @param boolean $realUsage
     *
     * @since 4.4.0
     */
    public function setRealUsage($realUsage)
    {
        $this->realUsage = $realUsage;
    }

    /**
     * Returns the peak memory usage
     *
     * @return integer
     *
     * @since 4.4.0
     */
    public function getPeakUsage()
    {
        return $this->peakUsage;
    }

    /**
     * Updates the peak memory usage value
     *
     * @since 4.4.0
     */
    public function updatePeakUsage()
    {
        if ($this->peakUsage === null) {
            $this->peakUsage = memory_get_peak_usage($this->realUsage);
        }
    }

    /**
     * @return array
     *
     * @since 4.4.0
     */
    public function collect()
    {
        $this->updatePeakUsage();

        return [
            'peak_usage'     => $this->peakUsage,
            'peak_usage_str' => $this->getDataFormatter()->formatBytes($this->peakUsage, 3),
        ];
    }

    /**
     * @return string
     *
     * @since 4.4.0
     */
    public function getName()
    {
        return 'memory';
    }

    /**
     * @return array
     *
     * @since 4.4.0
     */
    public function getWidgets()
    {
        return [
            'memory' => [
                'icon'    => 'cogs',
                'tooltip' => 'Memory Usage',
                'map'     => 'memory.peak_usage_str',
                'default' => "'0B'",
            ],
        ];
    }
}
