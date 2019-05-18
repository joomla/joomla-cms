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
use Joomla\Database\DatabaseQuery;

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
	 * @since   __DEPLOY_VERSION__
	 * @see     \Joomla\CMS\MVC\Model\ListModel
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
	 * @return  DatabaseQuery  The database query
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		$db = $this->getDbo();
		/** @var DatabaseQuery $query */
		$query = $db->getQuery(true)
			->select(
				array(
					'sites.update_site_id',
					'sites.name AS update_site_name',
					'sites.type AS update_site_type',
					'sites.extra_query AS extra_query',
					'sites.location',
					'extensions.extension_id',
					'extensions.name',
					'extensions.type',
					'extensions.element',
					'extensions.folder',
					'extensions.client_id',
					'extensions.manifest_cache',
				)
			)
			->from($db->quoteName('#__update_sites', 'sites'))
			->innerJoin(
				$db->quoteName('#__update_sites_extensions', 'sites_extensions') .
				' ON ' . $db->quoteName('sites_extensions.update_site_id') .
				' = ' . $db->quoteName('sites.update_site_id')
			)
			->innerJoin(
				$db->quoteName('#__extensions', 'extensions') .
				' ON ' . $db->quoteName('extensions.extension_id') .
				' = ' . $db->quoteName('sites_extensions.extension_id')
			)
			->where($db->quoteName('sites.location') . ' NOT LIKE ' . $db->quote('%.joomla.org/%'));

		// Process select filters.
		$enabled  = $this->getState('filter.enabled');
		$type     = $this->getState('filter.type');
		$clientId = $this->getState('filter.client_id');
		$folder   = $this->getState('filter.folder');

		if ($enabled != '')
		{
			$query->where($db->quoteName('sites.enabled') . ' = ' . (int) $enabled);
		}

		if ($type)
		{
			$query->where($db->quoteName('extensions.type') . ' = ' . $db->quote($type));
		}

		if ($clientId != '')
		{
			$query->where($db->quoteName('extensions.client_id') . ' = ' . (int) $clientId);
		}

		if ($folder != '' && in_array($type, array('plugin', 'library', '')))
		{
			$query->where($db->quoteName('extensions.folder') . ' = ' . $db->quote($folder == '*' ? '' : $folder));
		}

		// Process search filter (update site id).
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('sites.update_site_id') . ' = ' . (int) substr($search, 3));
			}
			else
			{
				$query->where($db->quoteName('sites.name') . ' LIKE ' . $db->quote('%' . $search . '%'));
			}
		}

		return $query;
	}
}
