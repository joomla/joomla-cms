<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

jimport('joomla.updater.update');

/**
 * Installer Update Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerModelUpdate extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name',
				'client_id',
				'type',
				'folder',
				'extension_id',
				'update_id',
				'update_site_id',
			);
		}

		parent::__construct($config);
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
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();
		$value = $app->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $value);

		$clientId = $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', '');
		$this->setState('filter.client_id', $clientId);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $categoryId);

		$group = $this->getUserStateFromRequest($this->context . '.filter.group', 'filter_group', '');
		$this->setState('filter.group', $group);

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState('name', 'asc');
	}

	/**
	 * Method to get the database query
	 *
	 * @return  JDatabaseQuery  The database query
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		$query = $db->getQuery(true);
		$type = $this->getState('filter.type');
		$client = $this->getState('filter.client_id');
		$group = $this->getState('filter.group');

		// Grab updates ignoring new installs
		$query->select('*')
			->from('#__updates')
			->where('extension_id != 0')
			->order($this->getState('list.ordering') . ' ' . $this->getState('list.direction'));

		if ($type)
		{
			$query->where('type=' . $db->quote($type));
		}
		if ($client != '')
		{
			$query->where('client_id = ' . intval($client));
		}
		if ($group != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where('folder=' . $db->quote($group == '*' ? '' : $group));
		}

		// Filter by extension_id
		if ($eid = $this->getState('filter.extension_id'))
		{
			$query->where($db->quoteName('extension_id') . ' = ' . $db->quote((int) $eid));
		}
		else
		{
			$query->where($db->quoteName('extension_id') . ' != ' . $db->quote(0))
				->where($db->quoteName('extension_id') . ' != ' . $db->quote(700));
		}

		// Filter by search
		$search = $this->getState('filter.search');
		if (!empty($search))
		{
			$query->where('name LIKE ' . $db->quote('%' . $search . '%'));
		}
		return $query;
	}

	/**
	 * Finds updates for an extension.
	 *
	 * @param   int  $eid            Extension identifier to look for
	 * @param   int  $cache_timeout  Cache timout
	 *
	 * @return  boolean Result
	 *
	 * @since   1.6
	 */
	public function findUpdates($eid = 0, $cache_timeout = 0)
	{
		// Purge the updates list
		$this->purge();

		$updater = JUpdater::getInstance();
		$updater->findUpdates($eid, $cache_timeout);
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
		$db = JFactory::getDbo();

		// Note: TRUNCATE is a DDL operation
		// This may or may not mean depending on your database
		$db->setQuery('TRUNCATE TABLE #__updates');
		if ($db->execute())
		{
			// Reset the last update check timestamp
			$query = $db->getQuery(true)
				->update($db->quoteName('#__update_sites'))
				->set($db->quoteName('last_check_timestamp') . ' = ' . $db->quote(0));
			$db->setQuery($query);
			$db->execute();
			$this->_message = JText::_('COM_INSTALLER_PURGED_UPDATES');
			return true;
		}
		else
		{
			$this->_message = JText::_('COM_INSTALLER_FAILED_TO_PURGE_UPDATES');
			return false;
		}
	}

	/**
	 * Enables any disabled rows in #__update_sites table
	 *
	 * @return  boolean result of operation
	 *
	 * @since   1.6
	 */
	public function enableSites()
	{
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->update('#__update_sites')
			->set('enabled = 1')
			->where('enabled = 0');
		$db->setQuery($query);
		if ($db->execute())
		{
			if ($rows = $db->getAffectedRows())
			{
				$this->_message .= JText::plural('COM_INSTALLER_ENABLED_UPDATES', $rows);
			}
			return true;
		}
		else
		{
			$this->_message .= JText::_('COM_INSTALLER_FAILED_TO_ENABLE_UPDATES');
			return false;
		}
	}

	/**
	 * Update function.
	 *
	 * Sets the "result" state with the result of the operation.
	 *
	 * @param   array  $uids  Array[int] List of updates to apply
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function update($uids)
	{
		$result = true;
		foreach ($uids as $uid)
		{
			$update = new JUpdate;
			$instance = JTable::getInstance('update');
			$instance->load($uid);
			$update->loadFromXML($instance->detailsurl);
			$update->set('extra_query', $instance->extra_query);

			// Install sets state and enqueues messages
			$res = $this->install($update);

			if ($res)
			{
				$instance->delete($uid);
			}

			$result = $res & $result;
		}

		// Set the final state
		$this->setState('result', $result);
	}

	/**
	 * Handles the actual update installation.
	 *
	 * @param   JUpdate  $update  An update definition
	 *
	 * @return  boolean   Result of install
	 *
	 * @since   1.6
	 */
	private function install($update)
	{
		$app = JFactory::getApplication();
		if (isset($update->get('downloadurl')->_data))
		{
			$url = $update->downloadurl->_data;

			$extra_query = $update->get('extra_query');

			if ($extra_query)
			{
				if (strpos($url, '?') === false)
				{
					$url .= '?';
				}
				else
				{
					$url .= '&amp;';
				}

				$url .= $extra_query;
			}
		}
		else
		{
			JError::raiseWarning('', JText::_('COM_INSTALLER_INVALID_EXTENSION_UPDATE'));
			return false;
		}

		$p_file = JInstallerHelper::downloadPackage($url);

		// Was the package downloaded?
		if (!$p_file)
		{
			JError::raiseWarning('', JText::sprintf('COM_INSTALLER_PACKAGE_DOWNLOAD_FAILED', $url));
			return false;
		}

		$config		= JFactory::getConfig();
		$tmp_dest	= $config->get('tmp_path');

		// Unpack the downloaded package file
		$package	= JInstallerHelper::unpack($tmp_dest . '/' . $p_file);

		// Get an installer instance
		$installer	= JInstaller::getInstance();
		$update->set('type', $package['type']);

		// Install the package
		if (!$installer->update($package['dir']))
		{
			// There was an error updating the package
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_ERROR', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = false;
		}
		else
		{
			// Package updated successfully
			$msg = JText::sprintf('COM_INSTALLER_MSG_UPDATE_SUCCESS', JText::_('COM_INSTALLER_TYPE_TYPE_' . strtoupper($package['type'])));
			$result = true;
		}

		// Quick change
		$this->type = $package['type'];

		// Set some model state values
		$app->enqueueMessage($msg);

		// TODO: Reconfigure this code when you have more battery life left
		$this->setState('name', $installer->get('name'));
		$this->setState('result', $result);
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		// Cleanup the install files
		if (!is_file($package['packagefile']))
		{
			$config = JFactory::getConfig();
			$package['packagefile'] = $config->get('tmp_path') . '/' . $package['packagefile'];
		}

		JInstallerHelper::cleanupInstall($package['packagefile'], $package['extractdir']);

		return $result;
	}

	/**
	* Method to get the row form.
	*
	* @param   array    $data      Data for the form.
	* @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	*
	* @return  mixed  A JForm object on success, false on failure
	*
	* @since	2.5.2
	*/
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.update', 'update', array('load_data' => $loadData));

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
		$data = JFactory::getApplication()->getUserState($this->context . '.data', array());
		return $data;
	}
}
