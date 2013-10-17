<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Searchtools elements.
 *
 * @package     Joomla.Libraries
 * @subpackage  HTML
 * @since       3.2
 */
abstract class JHtmlSearchtools
{
	/**
	 * @var    array  Array containing information for loaded files
	 * @since  3.2
	 */
	protected static $loaded = array();

	/**
	 * Load the main Searchtools libraries
	 *
	 * @param   mixed  $debug  Is debugging mode on? [optional]
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function main($debug = null)
	{
		// Only load once
		if (!empty(static::$loaded[__METHOD__]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if ($debug === null)
		{
			$config = JFactory::getConfig();
			$debug = (boolean) $config->get('debug');
		}

		// Load the jQuery plugin && CSS
		JHtml::_('script', 'jui/jquery.searchtools.js', false, true, false, false, $debug);
		JHtml::_('stylesheet', 'jui/jquery.searchtools.css', false, true);

		static::$loaded[__METHOD__] = true;

		return;
	}

	/**
	 * Add javascript support for Bootstrap alerts
	 *
	 * @param   array  $options  Optional settings array
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function grid($options = array())
	{
		// Default form name
		if (!isset($options['formName']))
		{
			$options['formName'] = 'adminForm';
		}

		// Only load once
		if (isset(static::$loaded[__METHOD__][$options['formName']]))
		{
			return;
		}

		// If no debugging value is set, use the configuration setting
		if (!isset($options['debug']))
		{
			$config = JFactory::getConfig();
			$options['debug'] = (boolean) $config->get('debug');
		}

		// Include main searchtools framework
		static::main($options['debug']);

		JHtml::_('script', 'jui/jquery.searchtools-grid.js', false, true, false, false, $options['debug']);

		// Attach the popover to the document
		JFactory::getDocument()->addScriptDeclaration(
			"(function($){
				$(document).ready(function() {
					$('#" . $options['formName'] . "').stoolsGrid(" . json_encode($options) . ");
				});
			})(jQuery);
			"
		);

		static::$loaded[__METHOD__][$options['formName']] = true;

		return;
	}

	/**
	 * Method to sort a column in a grid
	 *
	 * @param   string  $title          The link title
	 * @param   string  $order          The order field for the column
	 * @param   string  $direction      The current direction
	 * @param   mixed   $selected       The selected ordering
	 * @param   string  $task           An optional task override
	 * @param   string  $new_direction  An optional direction for the new column
	 * @param   string  $tip            An optional text shown as tooltip title instead of $title
	 * @param   string  $icon           Icon to show
	 * @param   string  $formName       Name of the form to submit
	 *
	 * @return  string
	 */
	public static function sort($title, $order, $direction = 'asc', $selected = 0, $task = null, $new_direction = 'asc', $tip = '', $icon = null, $formName = 'adminForm')
	{
		$direction = strtolower($direction);
		$orderIcons = array('icon-arrow-up-3', 'icon-arrow-down-3');
		$index = (int) ($direction == 'desc');

		if ($order != $selected)
		{
			$direction = $new_direction;
		}
		else
		{
			$direction = ($direction == 'desc') ? 'asc' : 'desc';
		}

		// Create an object to pass it to the layouts
		$data            = new stdClass;
		$data->order     = $order;
		$data->direction = $direction;
		$data->selected  = $selected;
		$data->task      = $task;
		$data->tip       = $tip;
		$data->title     = $title;
		$data->orderIcon = $orderIcons[$index];
		$data->icon      = $icon;
		$data->formName  = $formName;

		return JLayoutHelper::render('joomla.searchtools.grid.sort', $data);
	}
}
