<?php
/**
 * @package    Joomla.Administrator
 *
 * @copyright  Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
		JLog::add('JSubMenuHelper::addEntry() is deprecated. Use JHtmlSidebar::addEntry() instead.', JLog::WARNING, 'deprecated');
		array_push(self::$entries, array($name, $link, $active));
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
		JLog::add('JSubMenuHelper::getEntries() is deprecated. Use JHtmlSidebar::getEntries() instead.', JLog::WARNING, 'deprecated');

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
		JLog::add('JSubMenuHelper::addFilter() is deprecated. Use JHtmlSidebar::addFilter() instead.', JLog::WARNING, 'deprecated');
		array_push(self::$filters, array('label' => $label, 'name' => $name, 'options' => $options, 'noDefault' => $noDefault));
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
		JLog::add('JSubMenuHelper::getFilters() is deprecated. Use JHtmlSidebar::getFilters() instead.', JLog::WARNING, 'deprecated');

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
		JLog::add('JSubMenuHelper::setAction() is deprecated. Use JHtmlSidebar::setAction() instead.', JLog::WARNING, 'deprecated');
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
		JLog::add('JSubMenuHelper::getAction() is deprecated. Use JHtmlSidebar::getAction() instead.', JLog::WARNING, 'deprecated');

		return self::$action;
	}
}
