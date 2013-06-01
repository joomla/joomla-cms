<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/extension.php';

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
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		$db		= JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from('#__extensions')
			->where('state=-1');
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
