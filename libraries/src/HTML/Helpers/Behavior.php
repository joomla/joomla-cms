<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('JPATH_PLATFORM') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Utility class for JavaScript behaviors
 *
 * @since  1.5
 */
abstract class Behavior
{
    /**
     * Array containing information for loaded files
     *
     * @var    array
     * @since  2.5
     */
    protected static $loaded = [];

    /**
     * Method to load core.js into the document head.
     *
     * Core.js defines the 'Joomla' namespace and contains functions which are used across extensions
     *
     * @return  void
     *
     * @since   3.3
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the webasset manager instead
     *              Example: Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('core');
     */
    public static function core()
    {
        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('core');
    }

    /**
     * Add unobtrusive JavaScript support for form validation.
     *
     * To enable form validation the form tag must have class="form-validate".
     * Each field that needs to be validated needs to have class="validate".
     * Additional handlers can be added to the handler for username, password,
     * numeric and email. To use these add class="validate-email" and so on.
     *
     * @return  void
     *
     * @since   3.4
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the webasset manager instead
     *              Example: Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('form.validate');
     */
    public static function formvalidator()
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__])) {
            return;
        }

        Factory::getDocument()->getWebAssetManager()->useScript('form.validate');

        static::$loaded[__METHOD__] = true;
    }

    /**
     * Add unobtrusive JavaScript support for a combobox effect.
     *
     * Note that this control is only reliable in absolutely positioned elements.
     * Avoid using a combobox in a slider or dynamic pane.
     *
     * @return  void
     *
     * @since   1.5
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the webasset manager instead
     *              Example: Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('awesomeplete');
     */
    public static function combobox()
    {
        Factory::getDocument()->getWebAssetManager()->usePreset('awesomplete');
    }

    /**
     * JavaScript behavior to allow shift select in grids
     *
     * @param   string  $id  The id of the form for which a multiselect behaviour is to be applied.
     *
     * @return  void
     *
     * @since   1.7
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the webasset manager instead
     *              Example:
     *              Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('multiselect');
     *              Factory::getDocument()->addScriptOptions('js-multiselect', ['formName' => $id]);
     */
    public static function multiselect($id = 'adminForm')
    {
        // Only load once
        if (isset(static::$loaded[__METHOD__][$id])) {
            return;
        }

        Factory::getDocument()->getWebAssetManager()->useScript('multiselect');

        // Pass the required options to the javascript
        Factory::getDocument()->addScriptOptions('js-multiselect', ['formName' => $id]);

        // Set static array
        static::$loaded[__METHOD__][$id] = true;
    }

    /**
     * Keep session alive, for example, while editing or creating an article.
     *
     * @return  void
     *
     * @since   1.5
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the webasset manager instead
     *              Example: Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('keepalive');
     */
    public static function keepalive()
    {
        Factory::getApplication()->getDocument()->getWebAssetManager()->useScript('keepalive');
    }

    /**
     * Highlight some words via Javascript.
     *
     * @param   array   $terms      Array of words that should be highlighted.
     * @param   string  $start      ID of the element that marks the begin of the section in which words
     *                              should be highlighted. Note this element will be removed from the DOM.
     * @param   string  $end        ID of the element that end this section.
     *                              Note this element will be removed from the DOM.
     * @param   string  $className  Class name of the element highlights are wrapped in.
     * @param   string  $tag        Tag that will be used to wrap the highlighted words.
     *
     * @return  void
     *
     * @since   2.5
     *
     * @deprecated  4.0 will be removed in 6.0
     *              Use the script directly
     */
    public static function highlighter(array $terms, $start = 'highlighter-start', $end = 'highlighter-end', $className = 'highlight', $tag = 'span')
    {
        $terms = array_filter($terms, 'strlen');

        if (!empty($terms)) {
            $doc = Factory::getDocument();

            $doc->getWebAssetManager()->useScript('highlight');
            $doc->addScriptOptions(
                'highlight',
                [[
                    'class'         => 'js-highlight',
                    'highLight'     => $terms,
                    'compatibility' => true,
                    'start'         => $start,
                    'end'           => $end,
                ]]
            );
        }
    }

    /**
     * Add javascript polyfills.
     *
     * @param   string|array  $polyfillTypes       The polyfill type(s). Examples: event, array('event', 'classlist').
     * @param   string        $conditionalBrowser  An IE conditional expression. Example: lt IE 9 (lower than IE 9).
     *
     * @return  void
     *
     * @since   3.7.0
     */
    public static function polyfill($polyfillTypes = null, $conditionalBrowser = null)
    {
        if ($polyfillTypes === null) {
            return;
        }

        foreach ((array) $polyfillTypes as $polyfillType) {
            $sig = md5(serialize([$polyfillType, $conditionalBrowser]));

            // Only load once
            if (isset(static::$loaded[__METHOD__][$sig])) {
                continue;
            }

            // If include according to browser.
            $scriptOptions = ['version' => 'auto', 'relative' => true];
            $scriptOptions = $conditionalBrowser !== null ? array_replace($scriptOptions, ['conditional' => $conditionalBrowser]) : $scriptOptions;

            /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wa->registerAndUseScript('polyfill.' . $polyfillType, 'vendor/polyfills/polyfill-' . $polyfillType . '.js', $scriptOptions);

            // Set static array
            static::$loaded[__METHOD__][$sig] = true;
        }
    }

    /**
     * Internal method to translate the JavaScript Calendar
     *
     * @return  string  JavaScript that translates the object
     *
     * @since   1.5
     */
    protected static function calendartranslation()
    {
        static $jsscript = 0;

        // Guard clause, avoids unnecessary nesting
        if ($jsscript) {
            return false;
        }

        $jsscript = 1;

        // To keep the code simple here, run strings through Text::_() using array_map()
        $callback      = ['Text', '_'];
        $weekdays_full = array_map(
            $callback,
            [
                'SUNDAY', 'MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY', 'SATURDAY', 'SUNDAY',
            ]
        );
        $weekdays_short = array_map(
            $callback,
            [
                'SUN', 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN',
            ]
        );
        $months_long = array_map(
            $callback,
            [
                'JANUARY', 'FEBRUARY', 'MARCH', 'APRIL', 'MAY', 'JUNE',
                'JULY', 'AUGUST', 'SEPTEMBER', 'OCTOBER', 'NOVEMBER', 'DECEMBER',
            ]
        );
        $months_short = array_map(
            $callback,
            [
                'JANUARY_SHORT', 'FEBRUARY_SHORT', 'MARCH_SHORT', 'APRIL_SHORT', 'MAY_SHORT', 'JUNE_SHORT',
                'JULY_SHORT', 'AUGUST_SHORT', 'SEPTEMBER_SHORT', 'OCTOBER_SHORT', 'NOVEMBER_SHORT', 'DECEMBER_SHORT',
            ]
        );

        // This will become an object in Javascript but define it first in PHP for readability
        $today = " " . Text::_('JLIB_HTML_BEHAVIOR_TODAY') . " ";
        $text  = [
            'INFO'  => Text::_('JLIB_HTML_BEHAVIOR_ABOUT_THE_CALENDAR'),
            'ABOUT' => "DHTML Date/Time Selector\n"
                . "(c) dynarch.com 20022005 / Author: Mihai Bazon\n"
                . "For latest version visit: http://www.dynarch.com/projects/calendar/\n"
                . "Distributed under GNU LGPL.  See http://gnu.org/licenses/lgpl.html for details."
                . "\n\n"
                . Text::_('JLIB_HTML_BEHAVIOR_DATE_SELECTION')
                . Text::_('JLIB_HTML_BEHAVIOR_YEAR_SELECT')
                . Text::_('JLIB_HTML_BEHAVIOR_MONTH_SELECT')
                . Text::_('JLIB_HTML_BEHAVIOR_HOLD_MOUSE'),
            'ABOUT_TIME' => "\n\n"
                . "Time selection:\n"
                . " Click on any of the time parts to increase it\n"
                . " or Shiftclick to decrease it\n"
                . " or click and drag for faster selection.",
            'PREV_YEAR'       => Text::_('JLIB_HTML_BEHAVIOR_PREV_YEAR_HOLD_FOR_MENU'),
            'PREV_MONTH'      => Text::_('JLIB_HTML_BEHAVIOR_PREV_MONTH_HOLD_FOR_MENU'),
            'GO_TODAY'        => Text::_('JLIB_HTML_BEHAVIOR_GO_TODAY'),
            'NEXT_MONTH'      => Text::_('JLIB_HTML_BEHAVIOR_NEXT_MONTH_HOLD_FOR_MENU'),
            'SEL_DATE'        => Text::_('JLIB_HTML_BEHAVIOR_SELECT_DATE'),
            'DRAG_TO_MOVE'    => Text::_('JLIB_HTML_BEHAVIOR_DRAG_TO_MOVE'),
            'PART_TODAY'      => $today,
            'DAY_FIRST'       => Text::_('JLIB_HTML_BEHAVIOR_DISPLAY_S_FIRST'),
            'WEEKEND'         => Factory::getLanguage()->getWeekEnd(),
            'CLOSE'           => Text::_('JLIB_HTML_BEHAVIOR_CLOSE'),
            'TODAY'           => Text::_('JLIB_HTML_BEHAVIOR_TODAY'),
            'TIME_PART'       => Text::_('JLIB_HTML_BEHAVIOR_SHIFT_CLICK_OR_DRAG_TO_CHANGE_VALUE'),
            'DEF_DATE_FORMAT' => "%Y%m%d",
            'TT_DATE_FORMAT'  => Text::_('JLIB_HTML_BEHAVIOR_TT_DATE_FORMAT'),
            'WK'              => Text::_('JLIB_HTML_BEHAVIOR_WK'),
            'TIME'            => Text::_('JLIB_HTML_BEHAVIOR_TIME'),
        ];

        return 'Calendar._DN = ' . json_encode($weekdays_full) . ';'
            . ' Calendar._SDN = ' . json_encode($weekdays_short) . ';'
            . ' Calendar._FD = 0;'
            . ' Calendar._MN = ' . json_encode($months_long) . ';'
            . ' Calendar._SMN = ' . json_encode($months_short) . ';'
            . ' Calendar._TT = ' . json_encode($text) . ';';
    }
}
