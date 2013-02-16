<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
	 * @see		JDatabase
	 */
	protected function getOptions()
	{
		// Initialize variables.
		// This gets the connectors available in the platform and supported by the server.
		$available = JDatabase::getConnectors();

		/**
		 * This gets the list of database types supported by the application.
		 * This should be entered in the form definition as a comma separated list.
		 * If no supported databases are listed, it is assumed all available databases
		 * are supported.
		 */
		$supported = $this->element['supported'];
		if (!empty($supported))
		{
			$supported = explode(',', $supported);
			foreach ($supported as $support)
			{
				if (in_array($support, $available))
				{
					$options[$support] = ucfirst($support);
				}
			}
		}
		else
		{
			foreach ($available as $support)
			{
				$options[$support] = ucfirst($support);
			}
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
