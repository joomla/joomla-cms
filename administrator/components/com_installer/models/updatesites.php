<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/extension.php';

/**
 * Installer Update Sites Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 * @since       3.4
 */
class InstallerModelUpdatesites extends InstallerModel
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JController
	 * @since   3.4
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'update_site_name', 'name', 'client_id',
				'status', 'type', 'folder', 'update_site_id',
				'enabled'
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
	 * @since   3.4
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		// Load the filter state.
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		$clientId = $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', '');
		$this->setState('filter.client_id', $clientId);

		$status = $this->getUserStateFromRequest($this->context . '.filter.enabled', 'filter_enabled', '');
		$this->setState('filter.enabled', $status);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '');
		$this->setState('filter.type', $categoryId);

		$group = $this->getUserStateFromRequest($this->context . '.filter.group', 'filter_group', '');
		$this->setState('filter.group', $group);

		parent::populateState('name', 'asc');
	}

	/**
	 * Enable/Disable an extension.
	 *
	 * @param   array  &$eid   Extension ids to un/publish
	 * @param   int    $value  Publish value
	 *
	 * @return  boolean  True on success
	 *
	 * @since   3.4
	 *
	 * @throws  Exception on ACL error
	 */
	public function publish(&$eid = array(), $value = 1)
	{
		$user = JFactory::getUser();

		if (!$user->authorise('core.edit.state', 'com_installer'))
		{
			throw new Exception(JText::_('JLIB_APPLICATION_ERROR_EDITSTATE_NOT_PERMITTED'), 403);
		}

		$result = true;

		// Ensure eid is an array of extension ids
		if (!is_array($eid))
		{
			$eid = array($eid);
		}

		// Get a table object for the extension type
		$table = JTable::getInstance('Updatesite');

		// Enable the update site in the table and store it in the database
		foreach ($eid as $i => $id)
		{
			$table->load($id);
			$table->enabled = $value;

			if (!$table->store())
			{
				$this->setError($table->getError());
				$result = false;
			}
		}

		return $result;
	}

	/**
	 * Method to get the database query
	 *
	 * @return  JDatabaseQuery  The database query
	 *
	 * @since   3.4
	 */
	protected function getListQuery()
	{
		$enabled = $this->getState('filter.enabled');
		$type    = $this->getState('filter.type');
		$client  = $this->getState('filter.client_id');
		$group   = $this->getState('filter.group');

		$query = JFactory::getDbo()->getQuery(true)
			->select(
				array(
					's.update_site_id',
					's.name as update_site_name',
					's.type as update_site_type',
					's.location',
					's.enabled',
					'e.extension_id',
					'e.name',
					'e.type',
					'e.element',
					'e.folder',
					'e.client_id',
					'e.state',
					'e.manifest_cache',
				)
			)
			->from('#__update_sites AS s')
			->innerJoin('#__update_sites_extensions AS se on(se.update_site_id = s.update_site_id)')
			->innerJoin('#__extensions AS e ON(e.extension_id = se.extension_id)')
			->where('state=0');

		if ($enabled != '')
		{
			$query->where('s.enabled=' . (int) $enabled);
		}

		if ($type)
		{
			$query->where('e.type=' . $this->_db->quote($type));
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
			$query->where('s.update_site_id = ' . (int) substr($search, 3));
		}

		return $query;
	}

	/**
	 * Returns an object list
	 *
	 * @param   string  $query       The query
	 * @param   int     $limitstart  Offset
	 * @param   int     $limit       The number of records
	 *
	 * @return  array
	 *
	 * @since   3.4
	 */
	protected function _getList($query, $limitstart = 0, $limit = 0)
	{
		$ordering = $this->getState('list.ordering');
		$search   = $this->getState('filter.search');

		// Replace slashes so preg_match will work
		$search = str_replace('/', ' ', $search);
		$db     = $this->getDbo();

		if ($ordering == 'name' || (!empty($search) && stripos($search, 'id:') !== 0))
		{
			$db->setQuery($query);
			$result = $db->loadObjectList();
			$this->translate($result);

			if (!empty($search) && (stripos($search, 'id:') !== 0))
			{
				foreach ($result as $i => $item)
				{
					if (!preg_match("/$search/i", $item->name) && !preg_match("/$search/i", $item->update_site_name))
					{
						unset($result[$i]);
					}
				}
			}

			JArrayHelper::sortObjects($result, $this->getState('list.ordering'), $this->getState('list.direction') == 'desc' ? -1 : 1, true, true);

			$total = count($result);
			$this->cache[$this->getStoreId('getTotal')] = $total;

			if ($total < $limitstart)
			{
				$limitstart = 0;
				$this->setState('list.start', 0);
			}

			return array_slice($result, $limitstart, $limit ? $limit : null);
		}

		$query->order($db->quoteName($ordering) . ' ' . $this->getState('list.direction'));
		$result = parent::_getList($query, $limitstart, $limit);
		$this->translate($result);

		return $result;
	}
}
