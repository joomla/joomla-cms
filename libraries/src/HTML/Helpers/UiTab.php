<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;

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
	protected static $loaded = array();

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
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			// Include the custom element
			Factory::getDocument()->getWebAssetManager()
				->useStyle('webcomponent.joomla-tab')
				->useScript('webcomponent.joomla-tab');

			// Setup options object
			$opt['active'] = (isset($params['active']) && ($params['active'])) ? (string) $params['active'] : '';

			// Set static array
			static::$loaded[__METHOD__][$sig] = true;
			static::$loaded[__METHOD__][$selector]['active'] = $opt['active'];
		}

		// @TODO echo the recall attribute correctly, now it's hardcoded!!!
		$recall = !isset($params['recall']) ? '' : 'recall';

		return '<joomla-tab id="' . $selector . '" recall>';
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
	 * @param   string  $title     The title text for the new UL tab
	 *
	 * @return  string  HTML to start a new panel
	 *
	 * @since   4.0.0
	 */
	public static function addTab($selector, $id, $title)
	{
		$active = (static::$loaded[__CLASS__ . '::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return '<section id="' . $id . '"' . $active . ' name="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '">';

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
		return '</section>';
	}
}
