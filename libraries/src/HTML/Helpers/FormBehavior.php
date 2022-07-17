<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;

/**
 * Utility class for form related behaviors
 *
 * @since       3.0
 *
 * @deprecated  5.0  Without replacement
 */
abstract class FormBehavior
{
    /**
     * @var    array  Array containing information for loaded files
     * @since  3.0
     */
    protected static $loaded = array();

    /**
     * Method to load the Chosen JavaScript framework and supporting CSS into the document head
     *
     * If debugging mode is on an uncompressed version of Chosen is included for easier debugging.
     *
     * @param   string  $selector  Class for Chosen elements.
     * @param   mixed   $debug     Is debugging mode on? [optional]
     * @param   array   $options   the possible Chosen options as name => value [optional]
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function chosen($selector = '.advancedSelect', $debug = null, $options = array())
    {
        if (isset(static::$loaded[__METHOD__][$selector])) {
            return;
        }

        // If no debugging value is set, use the configuration setting
        if ($debug === null) {
            $debug = JDEBUG;
        }

        // Default settings
        if (!isset($options['disable_search_threshold'])) {
            $options['disable_search_threshold'] = 10;
        }

        // Allow searching contains space in query
        if (!isset($options['search_contains'])) {
            $options['search_contains'] = true;
        }

        if (!isset($options['allow_single_deselect'])) {
            $options['allow_single_deselect'] = true;
        }

        if (!isset($options['placeholder_text_multiple'])) {
            $options['placeholder_text_multiple'] = Text::_('JGLOBAL_TYPE_OR_SELECT_SOME_OPTIONS');
        }

        if (!isset($options['placeholder_text_single'])) {
            $options['placeholder_text_single'] = Text::_('JGLOBAL_SELECT_AN_OPTION');
        }

        if (!isset($options['no_results_text'])) {
            $options['no_results_text'] = Text::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');
        }

        // Options array to json options string
        $options_str = \json_encode($options, ($debug && \defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false));

        // Add chosen.js assets

        /** @var \Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
        $wa->usePreset('chosen')
            ->registerAndUseScript('joomla-chosen', 'legacy/joomla-chosen.min.js', [], [], ['chosen'])
            ->addInlineScript(
                "
		jQuery(document).ready(function (){
			jQuery('" . $selector . "').jchosen(" . $options_str . ");
		});
	"
            );

        static::$loaded[__METHOD__][$selector] = true;
    }

    /**
     * Method to load the AJAX Chosen library
     *
     * If debugging mode is on an uncompressed version of AJAX Chosen is included for easier debugging.
     *
     * @param   Registry  $options  Options in a Registry object
     * @param   mixed     $debug    Is debugging mode on? [optional]
     *
     * @return  void
     *
     * @since   3.0
     */
    public static function ajaxchosen(Registry $options, $debug = null)
    {
        // Retrieve options/defaults
        $selector       = $options->get('selector', '.tagfield');
        $type           = $options->get('type', 'GET');
        $url            = $options->get('url', null);
        $dataType       = $options->get('dataType', 'json');
        $jsonTermKey    = $options->get('jsonTermKey', 'term');
        $afterTypeDelay = $options->get('afterTypeDelay', '500');
        $minTermLength  = $options->get('minTermLength', '3');

        // Ajax URL is mandatory
        if (!empty($url)) {
            if (isset(static::$loaded[__METHOD__][$selector])) {
                return;
            }

            // Requires chosen to work
            static::chosen($selector, $debug);

            Text::script('JGLOBAL_KEEP_TYPING');
            Text::script('JGLOBAL_LOOKING_FOR');

            // Include scripts
            HTMLHelper::_('behavior.core');
            HTMLHelper::_('jquery.framework');
            HTMLHelper::_('script', 'legacy/ajax-chosen.min.js', ['version' => 'auto', 'relative' => true, 'detectDebug' => $debug]);

            Factory::getDocument()->addScriptOptions(
                'ajax-chosen',
                array(
                    'url'            => $url,
                    'debug'          => $debug,
                    'options'        => $options,
                    'selector'       => $selector,
                    'type'           => $type,
                    'dataType'       => $dataType,
                    'jsonTermKey'    => $jsonTermKey,
                    'afterTypeDelay' => $afterTypeDelay,
                    'minTermLength'  => $minTermLength,
                )
            );

            static::$loaded[__METHOD__][$selector] = true;
        }
    }
}
