<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for the Joomla core UI Tab element.
 *
 * @since  __DEPLOY_VERSION__
 */
abstract class JHtmlUiTab
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function startTabSet($selector = 'myTab', $params = array())
	{
		$sig = md5(serialize(array($selector, $params)));

		if (!isset(static::$loaded[__METHOD__][$sig]))
		{
			// Include the custom element
			JHtml::_('webcomponent', 'system/webcomponents/joomla-tab.min.js', ['relative' => true, 'version' => 'auto']);

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
	 * @since   __DEPLOY_VERSION__
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
	 * @since   __DEPLOY_VERSION__
	 */
	public static function addTab($selector, $id, $title)
	{
		$active = (static::$loaded['JHtmlUiTab::startTabSet'][$selector]['active'] == $id) ? ' active' : '';

		return '<section id="' . $id . '" class="' . $active . '" name="' . htmlspecialchars($title, ENT_COMPAT, 'UTF-8') . '">';

	}

	/**
	 * Close the current tab content panel
	 *
	 * @return  string  HTML to close the pane
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function endTab()
	{
		return '</section>';
	}
}
