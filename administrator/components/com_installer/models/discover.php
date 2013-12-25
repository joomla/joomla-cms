<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Import library dependencies
require_once dirname(__FILE__) . '/extension.php';

/**
 * Installer Manage Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       1.6
 */
class InstallerModelDiscover extends InstallerModel
{
	protected $_context = 'com_installer.discover';

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since	1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Initialise variables.
		$app     = JFactory::getApplication();
		$filters = JRequest::getVar('filters');

		if (empty($filters))
		{
			$data = $app->getUserState($this->context . '.data');
			$filters = $data['filters'];
		}
		else
		{
			$app->setUserState($this->context . '.data', array('filters' => $filters));
		}

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		$this->setState('filter.search', isset($filters['search']) ? $filters['search'] : '');
		$this->setState('filter.type', isset($filters['type']) ? $filters['type'] : '');
		$this->setState('filter.group', isset($filters['group']) ? $filters['group'] : '');
		$this->setState('filter.client_id', isset($filters['client_id']) ? $filters['client_id'] : '');

		parent::populateState('name', 'asc');
	}

	/**
	 * Method to get the database query.
	 *
	 * @return	JDatabaseQuery the database query
	 *
	 * @since	1.6
	 */
	protected function getListQuery()
	{
		$type   = $this->getState('filter.type');
		$client = $this->getState('filter.client_id');
		$group  = $this->getState('filter.group');
		$query  = JFactory::getDBO()->getQuery(true);
		$query->select('*');
		$query->from('#__extensions');
		$query->where('state =-1');

		if ($type)
		{
			$query->where('type=' . $this->_db->Quote($type));
		}

		if ($client != '')
		{
			$query->where('client_id=' . intval($client));
		}

		if ($group != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where('folder=' . $this->_db->Quote($group == '*' ? '' : $group));
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
	 * @since	1.6
	 */
	function discover()
	{
		$installer	= JInstaller::getInstance();
		$results	= $installer->discover();

		// Get all templates, including discovered ones
		$query = 'SELECT extension_id, element, folder, client_id, type FROM #__extensions';
		$dbo = JFactory::getDBO();
		$dbo->setQuery($query);
		$installedtmp = $dbo->loadObjectList();
		$extensions = array();

		foreach($installedtmp as $install)
		{
			$key = implode(':', array($install->type, $install->element, $install->folder, $install->client_id));
			$extensions[$key] = $install;
		}

		unset($installedtmp);


		foreach($results as $result)
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
	 * @since	1.6
	 */
	function discover_install()
	{
		$app = JFactory::getApplication();
		$installer = JInstaller::getInstance();
		$eid = JRequest::getVar('cid', 0);

		if (is_array($eid) || $eid)
		{
			if (!is_array($eid))
			{
				$eid = array($eid);
			}

			JArrayHelper::toInteger($eid);
			$app = JFactory::getApplication();
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
	 * @since	1.6
	 */
	function purge()
	{
		$db		= JFactory::getDBO();
		$query	= $db->getQuery(true);
		$query->delete();
		$query->from('#__extensions');
		$query->where('state = -1');
		$db->setQuery((string) $query);

		if ($db->Query())
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

	/**
	 * Method to get the row form.
	 *
	 * @param   array    $data      Data for the form.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  mixed  A JForm object on success, false on failure
	 *
	 * @since	1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$app = JFactory::getApplication();
		JForm::addFormPath(JPATH_COMPONENT . '/models/forms');
		JForm::addFieldPath(JPATH_COMPONENT . '/models/fields');
		$form = JForm::getInstance('com_installer.discover', 'discover', array('load_data' => $loadData));

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
	 * @return	mixed	The data for the form.
	 *
	 * @since	1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = JFactory::getApplication()->getUserState('com_installer.discover.data', array());

		return $data;
	}
}
