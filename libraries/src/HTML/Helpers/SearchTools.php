<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Searchtools elements.
 *
 * @since  3.2
 */
abstract class SearchTools
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  3.2
     */
    protected static $loaded = [];

    /**
     * Load searchtools for a specific form
     *
     * @param   mixed  $selector  Is debugging mode on? [optional]
     * @param   array  $options   Optional array of parameters for search tools
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function form($selector = '.js-stools-form', $options = [])
    {
        $sig = md5(serialize([$selector, $options]));

        // Only load once
        if (!isset(static::$loaded[__METHOD__][$sig])) {
            // Add the form selector to the search tools options
            $options['formSelector'] = $selector;

            // Generate options with default values
            $options = static::optionsToRegistry($options);

            // Load the script && css files
            Factory::getApplication()->getDocument()->getWebAssetManager()
                ->useStyle('searchtools')
                ->useScript('searchtools');

            Factory::getDocument()->addScriptOptions('searchtools', $options);

            static::$loaded[__METHOD__][$sig] = true;
        }
    }

    /**
     * Function to receive & pre-process javascript options
     *
     * @param   mixed  $options  Associative array/Registry object with options
     *
     * @return  Registry         Options converted to Registry object
     */
    private static function optionsToRegistry($options)
    {
        // Support options array
        if (is_array($options)) {
            $options = new Registry($options);
        }

        if (!($options instanceof Registry)) {
            $options = new Registry();
        }

        return $options;
    }

    /**
     * Method to sort a column in a grid
     *
     * @param   string  $title         The link title
     * @param   string  $order         The order field for the column
     * @param   string  $direction     The current direction
     * @param   mixed   $selected      The selected ordering
     * @param   string  $task          An optional task override
     * @param   string  $newDirection  An optional direction for the new column
     * @param   string  $tip           An optional text shown as tooltip title instead of $title
     * @param   string  $icon          Icon to show
     * @param   string  $formName      Name of the form to submit
     *
     * @return  string
     */
    public static function sort(
        $title,
        $order,
        $direction = 'asc',
        $selected = 0,
        $task = null,
        $newDirection = 'asc',
        $tip = '',
        $icon = null,
        $formName = 'adminForm'
    ) {
        $direction  = strtolower($direction);
        $orderIcons = ['icon-caret-up', 'icon-caret-down'];
        $index      = (int) ($direction === 'desc');

        if ($order !== $selected) {
            $direction = $newDirection;
        } else {
            $direction = $direction === 'desc' ? 'asc' : 'desc';
        }

        // Create an object to pass it to the layouts
        $data            = new \stdClass();
        $data->order     = $order;
        $data->direction = $direction;
        $data->selected  = $selected;
        $data->task      = $task;
        $data->tip       = $tip;
        $data->title     = $title;
        $data->orderIcon = $orderIcons[$index];
        $data->icon      = $icon;
        $data->formName  = $formName;

        return LayoutHelper::render('joomla.searchtools.grid.sort', $data);
    }
}
