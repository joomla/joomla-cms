<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

/**
 * HTML utility class for building a dropdown menu
 *
 * @since  3.2
 */
abstract class ActionsDropdown
{
    /**
     * @var    string  HTML markup for the dropdown list
     * @since  3.2
     */
    protected static $dropDownList = array();

    /**
     * Method to render current dropdown menu
     *
     * @param   string  $item  An item to render.
     *
     * @return  string  HTML markup for the dropdown list
     *
     * @since   3.2
     */
    public static function render($item = '')
    {
        $html = array();

        $html[] = '<button data-bs-toggle="dropdown" class="dropdown-toggle btn btn-sm btn-secondary">';
        $html[] = '<span class="caret"></span>';

        if ($item) {
            $html[] = '<span class="visually-hidden">' . Text::sprintf('JACTIONS', $item) . '</span>';
        }

        $html[] = '</button>';
        $html[] = '<ul class="dropdown-menu">';
        $html[] = implode('', static::$dropDownList);
        $html[] = '</ul>';

        static::$dropDownList = null;

        return implode('', $html);
    }

    /**
     * Append a publish item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function publish($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'publish';
        static::addCustomItem(Text::_('JTOOLBAR_PUBLISH'), 'publish', $id, $task);
    }

    /**
     * Append an unpublish item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function unpublish($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'unpublish';
        static::addCustomItem(Text::_('JTOOLBAR_UNPUBLISH'), 'unpublish', $id, $task);
    }

    /**
     * Append a feature item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function feature($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'featured';
        static::addCustomItem(Text::_('JFEATURE'), 'featured', $id, $task);
    }

    /**
     * Append an unfeature item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function unfeature($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'unfeatured';
        static::addCustomItem(Text::_('JUNFEATURE'), 'unfeatured', $id, $task);
    }

    /**
     * Append an archive item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function archive($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'archive';
        static::addCustomItem(Text::_('JTOOLBAR_ARCHIVE'), 'archive', $id, $task);
    }

    /**
     * Append an unarchive item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function unarchive($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'unpublish';
        static::addCustomItem(Text::_('JTOOLBAR_UNARCHIVE'), 'unarchive', $id, $task);
    }

    /**
     * Append a duplicate item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function duplicate($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'duplicate';
        static::addCustomItem(Text::_('JTOOLBAR_DUPLICATE'), 'copy', $id, $task);
    }

    /**
     * Append a trash item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function trash($id, $prefix = '')
    {
        $task = ($prefix ? $prefix . '.' : '') . 'trash';
        static::addCustomItem(Text::_('JTOOLBAR_TRASH'), 'trash', $id, $task);
    }

    /**
     * Append an untrash item to the current dropdown menu
     *
     * @param   string  $id      ID of corresponding checkbox of the record
     * @param   string  $prefix  The task prefix
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function untrash($id, $prefix = '')
    {
        self::publish($id, $prefix);
    }

    /**
     * Writes a divider between dropdown items
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function divider()
    {
        static::$dropDownList[] = '<li class="divider"></li>';
    }

    /**
     * Append a custom item to current dropdown menu.
     *
     * @param   string  $label  The label of the item.
     * @param   string  $icon   The icon classname.
     * @param   string  $id     The item id.
     * @param   string  $task   The task.
     *
     * @return  void
     *
     * @since   3.2
     */
    public static function addCustomItem($label, $icon = '', $id = '', $task = '')
    {
        static::$dropDownList[] = '<li>'
            . HTMLHelper::link(
                'javascript://',
                ($icon ? LayoutHelper::render('joomla.icon.iconclass', ['icon' => $icon]) : '') . $label,
                [
                    'onclick' => 'Joomla.listItemTask(\'' . $id . '\', \'' . $task . '\')'
                ]
            )
            . '</li>';
    }
}
