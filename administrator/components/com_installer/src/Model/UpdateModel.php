<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Extension\ExtensionHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Installer\Installer;
use Joomla\CMS\Installer\InstallerHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Updater\Update;
use Joomla\CMS\Updater\Updater;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

/**
 * Installer Update Model
 *
 * @since  1.6
 */
class UpdateModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\ListModel
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name', 'u.name',
				'client_id', 'u.client_id', 'client_translated',
				'type', 'u.type', 'type_translated',
				'folder', 'u.folder', 'folder_translated',
				'extension_id', 'u.extension_id',
			);
		}

		parent::__construct($config, $factory);
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
	protected function populateState($ordering = 'u.name', $direction = 'asc')
	{
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		$app = Factory::getApplication();
		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to get the database query
	 *
	 * @return  \JDatabaseQuery  The database query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();

		// Grab updates ignoring new installs
		$query = $db->getQuery(true)
			->select('u.*')
			->select($db->quoteName('e.manifest_cache'))
			->from($db->quoteName('#__updates', 'u'))
			->join(
				'LEFT',
				$db->quoteName('#__extensions', 'e'),
				$db->quoteName('e.extension_id') . ' = ' . $db->quoteName('u.extension_id')
			)
			->where($db->quoteName('u.extension_id') . ' != 0');

		// Process select filters.
		$clientId    = $this->getState('filter.client_id');
		$type        = $this->getState('filter.type');
		$folder      = $this->getState('filter.folder');
		$extensionId = $this->getState('filter.extension_id');

		if ($type)
		{
			$query->where($db->quoteName('u.type') . ' = :type')
				->bind(':type', $type);
		}

		if ($clientId != '')
		{
			$clientId = (int) $clientId;
			$query->where($db->quoteName('u.client_id') . ' = :clientid')
				->bind(':clientid', $clientId, ParameterType::INTEGER);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$folder = $folder === '*' ? '' : $folder;
			$query->where($db->quoteName('u.folder') . ' = :folder')
				->bind(':folder', $folder);
		}

		if ($extensionId)
		{
			$extensionId = (int) $extensionId;
			$query->where($db->quoteName('u.extension_id') . ' = :extensionid')
				->bind(':extensionid', $extensionId, ParameterType::INTEGER);
		}
		else
		{
			$eid = ExtensionHelper::getExtensionRecord('joomla', 'file')->extension_id;
			$query->where($db->quoteName('u.extension_id') . ' != 0')
				->where($db->quoteName('u.extension_id') . ' != :eid')
				->bind(':eid', $eid, ParameterType::INTEGER);
		}

		// Process search filter.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'eid:') !== false)
			{
				$sid = (int) substr($search, 4);
				$query->where($db->quoteName('u.extension_id') . ' = :sid')
					->bind(':sid', $sid, ParameterType::INTEGER);
			}
			else
			{
				if (stripos($search, 'uid:') !== false)
				{
					$suid = (int) substr($search, 4);
					$query->where($db->quoteName('u.update_site_id') . ' = :suid')
						->bind(':suid', $suid, ParameterType::INTEGER);
				}
				elseif (stripos($search, 'id:') !== false)
				{
					$uid = (int) substr($search, 3);
					$query->where($db->quoteName('u.update_id') . ' = :uid')
						->bind(':uid', $uid, ParameterType::INTEGER);
				}
				else
				{
					$search = '%' . str_replace(' ', '%', trim($search)) . '%';
					$query->where($db->quoteName('u.name') . ' LIKE :search')
						->bind(':search', $search);
				}
			}
		}

		return $query;
	}

	/**
	 * Translate a list of objects
	 *
	 * @param   array  $items  The array of objects
	 *
	 * @return  array The array of translated objects
	 *
	 * @since   3.5
	 */
	protected function translate(&$items)
	{
		foreach ($items as &$item)
		{
			$item->client_translated  = Text::_([0 => 'JSITE', 1 => 'JADMINISTRATOR', 3 => 'JAPI'][$item->client_id] ?? 'JSITE');
			$manifest                 = json_decode($item->manifest_cache);
			$item->current_version    = $manifest->version ?? Text::_('JLIB_UNKNOWN');
			$item->description        = $item->description !== '' ? $item->description : Text::_('COM_INSTALLER_MSG_UPDATE_NODESC');
			$item->type_translated    = Text::_('COM_INSTALLER_TYPE_' . strtoupper($item->type));
			$item->folder_translated  = $item->folder ?: Text::_('COM_INSTALLER_TYPE_NONAPPLICABLE');
			$item->install_type       = $item->extension_id ? Text::_('COM_INSTALLER_MSG_UPDATE_UPDATE') : Text::_('COM_INSTALLER_NEW_INSTALL');
		}

		return $items;
	}

	/**
	 * Returns an object list
	 *
	 * @param   \JDatabaseQuery  $query       The query
	 * @param   int              $limitstart  Offset
	 * @param   int              $limit       The number of records
	 *
	 * @return  array
	 *
	 * @since   3.5
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$db = $this->getDbo();
		$listOrder = $this->getState('list.ordering', 'u.name');
		$listDirn  = $this->getState('list.direction', 'asc');

		// Process ordering.
		if (in_array($listOrder, array('client_translated', 'folder_translated', 'type_translated')))
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
			$this->translate($result);
			$result = ArrayHelper::sortObjects($result, $listOrder, strtolower($listDirn) === 'desc' ? -1 : 1, true, true);
			$total = count($result);

			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}

			return array_slice($result, $limitstart, $limit ?: null);
		}
		else
		{
			$query->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn));

			$result = parent::_getList($query, $limitstart, $limit);
			$this->translate($result);

			return $result;
		}
	}

	/**
	 * Get the count of disabled update sites
	 *
	 * @return  integer
	 *
	 * @since   3.4
	 */
	public function getDisabledUpdateSites()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true)
			->select('COUNT(*)')
			->from($db->quoteName('#__update_sites'))
			->where($db->quoteName('enabled') . ' = 0');

		$db->setQuery($query);

		return $db->loadResult();
	}

	/**
	 * Finds updates for an extension.
	 *
	 * @param   int  $eid               Extension identifier to look for
	 * @param   int  $cacheTimeout      Cache timeout
	 * @param   int  $minimumStability  Minimum stability for updates {@see Updater} (0=dev, 1=alpha, 2=beta, 3=rc, 4=stable)
	 *
	 * @return  boolean Result
	 *
	 * @since   1.6
	 */
	public function findUpdates($eid = 0, $cacheTimeout = 0, $minimumStability = Updater::STABILITY_STABLE)
	{
		Updater::getInstance()->findUpdates($eid, $cacheTimeout, $minimumStability);

		return true;
	}

	/**
	 * Removes all of the updates from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   1.6
	 */
	public function purge()
	{
		$db = $this->getDbo();

		try
		{
			$db->truncateTable('#__updates');
		}
		catch (ExecutionFailureException $e)
		{
			$this->_message = Text::_('JLIB_INSTALLER_FAILED_TO_PURGE_UPDATES');

			return false;
		}

		// Reset the last update check timestamp
		$query = $db->getQuery(true)
			->update($db->quoteName('#__update_sites'))
			->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote(0));
		$db->setQuery($query);
		$db->execute();

		// Clear the administrator cache
		$this->cleanCache('_system');

		$this->_message = Text::_('JLIB_INSTALLER_PURGED_UPDATES');

		return true;
	}

	/**
	 * Update function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param   int[]  $uids              List of updates to apply
	 * @param   int    $minimumStability  The minimum allowed stability for installed updates {@see Updater}
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function update($uids, $minimumStability = Updater::STABILITY_STABLE)
	{
		$result = true;

		foreach ($uids as $uid)
		{
			$update = new Update;
			$instance = new \Joomla\CMS\Table\Update($this->getDbo());

			if (!$instance->load($uid))
			{
				// Update no longer available, maybe already updated by a package.
				continue;
			}

			$update->loadFromXml($instance->detailsurl, $minimumStability);

			// Find and use extra_query from update_site if available
			$updateSiteInstance = new \Joomla\CMS\Table\UpdateSite($this->getDbo());
			$updateSiteInstance->load($instance->update_site_id);

			if ($updateSiteInstance->extra_query)
			{
				$update->set('extra_query', $updateSiteInstance->extra_query);
			}

			$this->preparePreUpdate($update, $instance);

			// Install sets state and enqueues messages
			$res = $this->install($update);

			if ($res)
			{
				$instance->delete($uid);
			}

			$result = $res & $result;
		}

		// Clear the cached extension data and menu cache
		$this->cleanCache('_system');
		$this->cleanCache('com_modules');
		$this->cleanCache('com_plugins');
		$this->cleanCache('mod_menu');

		// Set the final state
		$this->setState('result', $result);
	}

	/**
	 * Handles the actual update installation.
	 *
	 * @param   Update  $update  An update definition
	 *
	 * @return  boolean   Result of install
	 *
	 * @since   1.6
	 */
	private function install($update)
	{
		// Load overrides plugin.
		PluginHelper::importPlugin('installer');

		$app = Factory::getApplication();

		if (!isset($update->get('downloadurl')->_data))
		{
			Factory::getApplication()->enqueueMessage(Text::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'), 'error');

			return false;
		}

		$url     = trim($update->downloadurl->_data);
		$sources = $update->get('downloadSources', array());

		if ($extra_query = $update->get('extra_query'))
		{
			$url .= (strpos($url, '?') === false) ? '?' : '&amp;';
			$url .= $extra_query;
		}

		$mirror = 0;

		while (!($p_file = InstallerHelper::downloadPackage($url)) && isset($sources[$mirror]))
		{
			$name = $sources[$mirror];
			$url  = trim($name->url);

			if ($extra_query)
			{
				$url .= (strpos($url, '?') === false) ? '?' : '&amp;';
				$url .= $extra_query;
			}

			$mirror++;
		}

		// Was the package downloaded?
		if (!$p_file)
		{
			Factory::getApplication()->enqueueMessage(Text::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url), 'error');

			return false;
		}

		$config   = $app->getConfig();
		$tmp_dest = $config->get('tmp_path');

		// Unpack the downloaded package file
		$package = InstallerHelper::unpack($tmp_dest . '/' . $p_file);

		if (empty($package))
		{
			$app->enqueueMessage(Text::sprintf('COM_INSTALLER_UNPACK_ERROR', $p_file), 'error');

			return false;
		}

		// Get an installer instance
		$installer = Installer::getInstance();
		$update->set('type', $package['type']);

		// Check the package
		$check = InstallerHelper::isChecksumValid($package['packagefile'], $update);

		if ($check === InstallerHelper::HASH_NOT_VALIDATED)
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_INSTALL_CHECKSUM_WRONG'), 'error');

			return false;
		}

		if ($check === InstallerHelper::HASH_NOT_PROVIDED)
		{
			$app->enqueueMessage(Text::_('COM_INSTALLER_INSTALL_CHECKSUM_WARNING'), 'warning');
		}

		// Install the package
		if (!$installer->update($package['dir']))
		{
			// There was an error updating the package
			$app->enqueueMessage(
				Text::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR',
					Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type']))
				), 'error'
			);
			$result = false;
		}
		else
		{
			// Package updated successfully
			$app->enqueueMessage(
				Text::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS',
					Text::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type']))
				), 'success'
			);
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		InstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A \JForm object on success, false on failure
	 *
	 * @since	2.5.2
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		\JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		\JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = \JForm::getInstance('com_installer.update', 'update', array('load_data' => $loadData));

		// Check for an error.
		if ($form == false)
		{
			$this->setError($form->getMessage());

			return false;
		}

		// Check the session for previously entered form data.
		$data = $this->loadFormData();

		// Bind the form data if present.
		if (!empty($data))
		{
			$form->bind($data);
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since	2.5.2
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState($this->context, array());

		return $data;
	}

	/**
	 * Method to add parameters to the update
	 *
	 * @param   Update                    $update  An update definition
	 * @param   \Joomla\CMS\Table\Update  $table   The update instance from the database
	 *
	 * @return  void
	 *
	 * @since   3.7.0
	 */
	protected function preparePreUpdate($update, $table)
	{
		switch ($table->type)
		{
			// Components could have a helper which adds additional data
			case 'component':
				$ename = str_replace('com_', '', $table->element);
				$fname = $ename . '.php';
				$cname = ucfirst($ename) . 'Helper';

				$path = JPATH_ADMINISTRATOR . '/components/' . $table->element . '/helpers/' . $fname;

				if (File::exists($path))
				{
					require_once $path;

					if (class_exists($cname) && is_callable(array($cname, 'prepareUpdate')))
					{
						call_user_func_array(array($cname, 'prepareUpdate'), array(&$update, &$table));
					}
				}

				break;

			// Modules could have a helper which adds additional data
			case 'module':
				$cname = str_replace('_', '', $table->element) . 'Helper';
				$path = ($table->client_id ? JPATH_ADMINISTRATOR : JPATH_SITE) . '/modules/' . $table->element . '/helper.php';

				if (File::exists($path))
				{
					require_once $path;

					if (class_exists($cname) && is_callable(array($cname, 'prepareUpdate')))
					{
						call_user_func_array(array($cname, 'prepareUpdate'), array(&$update, &$table));
					}
				}

				break;

			// If we have a plugin, we can use the plugin trigger "onInstallerBeforePackageDownload"
			// But we should make sure, that our plugin is loaded, so we don't need a second "installer" plugin
			case 'plugin':
				$cname = str_replace('plg_', '', $table->element);
				PluginHelper::importPlugin($table->folder, $cname);
				break;
		}
	}

	/**
	 * Manipulate the query to be used to evaluate if this is an Empty State to provide specific conditions for this extension.
	 *
	 * @return DatabaseQuery
	 *
	 * @since 4.0.0
	 */
	protected function getEmptyStateQuery()
	{
		$query = parent::getEmptyStateQuery();

		$query->where($this->_db->quoteName('extension_id') . ' != 0');

		return $query;
	}
}
