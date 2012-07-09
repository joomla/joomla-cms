<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.filesystem.file');

/**
 * Database configuration model for the Joomla Core Installer.
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationModelDatabase extends JModelLegacy
{
	static protected $userId = 0;

	/**
	 * @since	3.0
	 */
	static protected function generateRandUserId()
	{
		$session = JFactory::getSession();
		$randUserId = $session->get('randUserId');
		if (empty($randUserId))
		{
			// Create the ID for the root user only once and store in session
			$randUserId = mt_rand(1, 1000);
			$session->set('randUserId', $randUserId);
		}
		return $randUserId;
	}

	/**
	 * @since	3.0
	 */
	static public function resetRandUserId()
	{
		self::$userId = 0;
		$session = JFactory::getSession();
		$session->set('randUserId', self::$userId);
	}

	/**
	 * @since	3.0
	 */
	static public function getUserId()
	{
		if (!self::$userId)
		{
			self::$userId = self::generateRandUserId();
		}
		return self::$userId;
	}

	/**
	 * @since	3.0
	 */
	public function initialise($options)
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

	/**
	 * @since	3.0
	 */
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
