<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.model');
jimport('joomla.filesystem.file');

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationModelDatabase extends JModel
{

	function initialise($options)
	{
		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		// Load the back-end language files so that the DB error messages work
		$jlang = JFactory::getLanguage();

		// Pre-load en-GB in case the chosen language files do not exist
		$jlang->load('joomla', JPATH_ADMINISTRATOR, 'en-GB', true);

		// Load the selected language
		$jlang->load('joomla', JPATH_ADMINISTRATOR, $options->language, true);

		// Ensure a database type was selected.
		if (empty($options->db_type))
		{
			$this->setError(JText::_('INSTL_DATABASE_INVALID_TYPE'));

			return false;
		}

		try
		{
			// @todo remove deprecated
			JError::$legacy = false;

			JDatabaseInstaller::getInstance($options)
				->check()
				->create()
				->clean()
				->populate()
				->update()
				->localize();
		}
		catch(JDatabaseInstallerException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}
		catch(JDatabaseException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		return true;
	}

	function installSampleData($options)
	{
		// @todo remove deprecated
		JError::$legacy = false;

		// Get the options as a JObject for easier handling.
		$options = JArrayHelper::toObject($options, 'JObject');

		JDatabaseInstaller::getInstance($options)->installSampleData();

		return true;
	}
}
