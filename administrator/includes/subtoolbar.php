<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class for the submenu.
 *
 * @package     Joomla.Administrator
 * @since       1.5
 * @deprecated  4.0  Use JHtmlSidebar instead.
 */
abstract class JSubMenuHelper
{
	/**
	 * Menu entries
	 *
	 * @var    array
	 * @since  3.0
	 * @deprecated  4.0
	 */
	protected static $entries = array();

	/**
	 * Filters
	 *
	 * @var    array
	 * @since  3.0
	 * @deprecated  4.0
	 */
	protected static $filters = array();

	/**
	 * Value for the action attribute of the form.
	 *
	 * @var    string
	 * @since  3.0
	 * @deprecated  4.0
	 */
	protected static $action = '';

	/**
	 * Method to add a menu item to submenu.
	 *
	 * @param   string   $name    Name of the menu item.
	 * @param   string   $link    URL of the menu item.
	 * @param   boolean  $active  True if the item is active, false otherwise.
	 *
	 * @return  void
	 *
	 * @since   1.5
	 * @deprecated  4.0  Use JHtmlSidebar::addEntry() instead.
	 */
	public static function addEntry($name, $link = '', $active = false)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::addEntry() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		self::$entries[] = array($name, $link, $active);
	}

	/**
	 * Returns an array of all submenu entries
	 *
	 * @return  array
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use JHtmlSidebar::getEntries() instead.
	 */
	public static function getEntries()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::getEntries() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return self::$entries;
	}

	/**
	 * Method to add a filter to the submenu
	 *
	 * @param   string   $label      Label for the menu item.
	 * @param   string   $name       name for the filter. Also used as id.
	 * @param   string   $options    options for the select field.
	 * @param   boolean  $noDefault  Don't the label as the empty option
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use JHtmlSidebar::addFilter() instead.
	 */
	public static function addFilter($label, $name, $options, $noDefault = false)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::addFilter() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		self::$filters[] = array('label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault);
	}

	/**
	 * Returns an array of all filters
	 *
	 * @return  array
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use JHtmlSidebar::getFilters() instead.
	 */
	public static function getFilters()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::getFilters() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return self::$filters;
	}

	/**
	 * Set value for the action attribute of the filter form
	 *
	 * @param   string  $action  Value for the action attribute of the form
	 *
	 * @return  void
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use JHtmlSidebar::setAction() instead.
	 */
	public static function setAction($action)
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::setAction() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		self::$action = $action;
	}

	/**
	 * Get value for the action attribute of the filter form
	 *
	 * @return  string  Value for the action attribute of the form
	 *
	 * @since   3.0
	 * @deprecated  4.0  Use JHtmlSidebar::getAction() instead.
	 */
	public static function getAction()
	{
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlSidebar::getAction() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		return self::$action;
	}
}
