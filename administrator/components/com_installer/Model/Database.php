<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Version;
use Joomla\Database\UTF8MB4SupportInterface;
use Joomla\Registry\Registry;

\JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

/**
 * Installer Database Model
 *
 * @since  1.6
 */
class Database extends Installer
{
	protected $_context = 'com_installer.discover';

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\Model\ListModel
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array(), MvcFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'update_site_name',
				'name',
				'client_id',
				'client', 'client_translated',
				'status',
				'type', 'type_translated',
				'folder', 'folder_translated',
				'extension_id'
			);
		}

		if (!\JFactory::getSession()->set('changeSetList'))
		{
			$this->fetchSchemaCache();
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to populate the schema cache.
	 *
	 * @return  void
	 *
	 * @since   4.0
	 */
	protected function fetchSchemaCache()
	{
		$session = \JFactory::getSession();
		$changeSetList = array();

		try
		{
			$results = $this->getItems();

			foreach ($result as $index => $result)
			{
				$errorCount = 0;
				$errorMessage = "";

				if (strcmp($result->element, 'joomla') == 0)
				{
					$result->element = 'com_admin';
					$index = 'core';

					if (!$this->getDefaultTextFilters())
					{
						$errorCount++;
						$errorMessage .= \JText::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR');
					}
				}

				$folderTmp = JPATH_ADMINISTRATOR . '/components/' . $result->element . '/sql/updates/';

				$changeset = new ChangeSet($db, $folderTmp);

				$errors = $changeset->check();
				$schema = $changeset->getSchema();

				if ($result->version_id != $schema)
				{
					$errorMessage .= \JText::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $result->version_id, $schema) . "<br>";
					$errorCount++;
				}

				foreach ($errors as $line => $error)
				{
					$key     = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
					$msgs    = $error->msgElements;
					$file    = basename($error->file);
					$msg0    = isset($msgs[0]) ? $msgs[0] : ' ';
					$msg1    = isset($msgs[1]) ? $msgs[1] : ' ';
					$msg2    = isset($msgs[2]) ? $msgs[2] : ' ';
					$errorMessage .= \JText::sprintf($key, $file, $msg0, $msg1, $msg2) . "<br>";
				}

				$changeSetList[$index] = array(
					'folderTmp' => $folderTmp,
					'errorsMessage'  => $errorMessage,
					'errorsCount'    => $errorCount + count($errors),
					'results'   => $changeset->getStatus(),
					'schema'    => $schema,
					'extension' => $result
				);
			}
		}
		catch (\RuntimeException $e)
		{
			\JFactory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return false;
		}

		if ($extensionIdArray != null)
		{
			return $changeSetList;
		}

		$changeSetList = json_encode($changeSetList);

		$session->set('changeSetList', $changeSetList);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		parent::populateState($ordering, $direction);
	}

	/**
	 * Fixes database problems.
	 *
	 * @param   array  $extensionIdArray  list of the selected extensions to fix
	 *
	 * @return  void|bool
	 */
	public function fix($extensionIdArray = null)
	{
		if (!$changeSetList = $this->getItems($extensionIdArray))
		{
			return false;
		}

		$db    = $this->getDbo();

		foreach ($changeSetList as $i => $changeSet)
		{
			$changeSet['changeset'] = new ChangeSet($db, $changeSet['folderTmp']);

			$changeSet['changeset']->fix();
			$this->fixSchemaVersion($changeSet['changeset'], $changeSet['extension']->extension_id);
			$this->fixUpdateVersion($changeSet['extension']->extension_id);

			if ($i === "core")
			{
				$installer = new \JoomlaInstallerScript;
				$installer->deleteUnexistingFiles();
				$this->fixDefaultTextFilters();

				/*
				 * Finally, if the schema updates succeeded, make sure the database is
				 * converted to utf8mb4 or, if not suported by the server, compatible to it.
				 */
				$statusArray = $changeSet['changeset']->getStatus();

				if (count($statusArray['error']) == 0)
				{
					$installer->convertTablesToUtf8mb4(false);
				}
			}
		}
	}

	/**
	 * Gets the changeset object.
	 *
	 * @param   array  $extensionIdArray  list of the selected extensions to fix ????
	 *
	 * @return  \Joomla\CMS\Schema\ChangeSet
	 */
	public function getItems()
	{
		$results = $this->retrieveItems();
		$results = $this->mergeSchemaCache($results);

		return $results;
	}

	protected function retrieveItems()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName('e.client_id') . ', ' .
				$db->quoteName('e.element') . ', ' .
				$db->quoteName('e.extension_id') . ', ' .
				$db->quoteName('e.folder') . ', ' .
				$db->quoteName('e.name') . ', ' .
				$db->quoteName('e.type') . ', ' .
				$db->quoteName('s.version_id')
			)
			->from(
				$db->quoteName(
					'#__schemas',
					's'
				)
			)->join(
				'INNER',
				$db->quoteName(
					'#__extensions', 'e'
				) . ' ON (' . $db->quoteName(
					's.extension_id'
				) . ' = ' . $db->quoteName(
					'e.extension_id'
				) . ')'
			);

		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($type)
		{
			$query->where('e.type = ' . $this->_db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where('e.client_id = ' . (int) $clientId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where('e.folder = ' . $this->_db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where('s.update_site_id = ' . (int) substr($search, 3));
		}

		if ($extensionIdArray != null)
		{
			$whereQuery = array();

			foreach ($extensionIdArray as $extension)
			{
				array_push($whereQuery, 'e.extension_id = ' . $extension);
			}

			$query->where($whereQuery, 'OR');
		}

		$result = $this->_getList($query);

		return $result;
	}

	protected function mergeSchemaCache($results)
	{
		$session = \JFactory::getSession();
		$changeSetList = $session->get('changeSetList');
		$finalResults = array();

		foreach ($results as $index => $result)
		{
			...
		}

		return $finalResults;
	}

	/**
	 * Get version from #__schemas table.
	 *
	 * @param   integer  $extensionId  id of the extensions.
	 *
	 * @return  mixed  the return value from the query, or null if the query fails.
	 *
	 * @throws \Exception
	 */
	public function getSchemaVersion($extensionId = 700)
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('version_id')
			->from($db->quoteName('#__schemas'))
			->where('extension_id = ' . $db->quote($extensionId));
		$db->setQuery($query);
		$result = $db->loadResult();

		return $result;
	}

	/**
	 * Fix schema version if wrong.
	 *
	 * @param   \Joomla\CMS\Schema\ChangeSet  $changeSet    Schema change set.
	 * @param   integer                       $extensionId  id of the extensions.
	 *
	 * @return   mixed  string schema version if success, false if fail.
	 */
	public function fixSchemaVersion($changeSet, $extensionId = 700)
	{
		// Get correct schema version -- last file in array.
		$schema = $changeSet->getSchema();

		// Check value. If ok, don't do update.
		if ($schema == $this->getSchemaVersion($extensionId))
		{
			return $schema;
		}

		// Delete old row.
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__schemas'))
			->where($db->quoteName('extension_id') . ' = ' . $db->quote($extensionId));
		$db->setQuery($query);
		$db->execute();

		// Add new row.
		$query->clear()
			->insert($db->quoteName('#__schemas'))
			->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
			->values($extensionId . ', ' . $db->quote($schema));
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\JDatabaseExceptionExecuting $e)
		{
			return false;
		}

		return $schema;
	}

	/**
	 * Get current version from #__extensions table.
	 *
	 * @return  mixed   version if successful, false if fail.
	 */
	public function getUpdateVersion()
	{
		$table = new \Joomla\CMS\Table\Extension($this->getDbo());
		$table->load('700');
		$cache = new Registry($table->manifest_cache);

		return $cache->get('version');
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal \JVersion short version).
	 *
	 * @param   integer  $extensionId  id of the extension
	 *
	 * @return   mixed  string update version if success, false if fail.
	 */
	public function fixUpdateVersion($extensionId = 700)
	{
		$table = new \Joomla\CMS\Table\Extension($this->getDbo());
		$table->load($extensionId);
		$cache = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');
		$cmsVersion = new Version;

		if ($updateVersion == $cmsVersion->getShortVersion())
		{
			return $updateVersion;
		}

		$cache->set('version', $cmsVersion->getShortVersion());
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $cmsVersion->getShortVersion();
		}

		return false;
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank.
	 *
	 * @return  string  default text filters (if any).
	 */
	public function getDefaultTextFilters()
	{
		$table = new \Joomla\CMS\Table\Extension($this->getDbo());
		$table->load($table->find(array('name' => 'com_config')));

		return $table->params;
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise.
	 */
	public function fixDefaultTextFilters()
	{
		$table = new \Joomla\CMS\Table\Extension($this->getDbo());
		$table->load($table->find(array('name' => 'com_config')));

		// Check for empty $config and non-empty content filters.
		if (!$table->params)
		{
			// Get filters from com_content and store if you find them.
			$contentParams = ComponentHelper::getComponent('com_content')->getParams();

			if ($contentParams->get('filters'))
			{
				$newParams = new Registry;
				$newParams->set('filters', $contentParams->get('filters'));
				$table->params = (string) $newParams;
				$table->store();

				return true;
			}
		}
	}

	/**
	 * Prepare the table to save the status of utf8mb4 conversion
	 * Make sure it contains 1 initialized record if there is not
	 * already exactly 1 record.
	 *
	 * @return  void
	 *
	 * @since   3.5
	 */
	private function prepareUtf8mb4StatusTable()
	{
		$db = \JFactory::getDbo();

		if (!$db instanceof UTF8MB4SupportInterface)
		{
			return;
		}

		$creaTabSql = 'CREATE TABLE IF NOT EXISTS ' . $db->quoteName('#__utf8_conversion')
			. ' (' . $db->quoteName('converted') . ' tinyint(4) NOT NULL DEFAULT 0'
			. ') ENGINE=InnoDB';

		if ($db->hasUTF8mb4Support())
		{
			$creaTabSql = $creaTabSql
				. ' DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci;';
		}
		else
		{
			$creaTabSql = $creaTabSql
				. ' DEFAULT CHARSET=utf8 DEFAULT COLLATE=utf8_unicode_ci;';
		}

		$db->setQuery($creaTabSql)->execute();

		$db->setQuery('SELECT COUNT(*) FROM ' . $db->quoteName('#__utf8_conversion') . ';');

		$count = $db->loadResult();

		if ($count > 1)
		{
			// Table messed up somehow, clear it
			$db->setQuery('DELETE FROM ' . $db->quoteName('#__utf8_conversion')
				. ';')->execute();
			$db->setQuery('INSERT INTO ' . $db->quoteName('#__utf8_conversion')
				. ' (' . $db->quoteName('converted') . ') VALUES (0);')->execute();
		}
		elseif ($count == 0)
		{
			// Record missing somehow, fix this
			$db->setQuery('INSERT INTO ' . $db->quoteName('#__utf8_conversion')
				. ' (' . $db->quoteName('converted') . ') VALUES (0);')->execute();
		}
	}
}
