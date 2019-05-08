<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Installer\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Mvc\Factory\MvcFactoryInterface;
use Joomla\Component\Installer\Administrator\Helper\InstallerHelper;

/**
 * Installer Download Keys Model
 *
 * @since  __DEPLOY_VERSION__
 */
class DownloadkeysModel extends InstallerModel
{
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
				'client',
				'client_translated',
				'status',
				'type',
				'type_translated',
				'folder',
				'folder_translated',
				'update_site_id',
				'enabled',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to get an array of data items.
	 *
	 * @return  mixed  An array of data items on success, false on failure.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		$items = parent::getItems();

		foreach ($items as $item)
		{
			$item->extra_query = InstallerHelper::getDownloadKey($item);
		}

		return $items;
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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'name', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search',
			$this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.client_id',
			$this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', null, 'int'));
		$this->setState('filter.enabled',
			$this->getUserStateFromRequest($this->context . '.filter.enabled', 'filter_enabled', '', 'string'));
		$this->setState('filter.type',
			$this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'string'));
		$this->setState('filter.folder',
			$this->getUserStateFromRequest($this->context . '.filter.folder', 'filter_folder', '', 'string'));

		parent::populateState($ordering, $direction);
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
				array(
					's.update_site_id',
					's.name AS update_site_name',
					's.type AS update_site_type',
					's.extra_query AS extra_query',
					's.location',
					'e.extension_id',
					'e.name',
					'e.type',
					'e.element',
					'e.folder',
					'e.client_id',
					'e.manifest_cache',
				)
			)
			->from($db->quoteName('#__update_sites', 's'))
			->innerJoin(
				$db->quoteName('#__update_sites_extensions', 'se') .
				' ON ' . $db->quoteName('se.update_site_id') .
				' = ' . $db->quoteName('s.update_site_id')
			)
			->innerJoin(
				$db->quoteName('#__extensions', 'e') .
				' ON ' . $db->quoteName('e.extension_id') .
				' = ' . $db->quoteName('se.extension_id')
			)
			->where('location not like \'%.joomla.org/%\'');

		// Process select filters.
		$enabled  = $this->getState('filter.enabled');
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($enabled != '')
		{
			$query->where($db->quoteName('s.enabled') . ' = ' . $db->quote((int) $enabled));
		}

		if ($type)
		{
			$query->where($db->quoteName('e.type') . ' = ' . $db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where($db->quoteName('e.client_id') . ' = ' . $db->quote((int) $clientId));
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where($db->quoteName('e.folder') . ' = ' . $db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search) && stripos($search, 'id:') === 0)
		{
			$query->where($db->quoteName('s.update_site_id') . ' = ' . $db->quote((int) substr($search, 3)));
		}

		return $query;
	}
}
