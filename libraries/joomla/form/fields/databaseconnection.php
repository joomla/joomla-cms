<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

JFormHelper::loadFieldClass('list');

/**
 * Form Field class for the Joomla Platform.
 * Provides a list of available database connections, optionally limiting to
 * a given list.
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @see         JDatabase
 * @since       11.3
 */
class JFormFieldDatabaseConnection extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	public $type = 'DatabaseConnection';

	/**
	 * Method to get the list of database options.
	 *
	 * This method produces a drop down list of available databases supported
	 * by JDatabase drivers that are also supported by the application.
	 *
	 * @return  array    The field option objects.
	 *
	 * @since   11.3
	 * @see     JDatabase
	 */
	protected function getOptions()
	{
		/**
		 * This gets the list of database types unsupported by the application.
		 * This should be entered in the form definition as a comma separated list.
		 * If no unsupported databases are listed, it is assumed all available databases
		 * are supported.
		 */
		$unsupported = explode(',', $this->element['unsupported']);

		// This gets the connectors available in the platform and supported by the server and the application.
		foreach (JDatabase::getConnectors() as $connector)
		{
			if(in_array($connector, $unsupported))
			{
				// The connector is not supported by the application
				continue;
			}

			$options[$connector] = ucfirst($connector);
		}

		// This will come into play if an application is installed that requires
		// a database that is not available on the server.
		if (empty($options))
		{
			$options[''] = JText::_('JNONE');
		}

		return $options;
	}
}
