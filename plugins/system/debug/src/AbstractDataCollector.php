<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.Debug
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Debug;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * AbstractDataCollector
 *
 * @since  4.0.0
 */
abstract class AbstractDataCollector extends DataCollector implements Renderable
{
    /**
     * Parameters.
     *
     * @var   Registry
     * @since 4.0.0
     */
    protected $params;

    /**
     * The default formatter.
     *
     * @var   DataFormatter
     * @since 4.0.0
     */
    private static $defaultDataFormatter;

    /**
     * AbstractDataCollector constructor.
     *
     * @param   Registry  $params  Parameters.
     *
     * @since 4.0.0
     */
    public function __construct(Registry $params)
    {
        $this->params = $params;
    }

    /**
     * Get a data formatter.
     *
     * @since  4.0.0
     * @return DataFormatter
     */
    public function getDataFormatter(): DataFormatter
    {
        if ($this->dataFormater === null) {
            $this->dataFormater = self::getDefaultDataFormatter();
        }

        return $this->dataFormater;
    }

    /**
     * Returns the default data formatter
     *
     * @since  4.0.0
     * @return DataFormatter
     */
    public static function getDefaultDataFormatter(): DataFormatter
    {
        if (self::$defaultDataFormatter === null) {
            self::$defaultDataFormatter = new DataFormatter();
        }

        return self::$defaultDataFormatter;
    }

    /**
     * Strip the Joomla! root path.
     *
     * @param   string  $path  The path.
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function formatPath($path): string
    {
        return $this->getDataFormatter()->formatPath($path);
    }

    /**
     * Format a string from back trace.
     *
     * @param   array  $call  The array to format
     *
     * @return string
     *
     * @since  4.0.0
     */
    public function formatCallerInfo(array $call): string
    {
        return $this->getDataFormatter()->formatCallerInfo($call);
    }
}
