<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\FileLayout;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for health checks.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class HealthChecks
{
    /**
     * Method to generate html code for an array of gauges
     *
     * @param   array  $gauges  Array of gauges
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function gauges($gauges)
    {
        if (empty($gauges)) {
            return '';
        }

        $html = [];

        foreach ($gauges as $gauge) {
            $html[] = HTMLHelper::_('healthchecks.gauge', $gauge);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a gauge
     *
     * @param   array  $gauge  Gauge properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function gauge($gauge)
    {
        if (!static::canAccess($gauge)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.gauge');

        return $layout->render($gauge);
    }

    /**
     * Method to generate html code for an array of buttons
     *
     * @param   array  $buttons  Array of buttons
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function buttons($buttons)
    {
        if (empty($buttons)) {
            return '';
        }

        $html = [];

        foreach ($buttons as $button) {
            $html[] = HTMLHelper::_('healthchecks.button', $button);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a button
     *
     * @param   array  $button  Button properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function button($button)
    {
        if (!static::canAccess($button)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.icon');

        return $layout->render($button);
    }

    /**
     * Method to generate html code for an array of tables
     *
     * @param   array  $tables  Array of tables
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function tables($tables)
    {
        if (empty($tables)) {
            return '';
        }

        $html = [];

        foreach ($tables as $table) {
            $html[] = HTMLHelper::_('healthchecks.table', $table);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a table
     *
     * @param   array  $table  Table properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function table($table)
    {
        if (!static::canAccess($table)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.table');

        return $layout->render($table);
    }

    /**
     * Method to generate html code for an array of lists
     *
     * @param   array  $lists  Array of lists
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function lists($lists)
    {
        if (empty($lists)) {
            return '';
        }

        $html = [];

        foreach ($lists as $list) {
            $html[] = HTMLHelper::_('healthchecks.list', $list);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for a list
     *
     * @param   array  $list  List properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function list($list)
    {
        if (!static::canAccess($list)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.list');

        return $layout->render($list);
    }

    /**
     * Method to generate html code for an array of leading information
     *
     * @param   array  $leadings  Array of leading information
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function leadings($leadings)
    {
        if (empty($leadings)) {
            return '';
        }

        $html = [];

        foreach ($leadings as $leading) {
            $html[] = HTMLHelper::_('healthchecks.leading', $leading);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for leading information
     *
     * @param   array  $leading  Leading properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function leading($leading)
    {
        if (!static::canAccess($leading)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.leading');

        return $layout->render($leading);
    }

    /**
     * Method to generate html code for an array of footer information
     *
     * @param   array  $footers  Array of footer information
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function footers($footers)
    {
        if (empty($footers)) {
            return '';
        }

        $html = [];

        foreach ($footers as $footer) {
            $html[] = HTMLHelper::_('healthchecks.footer', $footer);
        }

        return implode($html);
    }

    /**
     * Method to generate html code for footer information
     *
     * @param   array  $footer  Footer properties
     *
     * @return  string
     *
     * @since   __DEPLOY_VERSION__
     */
    public static function footer($footer)
    {
        if (!static::canAccess($footer)) {
            return '';
        }

        // Instantiate a new FileLayout instance and render the layout
        $layout = new FileLayout('joomla.healthchecks.footer');

        return $layout->render($footer);
    }

    /**
     * Check if an item can be accessed based on access permissions
     *
     * @param   array  $item  Item with access properties
     *
     * @return  bool  True if access is allowed, false otherwise
     *
     * @since   __DEPLOY_VERSION__
     */
    protected static function canAccess($item)
    {
        if (!isset($item['access'])) {
            return true;
        }

        if (\is_bool($item['access'])) {
            return $item['access'];
        }

        // Get the user object to verify permissions
        $user = Factory::getApplication()->getIdentity();

        // Take each pair of permission, context values.
        for ($i = 0, $n = \count($item['access']); $i < $n; $i += 2) {
            if (!$user->authorise($item['access'][$i], $item['access'][$i + 1])) {
                return false;
            }
        }

        return true;
    }
}
