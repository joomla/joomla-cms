<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for creating HTML Grids
 *
 * @since  1.6
 */
abstract class JGrid
{
    /**
     * Returns an action on a grid
     *
     * @param   integer       $i              The row index
     * @param   string        $task           The task to fire
     * @param   string|array  $prefix         An optional task prefix or an array of options
     * @param   string        $activeTitle    An optional active tooltip to display if $enable is true
     * @param   string        $inactiveTitle  An optional inactive tooltip to display if $enable is true
     * @param   boolean       $tip            An optional setting for tooltip
     * @param   string        $activeClass    An optional active HTML class
     * @param   string        $inactiveClass  An optional inactive HTML class
     * @param   boolean       $enabled        An optional setting for access control on the action.
     * @param   boolean       $translate      An optional setting for translation.
     * @param   string        $checkbox       An optional prefix for checkboxes.
     * @param   string        $formId         An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @since   1.6
     */
    public static function action(
        $i,
        $task,
        $prefix = '',
        $activeTitle = '',
        $inactiveTitle = '',
        $tip = false,
        $activeClass = '',
        $inactiveClass = '',
        $enabled = true,
        $translate = true,
        $checkbox = 'cb',
        $formId = null
    ) {
        if (is_array($prefix)) {
            $options = $prefix;
            $activeTitle = array_key_exists('active_title', $options) ? $options['active_title'] : $activeTitle;
            $inactiveTitle = array_key_exists('inactive_title', $options) ? $options['inactive_title'] : $inactiveTitle;
            $tip = array_key_exists('tip', $options) ? $options['tip'] : $tip;
            $activeClass = array_key_exists('active_class', $options) ? $options['active_class'] : $activeClass;
            $inactiveClass = array_key_exists('inactive_class', $options) ? $options['inactive_class'] : $inactiveClass;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        if ($tip) {
            $title = $enabled ? $activeTitle : $inactiveTitle;
            $title = $translate ? Text::_($title) : $title;
            $ariaid = $checkbox . $task . $i . '-desc';

            // Don't show empty tooltip.
            if ($title === '') {
                $tip = false;
            }
        }

        if ($enabled) {
            $html[] = '<a class="tbody-icon' . ($activeClass === 'publish' ? ' active' : '') . '"';

            if ($formId !== null) {
                $html[] = ' href="javascript:void(0);" onclick="return Joomla.listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix .
                    $task . '\',\'' . $formId . '\')"';
            } else {
                $html[] = ' href="javascript:void(0);" onclick="return Joomla.listItemTask(\'' . $checkbox . $i . '\',\'' . $prefix . $task . '\')"';
            }

            $html[] = $tip ? ' aria-labelledby="' . $ariaid . '"' : '';
            $html[] = '>';
            $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $activeClass]);
            $html[] = '</a>';
            $html[] = $tip ? '<div role="tooltip" id="' . $ariaid . '">' . $title . '</div>' : '';
        } else {
            $html[] = '<span class="tbody-icon jgrid"';
            $html[] = $tip ? ' aria-labelledby="' . $ariaid . '"' : '';
            $html[] = '>';
            $html[] = LayoutHelper::render('joomla.icon.iconclass', ['icon' => $inactiveClass]);
            $html[] = '</span>';
            $html[] = $tip ? '<div role="tooltip" id="' . $ariaid . '">' . $title . '</div>' : '';
        }

        return implode($html);
    }

    /**
     * Returns a state on a grid
     *
     * @param   array         $states     array of value/state. Each state is an array of the form
     *                                    (task, text, active title, inactive title, tip (boolean), HTML active class, HTML inactive class)
     *                                    or ('task'=>task, 'text'=>text, 'active_title'=>active title,
     *                                    'inactive_title'=>inactive title, 'tip'=>boolean, 'active_class'=>html active class,
     *                                    'inactive_class'=>html inactive class)
     * @param   integer       $value      The state value.
     * @param   integer       $i          The row index
     * @param   string|array  $prefix     An optional task prefix or an array of options
     * @param   boolean       $enabled    An optional setting for access control on the action.
     * @param   boolean       $translate  An optional setting for translation.
     * @param   string        $checkbox   An optional prefix for checkboxes.
     * @param   string        $formId     An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @since   1.6
     */
    public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate = true, $checkbox = 'cb', $formId = null)
    {
        if (is_array($prefix)) {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $translate = array_key_exists('translate', $options) ? $options['translate'] : $translate;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $state = ArrayHelper::getValue($states, (int) $value, $states[0]);
        $task = array_key_exists('task', $state) ? $state['task'] : $state[0];
        $text = array_key_exists('text', $state) ? $state['text'] : (array_key_exists(1, $state) ? $state[1] : '');
        $activeTitle = array_key_exists('active_title', $state) ? $state['active_title'] : (array_key_exists(2, $state) ? $state[2] : '');
        $inactiveTitle = array_key_exists('inactive_title', $state) ? $state['inactive_title'] : (array_key_exists(3, $state) ? $state[3] : '');
        $tip = array_key_exists('tip', $state) ? $state['tip'] : (array_key_exists(4, $state) ? $state[4] : false);
        $activeClass = array_key_exists('active_class', $state) ? $state['active_class'] : (array_key_exists(5, $state) ? $state[5] : '');
        $inactiveClass = array_key_exists('inactive_class', $state) ? $state['inactive_class'] : (array_key_exists(6, $state) ? $state[6] : '');

        return static::action(
            $i,
            $task,
            $prefix,
            $activeTitle,
            $inactiveTitle,
            $tip,
            $activeClass,
            $inactiveClass,
            $enabled,
            $translate,
            $checkbox,
            $formId
        );
    }

    /**
     * Returns a published state on a grid
     *
     * @param   integer       $value        The state value.
     * @param   integer       $i            The row index
     * @param   string|array  $prefix       An optional task prefix or an array of options
     * @param   boolean       $enabled      An optional setting for access control on the action.
     * @param   string        $checkbox     An optional prefix for checkboxes.
     * @param   string        $publishUp    An optional start publishing date.
     * @param   string        $publishDown  An optional finish publishing date.
     * @param   string        $formId       An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @see     JHtmlJGrid::state()
     * @since   1.6
     */
    public static function published(
        $value,
        $i,
        $prefix = '',
        $enabled = true,
        $checkbox = 'cb',
        $publishUp = null,
        $publishDown = null,
        $formId = null
    ) {
        if (is_array($prefix)) {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            1 => array('unpublish', 'JPUBLISHED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JPUBLISHED', true, 'publish', 'publish'),
            0 => array('publish', 'JUNPUBLISHED', 'JLIB_HTML_PUBLISH_ITEM', 'JUNPUBLISHED', true, 'unpublish', 'unpublish'),
            2 => array('unpublish', 'JARCHIVED', 'JLIB_HTML_UNPUBLISH_ITEM', 'JARCHIVED', true, 'archive', 'archive'),
            -2 => array('publish', 'JTRASHED', 'JLIB_HTML_PUBLISH_ITEM', 'JTRASHED', true, 'trash', 'trash'),
        );

        // Special state for dates
        if ($publishUp || $publishDown) {
            $nullDate = Factory::getDbo()->getNullDate();
            $nowDate = Factory::getDate()->toUnix();

            $tz = Factory::getUser()->getTimezone();

            $publishUp = ($publishUp !== null && $publishUp !== $nullDate) ? Factory::getDate($publishUp, 'UTC')->setTimezone($tz) : false;
            $publishDown = ($publishDown !== null && $publishDown !== $nullDate) ? Factory::getDate($publishDown, 'UTC')->setTimezone($tz) : false;

            // Create tip text, only we have publish up or down settings
            $tips = array();

            if ($publishUp) {
                $tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_START', HTMLHelper::_('date', $publishUp, Text::_('DATE_FORMAT_LC5'), 'UTC'));
            }

            if ($publishDown) {
                $tips[] = Text::sprintf('JLIB_HTML_PUBLISHED_FINISHED', HTMLHelper::_('date', $publishDown, Text::_('DATE_FORMAT_LC5'), 'UTC'));
            }

            $tip = empty($tips) ? false : implode('<br>', $tips);

            // Add tips and special titles
            foreach ($states as $key => $state) {
                // Create special titles for published items
                if ($key == 1) {
                    $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';

                    if ($publishUp > $nullDate && $nowDate < $publishUp->toUnix()) {
                        $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
                        $states[$key][5] = $states[$key][6] = 'pending';
                    }

                    if ($publishDown > $nullDate && $nowDate > $publishDown->toUnix()) {
                        $states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
                        $states[$key][5] = $states[$key][6] = 'expired';
                    }
                }

                // Add tips to titles
                if ($tip) {
                    $states[$key][1] = Text::_($states[$key][1]);
                    $states[$key][2] = Text::_($states[$key][2]) . '<br>' . $tip;
                    $states[$key][3] = Text::_($states[$key][3]) . '<br>' . $tip;
                    $states[$key][4] = true;
                }
            }

            return static::state($states, $value, $i, array('prefix' => $prefix, 'translate' => !$tip), $enabled, true, $checkbox, $formId);
        }

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $formId);
    }

    /**
     * Returns an isDefault state on a grid
     *
     * @param   integer       $value             The state value.
     * @param   integer       $i                 The row index
     * @param   string|array  $prefix            An optional task prefix or an array of options
     * @param   boolean       $enabled           An optional setting for access control on the action.
     * @param   string        $checkbox          An optional prefix for checkboxes.
     * @param   string        $formId            An optional form selector.
     * @param   string        $active_class      The class for active items.
     * @param   string        $inactive_class    The class for inactive items.
     *
     * @return  string  The HTML markup
     *
     * @see     JHtmlJGrid::state()
     * @since   1.6
     */
    public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $formId = null, $active_class = 'icon-color-featured icon-star', $inactive_class = 'icon-unfeatured')
    {
        if (is_array($prefix)) {
            $options  = $prefix;
            $enabled  = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix   = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $states = array(
            0 => array('setDefault', '', 'JLIB_HTML_SETDEFAULT_ITEM', '', 1, $inactive_class, $inactive_class),
            1 => array('unsetDefault', 'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM', 'JDEFAULT', 1, $active_class, $active_class),
        );

        return static::state($states, $value, $i, $prefix, $enabled, true, $checkbox, $formId);
    }

    /**
     * Returns an array of standard published state filter options.
     *
     * @param   array  $config  An array of configuration options.
     *                          This array can contain a list of key/value pairs where values are boolean
     *                          and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
     *                          These pairs determine which values are displayed.
     *
     * @return  array  The array of standard published state filter options
     *
     * @since   1.6
     */
    public static function publishedOptions($config = array())
    {
        // Build the active state filter options.
        $options = array();

        if (!array_key_exists('published', $config) || $config['published']) {
            $options[] = HTMLHelper::_('select.option', '1', 'JPUBLISHED');
        }

        if (!array_key_exists('unpublished', $config) || $config['unpublished']) {
            $options[] = HTMLHelper::_('select.option', '0', 'JUNPUBLISHED');
        }

        if (!array_key_exists('archived', $config) || $config['archived']) {
            $options[] = HTMLHelper::_('select.option', '2', 'JARCHIVED');
        }

        if (!array_key_exists('trash', $config) || $config['trash']) {
            $options[] = HTMLHelper::_('select.option', '-2', 'JTRASHED');
        }

        if (!array_key_exists('all', $config) || $config['all']) {
            $options[] = HTMLHelper::_('select.option', '*', 'JALL');
        }

        return $options;
    }

    /**
     * Returns a checked-out icon
     *
     * @param   integer       $i           The row index.
     * @param   string        $editorName  The name of the editor.
     * @param   string        $time        The time that the object was checked out.
     * @param   string|array  $prefix      An optional task prefix or an array of options
     * @param   boolean       $enabled     True to enable the action.
     * @param   string        $checkbox    An optional prefix for checkboxes.
     * @param   string        $formId      An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @since   1.6
     */
    public static function checkedout($i, $editorName, $time, $prefix = '', $enabled = false, $checkbox = 'cb', $formId = null)
    {
        if (is_array($prefix)) {
            $options = $prefix;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        $text = $editorName . '<br>' . HTMLHelper::_('date', $time, Text::_('DATE_FORMAT_LC')) . '<br>' . HTMLHelper::_('date', $time, 'H:i');
        $activeTitle = HTMLHelper::_('tooltipText', Text::_('JLIB_HTML_CHECKIN'), $text, 0);
        $inactiveTitle = HTMLHelper::_('tooltipText', Text::_('JLIB_HTML_CHECKED_OUT'), $text, 0);

        return static::action(
            $i,
            'checkin',
            $prefix,
            html_entity_decode($activeTitle, ENT_QUOTES, 'UTF-8'),
            html_entity_decode($inactiveTitle, ENT_QUOTES, 'UTF-8'),
            true,
            'checkedout',
            'checkedout',
            $enabled,
            false,
            $checkbox,
            $formId
        );
    }

    /**
     * Creates an order-up action icon.
     *
     * @param   integer       $i         The row index.
     * @param   string        $task      An optional task to fire.
     * @param   string|array  $prefix    An optional task prefix or an array of options
     * @param   string        $text      An optional text to display
     * @param   boolean       $enabled   An optional setting for access control on the action.
     * @param   string        $checkbox  An optional prefix for checkboxes.
     * @param   string        $formId    An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @since   1.6
     */
    public static function orderUp($i, $task = 'orderup', $prefix = '', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox = 'cb', $formId = null)
    {
        if (is_array($prefix)) {
            $options = $prefix;
            $text = array_key_exists('text', $options) ? $options['text'] : $text;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        return static::action($i, $task, $prefix, $text, $text, false, 'uparrow', 'uparrow_disabled', $enabled, true, $checkbox, $formId);
    }

    /**
     * Creates an order-down action icon.
     *
     * @param   integer       $i         The row index.
     * @param   string        $task      An optional task to fire.
     * @param   string|array  $prefix    An optional task prefix or an array of options
     * @param   string        $text      An optional text to display
     * @param   boolean       $enabled   An optional setting for access control on the action.
     * @param   string        $checkbox  An optional prefix for checkboxes.
     * @param   string        $formId    An optional form selector.
     *
     * @return  string  The HTML markup
     *
     * @since   1.6
     */
    public static function orderDown(
        $i,
        $task = 'orderdown',
        $prefix = '',
        $text = 'JLIB_HTML_MOVE_DOWN',
        $enabled = true,
        $checkbox = 'cb',
        $formId = null
    ) {
        if (is_array($prefix)) {
            $options = $prefix;
            $text = array_key_exists('text', $options) ? $options['text'] : $text;
            $enabled = array_key_exists('enabled', $options) ? $options['enabled'] : $enabled;
            $checkbox = array_key_exists('checkbox', $options) ? $options['checkbox'] : $checkbox;
            $prefix = array_key_exists('prefix', $options) ? $options['prefix'] : '';
        }

        return static::action($i, $task, $prefix, $text, $text, false, 'downarrow', 'downarrow_disabled', $enabled, true, $checkbox, $formId);
    }
}
