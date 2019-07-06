<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_installer
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
	 * @see     \Joomla\CMS\MVC\Model\ListModel
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = [], MvcFactoryInterface $factory = null)
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

		array_walk($items,
			static function ($item) {
				$item->extra_query = InstallerHelper::getDownloadKey($item);
			}
		);

		return $items;
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
				$db->quoteName(
					[
						'sites.update_site_id',
						'sites.name',
						'sites.type',
						'sites.extra_query',
						'sites.location',
						'extensions.extension_id',
						'extensions.name',
						'extensions.type',
						'extensions.element',
						'extensions.folder',
						'extensions.client_id',
						'extensions.manifest_cache',
					],
					[
						'update_site_id',
						'update_site_name',
						'update_site_type',
						'extra_query',
						'location',
						'extension_id',
						'name',
						'type',
						'element',
						'folder',
						'client_id',
						'manifest_cache',
					]
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
		$enabled = $this->getState('filter.enabled', '');

		if ($enabled !== '')
		{
			$query->where($db->quoteName('sites.enabled') . ' = ' . (int) $enabled);
		}

		$type = $this->getState('filter.type');

		if ($type)
		{
			$query->where($db->quoteName('extensions.type') . ' = ' . $db->quote($type));
		}

		$clientId = $this->getState('filter.client_id', '');

		if ($clientId !== '')
		{
			$query->where($db->quoteName('extensions.client_id') . ' = ' . (int) $clientId);
		}

		$folder = $this->getState('filter.folder');

		if ($folder !== '' && in_array($type, array('plugin', 'library', ''), true))
		{
			$query->where($db->quoteName('extensions.folder') . ' = ' . $db->quote($folder === '*' ? '' : $folder));
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

		// Add the list ordering clause.
		$orderCol       = $this->state->get('list.ordering', 'update_site_name');
		$orderDirection = $this->state->get('list.direction', 'ASC');

		$query->order($db->escape($orderCol . ' ' . $orderDirection));

		return $query;
	}

	/**
	 * Method to get a store id based on model configuration state.
	 *
	 * This is necessary because the model is used by the component and
	 * different modules that might need different sets of data or different
	 * ordering requirements.
	 *
	 * @param   string  $id  A prefix for the store id.
	 *
	 * @return  string  A store id.
	 *
	 * @since   1.6
	 */
	protected function getStoreId($id = ''): string
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.enabled');
		$id .= ':' . $this->getState('filter.type');
		$id .= ':' . $this->getState('filter.folder');

		return parent::getStoreId($id);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * This method should only be called once per instantiation and is designed
	 * to be called on the first call to the getState() method unless the model
	 * configuration flag to ignore the request is set.
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
	protected function populateState($ordering = null, $direction = null): void
	{
		if ($ordering === null)
		{
			$ordering = 'update_site_name';
		}

		if ($direction === null)
		{
			$direction = 'ASC';
		}

		parent::populateState($ordering, $direction);
	}
}
