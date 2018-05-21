<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Installer\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\CMS\Schema\ChangeSet;
use Joomla\CMS\Table\Extension;
use Joomla\CMS\Version;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;
use Joomla\Database\UTF8MB4SupportInterface;
use Joomla\Registry\Registry;

\JLoader::register('JoomlaInstallerScript', JPATH_ADMINISTRATOR . '/components/com_admin/script.php');

/**
 * Installer Database Model
 *
 * @since  1.6
 */
class DatabaseModel extends InstallerModel
{
	/**
	 * Set the model context
	 *
	 * @var    string
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	protected $_context = 'com_installer.discover';

	/**
	 * ChangeSet of all extensions
	 *
	 * @var  array
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $changeSetList = null;

	/**
	 * Total of errors
	 *
	 * @var  integer
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected $errorCount = 0;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\ListModel
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

		parent::__construct($config, $factory);
	}

	/**
	 * Method to return the total number of errors in all the extensions, saved in cache.
	 *
	 * @return  integer
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getErrorCount()
	{
		return Factory::getApplication()->getSession()->get('errorCount');
	}

	/**
	 * Method to populate the schema cache.
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function fetchSchemaCache()
	{
		// We already have it
		if ($this->changeSetList)
		{
			return;
		}

		// Restore it from Session
		$changeSetList = Factory::getApplication()->getSession()->get('changeSetList');
		$changeSetList = json_decode($changeSetList, true);
		$this->changeSetList = $changeSetList;

		if ($this->changeSetList)
		{
			return;
		}

		// Ok, let's generate it
		$changeSetList = array();

		try
		{
			// With the parent::save it can get the limit and
			// we need to make sure it gets all extensions
			$results = $this->_getList($this->getListQuery());

			foreach ($results as $result)
			{
				$errorCount = 0;
				$problemsMessage = "<ul>";

				if (strcmp($result->element, 'joomla') == 0)
				{
					$result->element = 'com_admin';
					if (!$this->getDefaultTextFilters())
					{
						$errorCount++;
						$problemsMessage .= '<li>' . Text::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR') . '</li>';
					}
				}

				$db  = $this->getDbo();
				$folderTmp = JPATH_ADMINISTRATOR . '/components/' . $result->element . '/sql/updates/';

				// If the extension doesn't follow the standard location for the
				// update sql files we don't support it
				if (!file_exists($folderTmp))
				{
					$installationXML = InstallerHelper::getInstallationXML($result->element, $result->type);

					$folderTmp = (string) $installationXML->update->schemas->schemapath[0];

					$a = explode("/", $folderTmp);
					array_pop($a);
					$folderTmp = JPATH_ADMINISTRATOR . '/components/' . $result->element . "/" . implode("/", $a);
				}

				$changeset = new ChangeSet($db, $folderTmp);

				// If the version in the #__schemas is different
				// than the update files, add to problems message
				$schema = $changeset->getSchema();

				if ($result->version_id != $schema)
				{
					$problemsMessage .= '<li>' . Text::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $result->version_id, $result->name, $schema) . '</li>';
					$errorCount++;
				}

				// If the version in the manifest_cache is different than the
				// version in the installation xml, add to problems message
				$compareUpdateMessage = $this->compareUpdateVersion($result);

				if ($compareUpdateMessage)
				{
					$problemsMessage .= $compareUpdateMessage;
					$errorCount++;
				}

				// If there are errors in the database, add to the problems message
				$errors = $changeset->check();

				$errorsMessage = $this->getErrorsMessage($errors);

				if ($errorsMessage)
				{
					$problemsMessage .= $errorsMessage;
					$errorCount++;
				}

				if ($errorCount)
				{
					$problemsMessage .= "<hr>";
				}

				// Number of database tables Checked and Skipped
				$problemsMessage .= $this->getOtherInformationMessage($changeset->getStatus());

				$this->errorCount += $errorCount;

				$problemsMessage .= "</ul>";

				$changeSetList[$result->element] = array(
					'folderTmp'      => $folderTmp,
					'errorsMessage'  => $problemsMessage,
					'errorsCount'    => $errorCount,
					'results'        => $changeset->getStatus(),
					'schema'         => $schema,
					'extension'      => $result
				);
			}
		}
		catch (\RuntimeException $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');

			return;
		}

		// Ready
		$changeSetList       = json_encode($changeSetList);
		$this->changeSetList = json_decode($changeSetList, true);

		// Save it for the next time
		Factory::getApplication()->getSession()->set('errorCount', $this->errorCount);
		Factory::getApplication()->getSession()->set('changeSetList', $changeSetList);
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

		// Prepare the utf8mb4 conversion check table
		$this->prepareUtf8mb4StatusTable();

		parent::populateState($ordering, $direction);
	}

	/**
	 * Fixes database problems.
	 *
	 * @param   array  $elementArray  list of the selected extensions to fix
	 *
	 * @return  void|bool
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fix($elementArray = null)
	{
		$changeSetList = json_decode(Factory::getApplication()->getSession()->get('changeSetList'), true);

		$db = $this->getDbo();

		foreach ($elementArray as $i => $element)
		{
			$changeSet = $changeSetList[$element];
			$changeSet['changeset'] = new ChangeSet($db, $changeSet['folderTmp']);
			$changeSet['changeset']->fix();

			$this->fixSchemaVersion($changeSet['changeset'], $changeSet['extension']['extension_id']);
			$this->fixUpdateVersion($changeSet['extension']['extension_id']);

			if ($i === "com_admin")
			{
				$installer = new \JoomlaInstallerScript;
				$installer->deleteUnexistingFiles();
				$this->fixDefaultTextFilters();

				/*
				 * Finally, if the schema updates succeeded, make sure the database table is
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
	 * Gets the changeset array.
	 *
	 * @return  array  Array with the information of the versions problems, errors and the extensions itself
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		$this->fetchSchemaCache();

		$results = parent::getItems();
		$results = $this->mergeSchemaCache($results);

		return $results;
	}

	/**
	 * Method to get the database query
	 *
	 * @return  \JDatabaseQuery  The database query
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select(
				$db->quoteName('e.client_id') . ', ' .
				$db->quoteName('e.element') . ', ' .
				$db->quoteName('e.extension_id') . ', ' .
				$db->quoteName('e.folder') . ', ' .
				$db->quoteName('e.manifest_cache') . ', ' .
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

		return $query;
	}

	/**
	 * Merge the items that will be visible with the changeSet information in cache
	 *
	 * @param   array  $results  extensions returned from parent::getItems().
	 *
	 * @return  array  the changeSetList of the merged items
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function mergeSchemaCache($results)
	{
		$changeSetList = $this->changeSetList;
		$finalResults = array();

		foreach ($results as $result)
		{
			$element = $result->element == 'joomla' ? 'com_admin' : $result->element;

			if (array_key_exists($element, $changeSetList) && $changeSetList[$element])
			{
				$finalResults[] = $changeSetList[$element];
			}
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
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @return  mixed  string schema version if success, false if fail.
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
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
	 * @param   object  $extension  data from #__extensions of a single extension.
	 *
	 * @return  mixed  string message with the errors with the update version or null if none
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function compareUpdateVersion($extension)
	{
		$updateVersion = json_decode($extension->manifest_cache)->version;

		if ($extension->element == 'com_admin')
		{
			$extensionVersion = JVERSION;
		}
		else
		{
			$installationXML  = InstallerHelper::getInstallationXML($extension->element, $extension->type);
			$extensionVersion = (string) $installationXML->version;
		}

		if (version_compare($extensionVersion, $updateVersion) != 0)
		{
			return '<li>' . Text::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $updateVersion, $extension->name, $extensionVersion) . '</li>';
		}

		return null;
	}

	/**
	 * Get a message of the tables skipped and checked
	 *
	 * @param   array  $status  status of of the update files
	 *
	 * @return  string  string message with the errors with the update version or null if none
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getOtherInformationMessage($status)
	{
		$problemsMessage = "";
		$problemsMessage .= '<li>' . Text::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($status['ok'])) . '</li>';
		$problemsMessage .= '<li>' . Text::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($status['skipped'])) . '</li>';

		return $problemsMessage;
	}

	/**
	 * Get a message with all errors found in a given extension
	 *
	 * @param   array  $errors  data from #__extensions of a single extension.
	 *
	 * @return  mixed  string   message with the errors in the database or null if none
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getErrorsMessage($errors)
	{
		$errorMessage = "";
		foreach ($errors as $line => $error)
		{
			$key     = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
			$msgs    = $error->msgElements;
			$file    = basename($error->file);
			$msg0    = isset($msgs[0]) ? $msgs[0] : ' ';
			$msg1    = isset($msgs[1]) ? $msgs[1] : ' ';
			$msg2    = isset($msgs[2]) ? $msgs[2] : ' ';
			$errorMessage .= '<li>' . Text::sprintf($key, $file, $msg0, $msg1, $msg2) . '</li>';
		}

		return $errorMessage;
	}

	/**
	 * Fix Joomla version in #__extensions table if wrong (doesn't equal \JVersion short version).
	 *
	 * @param   integer  $extensionId  id of the extension
	 *
	 * @return  mixed  string update version if success, false if fail.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fixUpdateVersion($extensionId)
	{
		$table = new Extension($this->getDbo());
		$table->load($extensionId);
		$cache = new Registry($table->manifest_cache);
		$updateVersion = $cache->get('version');

		if ($extensionId == 700)
		{
			$extensionVersion = new Version;
			$extensionVersion = $extensionVersion->getShortVersion();
		}
		else
		{
			$installationXML = InstallerHelper::getInstallationXML($table->element, $table->type);
			$extensionVersion = (string) $installationXML->version;
		}

		if ($updateVersion == $extensionVersion)
		{
			return $updateVersion;
		}

		$cache->set('version', $extensionVersion);
		$table->manifest_cache = $cache->toString();

		if ($table->store())
		{
			return $extensionVersion;
		}

		return false;
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank.
	 *
	 * @return  string  default text filters (if any).
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getDefaultTextFilters()
	{
		$table = new Extension($this->getDbo());
		$table->load($table->find(array('name' => 'com_config')));

		return $table->params;
	}

	/**
	 * For version 2.5.x only
	 * Check if com_config parameters are blank. If so, populate with com_content text filters.
	 *
	 * @return  mixed  boolean true if params are updated, null otherwise.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fixDefaultTextFilters()
	{
		$table = new Extension($this->getDbo());
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
		$db = Factory::getDbo();

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
