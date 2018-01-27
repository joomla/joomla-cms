<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

JLoader::register('InstallerModel', __DIR__ . '/extension.php');

/**
 * Installer Discover Model
 *
 * @since  1.6
 */
class InstallerModelDiscover extends InstallerModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.5
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'name',
				'client_id',
				'client', 'client_translated',
				'type', 'type_translated',
				'folder', 'folder_translated',
				'extension_id',
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
	 * @since   3.1
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		$app = JFactory::getApplication();

		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));

		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState($ordering, $direction);
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
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select('*')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('state') . ' = -1');

		// Process select filters.
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($type)
		{
			$query->where($db->quoteName('type') . ' = ' . $db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where($db->quoteName('client_id') . ' = ' . (int) $clientId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where($db->quoteName('folder') . ' = ' . $db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('extension_id') . ' = ' . (int) substr($search, 3));
			}
		}

		// Note: The search for name, ordering and pagination are processed by the parent InstallerModel class (in extension.php).

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
		// Purge the list of discovered extensions and fetch them again.
		$this->purge();
		$results = JInstaller::getInstance()->discover();

		// Get all templates, including discovered ones
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('extension_id', 'element', 'folder', 'client_id', 'type')))
			->from($db->quoteName('#__extensions'));
		$db->setQuery($query);
		$installedtmp = $db->loadObjectList();

		$extensions = array();

		foreach ($installedtmp as $install)
		{
			$key = implode(':', array($install->type, $install->element, $install->folder, $install->client_id));
			$extensions[$key] = $install;
		}

		foreach ($results as $result)
		{
			// Check if we have a match on the element
			$key = implode(':', array($result->type, $result->element, $result->folder, $result->client_id));

			if (!array_key_exists($key, $extensions))
			{
				// Put it into the table
				$result->check();
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
		$app   = JFactory::getApplication();
		$input = $app->input;
		$eid   = $input->get('cid', 0, 'array');

		if (is_array($eid) || $eid)
		{
			if (!is_array($eid))
			{
				$eid = array($eid);
			}

			$eid = ArrayHelper::toInteger($eid);
			$failed = false;

			foreach ($eid as $id)
			{
				$installer = new JInstaller;

				$result = $installer->discover_install($id);

				if (!$result)
				{
					$failed = true;
					$app->enqueueMessage(JText::_('COM_INSTALLER_MSG_DISCOVER_INSTALLFAILED') . ': ' . $id);
				}
			}

			// TODO - We are only receiving the message for the last JInstaller instance
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
		$db = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__extensions'))
			->where($db->quoteName('state') . ' = -1');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (JDatabaseExceptionExecuting $e)
		{
			$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_FAILEDTOPURGEEXTENSIONS');

			return false;
		}

		$this->_message = JText::_('COM_INSTALLER_MSG_DISCOVER_PURGEDDISCOVEREDEXTENSIONS');

		return true;
	}
}
