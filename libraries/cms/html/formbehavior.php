<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;

/**
 * Utility class for form related behaviors
 *
 * @since  3.0
 */
abstract class JHtmlFormbehavior
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
		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		// Include jQuery
		JHtml::_('jquery.framework');

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug  = (boolean) $config->get('debug');
		}

		// Default settings
		if (!isset($options['disable_search_threshold']))
		{
			$options['disable_search_threshold'] = 10;
		}

		if (!isset($options['allow_single_deselect']))
		{
			$options['allow_single_deselect'] = true;
		}

		if (!isset($options['placeholder_text_multiple']))
		{
			$options['placeholder_text_multiple'] = JText::_('JGLOBAL_SELECT_SOME_OPTIONS');
		}

		if (!isset($options['placeholder_text_single']))
		{
			$options['placeholder_text_single'] = JText::_('JGLOBAL_SELECT_AN_OPTION');
		}

		if (!isset($options['no_results_text']))
		{
			$options['no_results_text'] = JText::_('JGLOBAL_SELECT_NO_RESULTS_MATCH');
		}

		// Options array to json options string
		$options_str = json_encode($options, ($debug && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false));

		JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false, $debug);
		JHtml::_('stylesheet', 'jui/chosen.css', false, true);
		JFactory::getDocument()->addScriptDeclaration("
				jQuery(document).ready(function (){
					jQuery('" . $selector . "').chosen(" . $options_str . ");
				});
			"
		);

		static::$loaded[__METHOD__][$selector] = true;

		return;
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

		JText::script('JGLOBAL_KEEP_TYPING');
		JText::script('JGLOBAL_LOOKING_FOR');

		// Ajax URL is mandatory
		if (!empty($url))
		{
			if (isset(static::$loaded[__METHOD__][$selector]))
			{
				return;
			}

			// Include jQuery
			JHtml::_('jquery.framework');

			// Requires chosen to work
			static::chosen($selector, $debug);

			JHtml::_('script', 'jui/ajax-chosen.min.js', false, true, false, false, $debug);
			JFactory::getDocument()->addScriptDeclaration("
				(function($){
					$(document).ready(function () {
						$('" . $selector . "').ajaxChosen({
							type: '" . $type . "',
							url: '" . $url . "',
							dataType: '" . $dataType . "',
							jsonTermKey: '" . $jsonTermKey . "',
							afterTypeDelay: '" . $afterTypeDelay . "',
							minTermLength: '" . $minTermLength . "'
						}, function (data) {
							var results = [];

							$.each(data, function (i, val) {
								results.push({ value: val.value, text: val.text });
							});

							return results;
						});
					});
				})(jQuery);
				"
			);

			static::$loaded[__METHOD__][$selector] = true;
		}

		return;
	}
}
