<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for the Joomla core UI Tab element.
 *
 * @since  4.0.0
 */
abstract class UiTab
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  4.0.0
     */
    protected static $loaded = [];

    /**
     * Creates a core UI tab pane
     *
     * @param   string  $selector  The pane identifier.
     * @param   array   $params    The parameters for the pane
     *
     * @return  string
     *
     * @since   4.0.0
     */
    public static function startTabSet($selector = 'myTab', $params = [])
    {
        $sig = md5(serialize([$selector, $params]));

        if (!isset(static::$loaded[__METHOD__][$sig])) {
            // Include the custom element
            Factory::getDocument()->getWebAssetManager()
                ->useStyle('webcomponent.joomla-tab')
                ->useScript('webcomponent.joomla-tab');

            // Setup options object
            $opt = ['active' => (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : ''];

            // Set static array
            static::$loaded[__METHOD__][$sig]                = true;
            static::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
        }

        $orientation = $params['orientation'] ?? 'horizontal';
        $recall      = isset($params['recall']) ? 'recall' : '';
        $breakpoint  = isset($params['breakpoint']) ? 'breakpoint="' . $params['breakpoint'] . '"' : '';

        if (!isset($params['breakpoint']) && $breakpoint === '') {
            $breakpoint = 'breakpoint="768"';
        }

        return '<joomla-tab id="' . $selector . '" orientation="' . $orientation . '" ' . $recall . ' ' . $breakpoint . '>';
    }

    /**
     * Close the current tab pane
     *
     * @return  string  HTML to close the pane
     *
     * @since   4.0.0
     */
    public static function endTabSet()
    {
        return '</joomla-tab>';
    }

    /**
     * Begins the display of a new tab content panel.
     *
     * @param   string  $selector  Identifier of the panel.
     * @param   string  $id        The ID of the div element
     * @param   string  $title     The title text for the button
     *
     * @return  string  HTML to start a new panel
     *
     * @since   4.0.0
     */
    public static function addTab($selector, $id, $title)
    {
        $active = (static::$loaded[__CLASS__ . '::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

        return '<joomla-tab-element id="' . $id . '"' . $active . ' name="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '">';
    }

    /**
     * Close the current tab content panel
     *
     * @return  string  HTML to close the pane
     *
     * @since   4.0.0
     */
    public static function endTab()
    {
        return '</joomla-tab-element>';
    }
}
