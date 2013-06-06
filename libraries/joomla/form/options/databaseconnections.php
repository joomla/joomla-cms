<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Database Connections Option class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @since       11.1
 */
abstract class JFormOptionDatabaseConnections
{
	protected $type = 'DatabaseConnections';

	/**
	 * Method to get a list of options.
	 *
	 * @param   SimpleXMLElement  $option     <option/> element
	 * @param   string            $fieldname  The name of the field containing this option.
	 *
	 * @return  array  A list of objects representing HTML option elements (such as created by JHtmlSelect::option).
	 *
	 * @since   11.1
	 */
	public static function getOptions(SimpleXMLElement $option, $fieldname = '')
	{
		// List of options to return
		$options = array();

		// This gets the connectors available in the platform and supported by the server.
		$connectors = JDatabaseDriver::getConnectors();

		/**
		 * This gets the list of database types supported by the application.
		 * This should be entered in the form definition as a comma separated list.
		 * If no supported databases are listed, it is assumed all available databases
		 * are supported.
		 */
		$supported = array_filter(array_map('trim', explode(',', $option['supported'])));

		if (!empty($supported))
		{
			$connectors = array_intersect($connectors, $supported);
		}

		foreach ($connectors as $connector)
		{
			$options[] = JHtml::_('select.option', $connector, JText::_(ucfirst($connector)));
		}

		// This will come into play if an application is installed that requires
		// a database that is not available on the server.
		if (empty($options))
		{
			$options[] = JHtml::_('select.option', '', JText::_('JNONE'));
		}

		return $options;
	}
}
