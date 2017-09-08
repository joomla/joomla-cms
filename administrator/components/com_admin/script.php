<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_admin
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Database\UTF8MB4SupportInterface;

/**
 * Script file of Joomla CMS
 *
 * @since  1.6.4
 */
class JoomlaInstallerScript
{
	/**
	 * The Joomla Version we are updating from
	 *
	 * @var    string
	 * @since  3.7
	 */
	protected $fromVersion = null;

	/**
	 * Function to act prior to installation process begins
	 *
	 * @param   string      $action     Which action is happening (install|uninstall|discover_install|update)
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.7.0
	 */
	public function preflight($action, $installer)
	{
		if ($action === 'update')
		{
			// Get the version we are updating from
			if (!empty($installer->extension->manifest_cache))
			{
				$manifestValues = json_decode($installer->extension->manifest_cache, true);

				if ((array_key_exists('version', $manifestValues)))
				{
					$this->fromVersion = $manifestValues['version'];

					return true;
				}
			}

			return false;
		}

		return true;
	}

	/**
	 * Method to update Joomla!
	 *
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return  void
	 */
	public function update($installer)
	{
		$options['format']    = '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}';
		$options['text_file'] = 'joomla_update.php';

		JLog::addLogger($options, JLog::INFO, array('Update', 'databasequery', 'jerror'));

		try
		{
			JLog::add(JText::_('COM_JOOMLAUPDATE_UPDATE_LOG_DELETE_FILES'), JLog::INFO, 'Update');
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

		// This needs to stay for 2.5 update compatibility
		$this->deleteUnexistingFiles();
		$this->updateManifestCaches();
		$this->updateDatabase();
		$this->updateAssets($installer);
		$this->clearStatsCache();
		$this->convertTablesToUtf8mb4(true);
		$this->cleanJoomlaCache();

		// VERY IMPORTANT! THIS METHOD SHOULD BE CALLED LAST, SINCE IT COULD
		// LOGOUT ALL THE USERS
		$this->flushSessions();
	}

	/**
	 * Method to clear our stats plugin cache to ensure we get fresh data on Joomla Update
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	protected function clearStatsCache()
	{
		$db = JFactory::getDbo();

		try
		{
			// Get the params for the stats plugin
			$params = $db->setQuery(
				$db->getQuery(true)
					->select($db->quoteName('params'))
					->from($db->quoteName('#__extensions'))
					->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
					->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
					->where($db->quoteName('element') . ' = ' . $db->quote('stats'))
			)->loadResult();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		$params = json_decode($params, true);

		// Reset the last run parameter
		if (isset($params['lastrun']))
		{
			$params['lastrun'] = '';
		}

		$params = json_encode($params);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($params))
			->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
			->where($db->quoteName('element') . ' = ' . $db->quote('stats'));

		try
		{
			$db->setQuery($query)->execute();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}
	}

	/**
	 * Method to update Database
	 *
	 * @return  void
	 */
	protected function updateDatabase()
	{
		if (JFactory::getDbo()->getServerType() === 'mysql')
		{
			$this->updateDatabaseMysql();
		}
	}

	/**
	 * Method to update MySQL Database
	 *
	 * @return  void
	 */
	protected function updateDatabaseMysql()
	{
		$db = JFactory::getDbo();

		$db->setQuery('SHOW ENGINES');

		try
		{
			$results = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		foreach ($results as $result)
		{
			if ($result->Support != 'DEFAULT')
			{
				continue;
			}

			$db->setQuery('ALTER TABLE #__update_sites_extensions ENGINE = ' . $result->Engine);

			try
			{
				$db->execute();
			}
			catch (Exception $e)
			{
				echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

				return;
			}

			break;
		}
	}

	/**
	 * Update the manifest caches
	 *
	 * @return  void
	 */
	protected function updateManifestCaches()
	{
		$extensions = JExtensionHelper::getCoreExtensions();

		// Attempt to refresh manifest caches
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions');

		foreach ($extensions as $extension)
		{
			$query->where(
				'type=' . $db->quote($extension[0])
				. ' AND element=' . $db->quote($extension[1])
				. ' AND folder=' . $db->quote($extension[2])
				. ' AND client_id=' . $extension[3], 'OR'
			);
		}

		$db->setQuery($query);

		try
		{
			$extensions = $db->loadObjectList();
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return;
		}

		$installer = new JInstaller;

		foreach ($extensions as $extension)
		{
			if (!$installer->refreshManifestCache($extension->extension_id))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_MANIFEST', $extension->type, $extension->element, $extension->name, $extension->client_id) . '<br>';
			}
		}
	}

	/**
	 * Delete files that should not exist
	 *
	 * @return  void
	 */
	public function deleteUnexistingFiles()
	{
		$files = array(
			// Joomla 4.0
			'/components/com_contact/models/forms/form.xml',
		);

		// TODO There is an issue while deleting folders using the ftp mode
		$folders = array(
			// Joomla! 4.0
			'/templates/beez3',
			'/administrator/templates/isis',
			'/administrator/templates/hathor',
			'/media/jui/less',
		);

		jimport('joomla.filesystem.file');

		foreach ($files as $file)
		{
			if (JFile::exists(JPATH_ROOT . $file) && !JFile::delete(JPATH_ROOT . $file))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $file) . '<br>';
			}
		}

		jimport('joomla.filesystem.folder');

		foreach ($folders as $folder)
		{
			if (JFolder::exists(JPATH_ROOT . $folder) && !JFolder::delete(JPATH_ROOT . $folder))
			{
				echo JText::sprintf('FILES_JOOMLA_ERROR_FILE_FOLDER', $folder) . '<br>';
			}
		}
	}

	/**
	 * Method to create assets for newly installed components
	 *
	 * @param   JInstaller  $installer  The class calling this method
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	public function updateAssets($installer)
	{
		// List all components added since 4.0
		$newComponents = array(
		);

		foreach ($newComponents as $component)
		{
			/** @var JTableAsset $asset */
			$asset = JTable::getInstance('Asset');

			if ($asset->loadByName($component))
			{
				continue;
			}

			$asset->name      = $component;
			$asset->parent_id = 1;
			$asset->rules     = '{}';
			$asset->title     = $component;
			$asset->setLocation(1, 'last-child');

			if (!$asset->store())
			{
				// Install failed, roll back changes
				$installer->abort(JText::sprintf('JLIB_INSTALLER_ABORT_COMP_INSTALL_ROLLBACK', $asset->getError(true)));

				return false;
			}
		}

		return true;
	}

	/**
	 * If we migrated the session from the previous system, flush all the active sessions.
	 * Otherwise users will be logged in, but not able to do anything since they don't have
	 * a valid session
	 *
	 * @return  boolean
	 */
	public function flushSessions()
	{
		/**
		 * The session may have not been started yet (e.g. CLI-based Joomla! update scripts). Let's make sure we do
		 * have a valid session.
		 */
		$session = JFactory::getSession();

		/**
		 * Restarting the Session require a new login for the current user so lets check if we have an active session
		 * and only restart it if not.
		 * For B/C reasons we need to use getState as isActive is not available in 2.5
		 */
		if ($session->getState() !== 'active')
		{
			$session->restart();
		}

		// If $_SESSION['__default'] is no longer set we do not have a migrated session, therefore we can quit.
		if (!isset($_SESSION['__default']))
		{
			return true;
		}

		$db = JFactory::getDbo();

		try
		{
			switch ($db->getServerType())
			{
				// MySQL database, use TRUNCATE (faster, more resilient)
				case 'mysql':
					$db->truncateTable('#__session');
					break;

				// Non-MySQL databases, use a simple DELETE FROM query
				default:
					$query = $db->getQuery(true)
						->delete($db->qn('#__session'));
					$db->setQuery($query)->execute();
					break;
			}
		}
		catch (Exception $e)
		{
			echo JText::sprintf('JLIB_DATABASE_ERROR_FUNCTION_FAILED', $e->getCode(), $e->getMessage()) . '<br>';

			return false;
		}

		return true;
	}

	/**
	 * Converts the site's database tables to support UTF-8 Multibyte.
	 *
	 * @param   boolean  $doDbFixMsg  Flag if message to be shown to check db fix
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	public function convertTablesToUtf8mb4($doDbFixMsg = false)
	{
		$db = JFactory::getDbo();

		if (!($db instanceof UTF8MB4SupportInterface))
		{
			return;
		}

		// Set required conversion status
		if ($db->hasUTF8mb4Support())
		{
			$converted = 2;
		}
		else
		{
			$converted = 1;
		}

		// Check conversion status in database
		$db->setQuery('SELECT ' . $db->quoteName('converted')
			. ' FROM ' . $db->quoteName('#__utf8_conversion')
		);

		try
		{
			$convertedDB = $db->loadResult();
		}
		catch (Exception $e)
		{
			// Render the error message from the Exception object
			JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			if ($doDbFixMsg)
			{
				// Show an error message telling to check database problems
				JFactory::getApplication()->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
			}

			return;
		}

		// Nothing to do, saved conversion status from DB is equal to required
		if ($convertedDB == $converted)
		{
			return;
		}

		// Step 1: Drop indexes later to be added again with column lengths limitations at step 2
		$fileName1 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion-01.sql';

		if (is_file($fileName1))
		{
			$fileContents1 = @file_get_contents($fileName1);
			$queries1      = $db->splitSql($fileContents1);

			if (!empty($queries1))
			{
				foreach ($queries1 as $query1)
				{
					try
					{
						$db->setQuery($query1)->execute();
					}
					catch (Exception $e)
					{
						// If the query fails we will go on. It just means the index to be dropped does not exist.
					}
				}
			}
		}

		// Step 2: Perform the index modifications and conversions
		$fileName2 = JPATH_ROOT . '/administrator/components/com_admin/sql/others/mysql/utf8mb4-conversion-02.sql';

		if (is_file($fileName2))
		{
			$fileContents2 = @file_get_contents($fileName2);
			$queries2      = $db->splitSql($fileContents2);

			if (!empty($queries2))
			{
				foreach ($queries2 as $query2)
				{
					try
					{
						$db->setQuery($db->convertUtf8mb4QueryToUtf8($query2))->execute();
					}
					catch (Exception $e)
					{
						$converted = 0;

						// Still render the error message from the Exception object
						JFactory::getApplication()->enqueueMessage($e->getMessage(), 'error');
					}
				}
			}
		}

		if ($doDbFixMsg && $converted == 0)
		{
			// Show an error message telling to check database problems
			JFactory::getApplication()->enqueueMessage(JText::_('JLIB_DATABASE_ERROR_DATABASE_UPGRADE_FAILED'), 'error');
		}

		// Set flag in database if the update is done.
		$db->setQuery('UPDATE ' . $db->quoteName('#__utf8_conversion')
			. ' SET ' . $db->quoteName('converted') . ' = ' . $converted . ';')->execute();
	}

	/**
	 * This method clean the Joomla Cache using the method `clean` from the com_cache model
	 *
	 * @return  void
	 *
	 * @since   3.5.1
	 */
	private function cleanJoomlaCache()
	{
		$model = new \Joomla\Component\Cache\Administrator\Model\Cache;

		// Clean frontend cache
		$model->clean();

		// Clean admin cache
		$model->setState('client_id', 1);
		$model->clean();
	}
}
