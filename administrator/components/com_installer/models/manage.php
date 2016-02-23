<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/extension.php';

/**
 * Installer Manage Model
 *
 * @since  1.5
 */
class InstallerModelManage extends InstallerModel
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
				'status',
				'name',
				'client_id',
				'type',
				'folder',
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
	 * @since   1.6
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		$app = JFactory::getApplication();

		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.status', $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder', $this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		$this->setState('message', $app->getUserState('com_installer.message'));
		$this->setState('extension_message', $app->getUserState('com_installer.extension_message'));
		$app->setUserState('com_installer.message', '');
		$app->setUserState('com_installer.extension_message', '');

		parent::populateState($ordering, $direction);
	}

	/**
	 * Enable/Disable an extension.
	 *
	 * @param   array  &$eid   Extension ids to un/publish
	 * @param   int    $value  Publish value
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function publish(&$eid = array(), $value = 1)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.edit.state', 'com_installer'))
		{
			JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));

			return false;
		}

		$result = true;

		/*
		 * Ensure eid is an array of extension ids
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid))
		{
			$eid = array($eid);
		}

		// Get a table object for the extension type
		$table = JTable::getInstance('Extension');
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_templates/tables');

		// Enable the extension in the table and store it in the database
		foreach ($eid as $i => $id)
		{
			$table->load($id);

			if ($table->type == 'template')
			{
				$style = JTable::getInstance('Style', 'TemplatesTable');

				if ($style->load(array('template' => $table->element, 'client_id' => $table->client_id, 'home' => 1)))
				{
					JError::raiseNotice(403, JText::_('COM_INSTALLER_ERROR_DISABLE_DEFAULT_TEMPLATE_NOT_PERMITTED'));
					unset($eid[$i]);
					continue;
				}
			}

			if ($table->protected == 1)
			{
				$result = false;
				JError::raiseWarning(403, JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'));
			}
			else
			{
				$table->enabled = $value;
			}

			if (!$table->store())
			{
				$this->setError($table->getError());
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Refreshes the cached manifest information for an extension.
	 *
	 * @param   int  $eid  extension identifier (key in #__extensions)
	 *
	 * @return  boolean  result of refresh
	 *
	 * @since   1.6
	 */
	public function refresh($eid)
	{
		if (!is_array($eid))
		{
			$eid = array($eid => 0);
		}

		// Get an installer object for the extension type
		$installer = JInstaller::getInstance();
		$result = 0;

		// Uninstall the chosen extensions
		foreach ($eid as $id)
		{
			$result |= $installer->refreshManifestCache($id);
		}

		return $result;
	}

	/**
	 * Remove (uninstall) an extension
	 *
	 * @param   array  $eid  An array of identifiers
	 *
	 * @return  boolean  True on success
	 *
	 * @since   1.5
	 */
	public function remove($eid = array())
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.delete', 'com_installer'))
		{
			JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));

			return false;
		}

		$failed = array();

		/*
		 * Ensure eid is an array of extension ids in the form id => client_id
		 * TODO: If it isn't an array do we want to set an error and fail?
		 */
		if (!is_array($eid))
		{
			$eid = array($eid => 0);
		}

		// Get an installer object for the extension type
		$installer = JInstaller::getInstance();
		$row = JTable::getInstance('extension');

		// Uninstall the chosen extensions
		$msgs = array();
		$result = false;

		foreach ($eid as $id)
		{
			$id = trim($id);
			$row->load($id);
			$result = false;

			$langstring = 'COM_INSTALLER_TYPE_TYPE_' . strtoupper($row->type);
			$rowtype = JText::_($langstring);

			if (strpos($rowtype, $langstring) !== false)
			{
				$rowtype = $row->type;
			}

			if ($row->type && $row->type != 'language')
			{
				$result = $installer->uninstall($row->type, $id);

				// Build an array of extensions that failed to uninstall
				if ($result === false)
				{
					// There was an error in uninstalling the package
					$msgs[] = JText::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype);

					continue;
				}

				// Package uninstalled sucessfully
				$msgs[] = JText::sprintf('COM_INSTALLER_UNINSTALL_SUCCESS', $rowtype);
				$result = true;

				continue;
			}

			if ($row->type == 'language')
			{
				// One should always uninstall a language package, not a single language
				$msgs[] = JText::_('COM_INSTALLER_UNINSTALL_LANGUAGE');

				continue;
			}

			// There was an error in uninstalling the package
			$msgs[] = JText::sprintf('COM_INSTALLER_UNINSTALL_ERROR', $rowtype);
		}

		$msg = implode("<br />", $msgs);
		$app = JFactory::getApplication();
		$app->enqueueMessage($msg);
		$this->setState('action', 'remove');
		$this->setState('name', $installer->get('name'));
		$app->setUserState('com_installer.message', $installer->message);
		$app->setUserState('com_installer.extension_message', $installer->get('extension_message'));

		return $result;
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
		$query = $this->getDbo()->getQuery(true)
			->select('*')
			->select('2*protected+(1-protected)*enabled AS status')
			->from('#__extensions')
			->where('state = 0');

		// Process select filters.
		$status   = $this->getState('filter.status');
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($status != '')
		{
			if ($status == '2')
			{
				$query->where('protected = 1');
			}
			elseif ($status == '3')
			{
				$query->where('protected = 0');
			}
			else
			{
				$query->where('protected = 0')
					->where('enabled = ' . (int) $status);
			}
		}

		if ($type)
		{
			$query->where('type = ' . $this->_db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where('client_id = ' . (int) $clientId);
		}

		if ($folder != '')
		{
			$query->where('folder = ' . $this->_db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (extension id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where('extension_id = ' . (int) substr($search, 3));
		}

		return $query;
	}
}
