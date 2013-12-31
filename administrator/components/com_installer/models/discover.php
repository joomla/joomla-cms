<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/extension.php';

/**
 * Installer Discover Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerModelDiscover extends InstallerModel
{
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
	 * @since   3.1
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$app = JFactory::getApplication();

		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

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
	 * Method to get the database query.
	 *
	 * @return  JDatabaseQuery  the database query
	 *
	 * @since   3.1
	 */
	protected function getListQuery()
	{
		$type   = $this->getState('filter.type');
		$client = $this->getState('filter.client_id');
		$group  = $this->getState('filter.group');

		$query = JFactory::getDbo()->getQuery(true)
			->select('*')
			->from('#__extensions')
			->where('state=-1');

		if ($type)
		{
			$query->where('type=' . $this->_db->quote($type));
		}

		if ($client != '')
		{
			$query->where('client_id=' . (int) $client);
		}

		if ($group != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where('folder=' . $this->_db->quote($group == '*' ? '' : $group));
		}

		// Filter by search in id
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where('extension_id = ' . (int) substr($search, 3));
		}

		return $query;
	}

	/**
	 * Discover extensions.
	 *
	 * Finds uninstalled extensions
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function discover()
	{
		// Purge the list of discovered extensions
		$this->purge();

		$installer	= JInstaller::getInstance();
		$results	= $installer->discover();

		// Get all templates, including discovered ones
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('extension_id, element, folder, client_id, type')
			->from('#__extensions');

		$db->setQuery($query);
		$installedtmp = $db->loadObjectList();
		$extensions = array();

		foreach ($installedtmp as $install)
		{
			$key = implode(':', array($install->type, $install->element, $install->folder, $install->client_id));
			$extensions[$key] = $install;
		}

		unset($installedtmp);

		foreach ($results as $result)
		{
			// Check if we have a match on the element
			$key = implode(':', array($result->type, $result->element, $result->folder, $result->client_id));

			if (!array_key_exists($key, $extensions))
			{
				// Put it into the table
				$result->store();
			}
		}
	}

	/**
	 * Installs a discovered extension.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public function discover_install()
	{
		$app       = JFactory::getApplication();
		$installer = JInstaller::getInstance();
		$eid       = JRequest::getVar('cid', 0);

		if (is_array($eid) || $eid)
		{
			if (!is_array($eid))
			{
				$eid = array($eid);
			}

			JArrayHelper::toInteger($eid);
			$failed = false;

			foreach ($eid as $id)
			{
				$result = $installer->discover_install($id);

				if (!$result)
				{
					$failed = true;
					$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLFAILED') . ': ' . $id);
				}
			}

			$this->setState('action', 'remove');
			$this->setState('name', $installer->get('name'));
			$app->setUserState('com_installer.message', $installer->message);
			$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

			if (!$failed)
			{
				$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLSUCCESSFUL'));
			}
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_NOEXTENSIONSELECTED'));
		}
	}

	/**
	 * Cleans out the list of discovered extensions.
	 *
	 * @return  bool True on success
	 *
	 * @since   1.6
	 */
	public function purge()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true)
			->delete('#__extensions')
			->where('state = -1');
		$db->setQuery($query);

		if ($db->execute())
		{
			$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_PURGEDDISCOVEREDEXTENSIONS');

			return true;
		}
		else
		{
			$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_FAILEDTOPURGEEXTENSIONS');

			return false;
		}
	}
}
