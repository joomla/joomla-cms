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
	 * @var    array
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $changeSetList = array();

	/**
	 * Total of errors
	 *
	 * @var    integer
	 *
	 * @since  __DEPLOY_VERSION__
	 */
	private $errorCount = 0;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MvcFactoryInterface  $factory  The factory.
	 *
	 * @see     ListModel
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
		return $this->errorCount;
	}

	/**
	 * Method to populate the schema cache.
	 *
	 * @param   integer  $cid  The extension ID to get the schema for
	 *
	 * @return  void
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function fetchSchemaCache($cid = 0)
	{
		// We already have it
		if (array_key_exists($cid, $this->changeSetList))
		{
			return;
		}

		// Add the ID to the state so it can be used for filtering
		if ($cid)
		{
			$this->setState('filter.extension_id', $cid);
		}

		// With the parent::save it can get the limit and we need to make sure it gets all extensions
		$results = $this->_getList($this->getListQuery());

		foreach ($results as $result)
		{
			$errorMessages = array();
			$errorCount    = 0;

			if (strcmp($result->element, 'joomla') === 0)
			{
				$result->element = 'com_admin';

				if (!$this->getDefaultTextFilters())
				{
					$errorMessages[] = Text::_('COM_INSTALLER_MSG_DATABASE_FILTER_ERROR');
					$errorCount++;
				}
			}

			$db        = $this->getDbo();
			$folderTmp = JPATH_ADMINISTRATOR . '/components/' . $result->element . '/sql/updates/';

			// If the extension doesn't follow the standard location for the
			// update sql files we don't support it
			if (!file_exists($folderTmp))
			{
				$installationXML = InstallerHelper::getInstallationXML($result->element, $result->type);
				$folderTmp       = (string) $installationXML->update->schemas->schemapath[0];

				$a = explode('/', $folderTmp);
				array_pop($a);
				$folderTmp = JPATH_ADMINISTRATOR . '/components/' . $result->element . '/' . implode('/', $a);
			}

			$changeSet = new ChangeSet($db, $folderTmp);

			// If the version in the #__schemas is different
			// than the update files, add to problems message
			$schema = $changeSet->getSchema();

			if ($result->version_id !== $schema)
			{
				$errorMessages[] = Text::sprintf('COM_INSTALLER_MSG_DATABASE_SCHEMA_ERROR', $result->version_id, $schema);
				$errorCount++;
			}

			// If the version in the manifest_cache is different than the
			// version in the installation xml, add to problems message
			$compareUpdateMessage = $this->compareUpdateVersion($result);

			if ($compareUpdateMessage)
			{
				$errorMessages[] = $compareUpdateMessage;
				$errorCount++;
			}

			// If there are errors in the database, add to the problems message
			$errors = $changeSet->check();

			$errorsMessage = $this->getErrorsMessage($errors);

			if ($errorsMessage)
			{
				$errorMessages = array_merge($errorMessages, $errorsMessage);
				$errorCount++;
			}

			// Number of database tables Checked and Skipped
			$errorMessages = array_merge($errorMessages, $this->getOtherInformationMessage($changeSet->getStatus()));

			// Set the total number of errors
			$this->errorCount += $errorCount;

			// Collect the extension details
			$this->changeSetList[$result->extension_id] = array(
				'folderTmp'     => $folderTmp,
				'errorsMessage' => $errorMessages,
				'errorsCount'   => $errorCount,
				'results'       => $changeSet->getStatus(),
				'schema'        => $schema,
				'extension'     => $result
			);
		}
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
	 * @param   array  $cids  List of the selected extensions to fix
	 *
	 * @return  void|boolean
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fix($cids = array())
	{
		$db = $this->getDbo();

		foreach ($cids as $i => $cid)
		{
			// Load the database issues
			$this->fetchSchemaCache($cid);

			$changeSet = $this->changeSetList[$cid];
			$changeSet['changeset'] = new ChangeSet($db, $changeSet['folderTmp']);
			$changeSet['changeset']->fix();

			$this->fixSchemaVersion($changeSet['changeset'], $changeSet['extension']->extension_id);
			$this->fixUpdateVersion($changeSet['extension']->extension_id);

			if ($i === 'com_admin')
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
				$db->quoteName(
					array(
						'extensions.client_id',
						'extensions.element',
						'extensions.extension_id',
						'extensions.folder',
						'extensions.manifest_cache',
						'extensions.name',
						'extensions.type',
						'schemas.version_id'
					)
				)
			)
			->from(
				$db->quoteName(
					'#__schemas',
					'schemas'
				)
			)->join(
				'INNER',
				$db->quoteName(
					'#__extensions', 'extensions'
				) . ' ON (' . $db->quoteName(
					'schemas.extension_id'
				) . ' = ' . $db->quoteName(
					'extensions.extension_id'
				) . ')'
			);

		$type        = $this->getState('filter.type');
		$clientId    = $this->getState('filter.client_id');
		$extensionId = $this->getState('filter.extension_id');
		$folder      = $this->getState('filter.folder');

		if ($type)
		{
			$query->where($db->quoteName('extensions.type') . ' = ' . $db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where($db->quoteName('extensions.client_id') . ' = ' . (int) $clientId);
		}

		if ($extensionId != '')
		{
			$query->where($db->quoteName('extensions.extension_id') . ' = ' . (int) $extensionId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where($db->quoteName('extensions.folder') . ' = ' . $db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where($db->quoteName('schemas.extension_id') . ' = ' . (int) substr($search, 3));
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
		$finalResults  = array();

		foreach ($results as $result)
		{
			if (array_key_exists($result->extension_id, $changeSetList) && $changeSetList[$result->extension_id])
			{
				$finalResults[] = $changeSetList[$result->extension_id];
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
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getSchemaVersion($extensionId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName('version_id'))
			->from($db->quoteName('#__schemas'))
			->where($db->quoteName('extension_id') . ' = ' . (int) $extensionId);
		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Fix schema version if wrong.
	 *
	 * @param   ChangeSet  $changeSet    Schema change set.
	 * @param   integer    $extensionId  ID of the extensions.
	 *
	 * @return  mixed  string schema version if success, false if fail.
	 *
	 * @throws  \Exception
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function fixSchemaVersion($changeSet, $extensionId)
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
			->where($db->quoteName('extension_id') . ' = ' . (int) $extensionId);
		$db->setQuery($query)->execute();

		// Add new row.
		$query->clear()
			->insert($db->quoteName('#__schemas'))
			->columns($db->quoteName('extension_id') . ',' . $db->quoteName('version_id'))
			->values((int) $extensionId . ', ' . $db->quote($schema));
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

		if ($extension->element === 'com_admin')
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
			return Text::sprintf('COM_INSTALLER_MSG_DATABASE_UPDATEVERSION_ERROR', $updateVersion, $extension->name, $extensionVersion);
		}

		return null;
	}

	/**
	 * Get a message of the tables skipped and checked
	 *
	 * @param   array  $status  status of of the update files
	 *
	 * @return  array  Messages with the errors with the update version
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getOtherInformationMessage($status)
	{
		$problemsMessage = array();
		$problemsMessage[] = Text::sprintf('COM_INSTALLER_MSG_DATABASE_CHECKED_OK', count($status['ok']));
		$problemsMessage[] = Text::sprintf('COM_INSTALLER_MSG_DATABASE_SKIPPED', count($status['skipped']));

		return $problemsMessage;
	}

	/**
	 * Get a message with all errors found in a given extension
	 *
	 * @param   array  $errors  data from #__extensions of a single extension.
	 *
	 * @return  array  List of messages with the errors in the database
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function getErrorsMessage($errors)
	{
		$errorMessages = array();

		foreach ($errors as $line => $error)
		{
			$key             = 'COM_INSTALLER_MSG_DATABASE_' . $error->queryType;
			$messages        = $error->msgElements;
			$file            = basename($error->file);
			$message0        = isset($messages[0]) ? $messages[0] : ' ';
			$message1        = isset($messages[1]) ? $messages[1] : ' ';
			$message2        = isset($messages[2]) ? $messages[2] : ' ';
			$errorMessages[] = Text::sprintf($key, $file, $message0, $message1, $message2);
		}

		return $errorMessages;
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

		if ($table->get('type') === 'file' && $table->get('element') === 'joomla')
		{
			$extensionVersion = new Version;
			$extensionVersion = $extensionVersion->getShortVersion();
		}
		else
		{
			$installationXML  = InstallerHelper::getInstallationXML($table->get('element'), $table->get('type'));
			$extensionVersion = (string) $installationXML->version;
		}

		if ($updateVersion === $extensionVersion)
		{
			return $updateVersion;
		}

		$cache->set('version', $extensionVersion);
		$table->set('manifest_cache', $cache->toString());

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
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function fixDefaultTextFilters()
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
