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
	 * Load Main Behavior script
	 */
	public static function formbehavior()
	{
		if (isset(static::$loaded[__METHOD__]))
		{
			return;
		}

		// Include Core
		JHtml::_('behavior.core');

		// Include jQuery
		JHtml::_('jquery.framework');

		JHtml::_('script', 'jui/behaviorform.min.js', false, true);

		static::$loaded[__METHOD__] = true;
	}

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

		$debug = is_null($debug) ? JDEBUG : $debug;

		// Load main script
		self::formbehavior();

		// Load Chosen asset
		JHtml::_('stylesheet', 'jui/chosen.css', false, true);
		JHtml::_('script', 'jui/chosen.jquery.min.js', false, true, false, false, $debug);

		// Default translation
		JText::script('JGLOBAL_SELECT_SOME_OPTIONS');
		JText::script('JGLOBAL_SELECT_AN_OPTION');
		JText::script('JGLOBAL_SELECT_NO_RESULTS_MATCH');

		JFactory::getDocument()->addScriptOptions(__FUNCTION__, array($selector => $options));

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
		$selector = $options->get('selector', '.tagfield');

		if (isset(static::$loaded[__METHOD__][$selector]))
		{
			return;
		}

		$debug = is_null($debug) ? JDEBUG : $debug;

		// Load main script
		self::formbehavior();

		// Requires chosen to work
		static::chosen($selector, $debug);

		JHtml::_('script', 'jui/ajax-chosen.min.js', false, true, false, false, $debug);

		JText::script('JGLOBAL_KEEP_TYPING');
		JText::script('JGLOBAL_LOOKING_FOR');

		JFactory::getDocument()->addScriptOptions(__FUNCTION__, array($selector => $options->toArray()));

		static::$loaded[__METHOD__][$selector] = true;

		return;
	}
}
