<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('BannersHelper', JPATH_ADMINISTRATOR . '/components/com_banners/helpers/banners.php');

/**
 * Methods supporting a list of tracks.
 *
 * @since  1.6
 */
class BannersModelTracks extends JModelList
{
	/**
	 * The base name
	 *
	 * @var    string
	 * @since  1.6
	 */
	protected $basename;

	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @see     JControllerLegacy
	 * @since   1.6
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'b.name', 'banner_name',
				'cl.name', 'client_name', 'client_id',
				'c.title', 'category_title', 'category_id',
				'track_type', 'a.track_type', 'type',
				'count', 'a.count',
				'track_date', 'a.track_date', 'end', 'begin',
				'level', 'c.level',
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
	protected function populateState($ordering = 'b.name', $direction = 'asc')
	{
		// Load the filter state.
		$this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));
		$this->setState('filter.category_id', $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '', 'cmd'));
		$this->setState('filter.client_id', $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', '', 'cmd'));
		$this->setState('filter.type', $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type', '', 'cmd'));
		$this->setState('filter.level', $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level', '', 'cmd'));
		$this->setState('filter.begin', $this->getUserStateFromRequest($this->context . '.filter.begin', 'filter_begin', '', 'string'));
		$this->setState('filter.end', $this->getUserStateFromRequest($this->context . '.filter.end', 'filter_end', '', 'string'));

		// Load the parameters.
		$this->setState('params', JComponentHelper::getParams('com_banners'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select($db->quoteName(array('a.track_date', 'a.track_type', 'a.count')))
			->select($db->quoteName('b.name', 'banner_name'))
			->select($db->quoteName('cl.name', 'client_name'))
			->select($db->quoteName('c.title', 'category_title'));

		// From tracks table.
		$query->from($db->quoteName('#__banner_tracks', 'a'));

		// Join with the banners.
		$query->join('LEFT', $db->quoteName('#__banners', 'b') . ' ON ' . $db->quoteName('b.id') . ' = ' . $db->quoteName('a.banner_id'));

		// Join with the client.
		$query->join('LEFT', $db->quoteName('#__banner_clients', 'cl') . ' ON ' . $db->quoteName('cl.id') . ' = ' . $db->quoteName('b.cid'));

		// Join with the category.
		$query->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON ' . $db->quoteName('c.id') . ' = ' . $db->quoteName('b.catid'));

		// Filter by type.
		$type = $this->getState('filter.type');

		if (!empty($type))
		{
			$query->where($db->quoteName('a.track_type') . ' = ' . (int) $type);
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');

		if (is_numeric($clientId))
		{
			$query->where($db->quoteName('b.cid') . ' = ' . (int) $clientId);
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$query->where($db->quoteName('b.catid') . ' = ' . (int) $categoryId);
		}

		// Filter by begin date.

		$begin = $this->getState('filter.begin');

		if (!empty($begin))
		{
			$query->where($db->quoteName('a.track_date') . ' >= ' . $db->quote($begin));
		}

		// Filter by end date.
		$end = $this->getState('filter.end');

		if (!empty($end))
		{
			$query->where($db->quoteName('a.track_date') . ' <= ' . $db->quote($end));
		}

		// Filter on the level.
		if ($level = $this->getState('filter.level'))
		{
			$query->where($db->quoteName('c.level') . ' <= ' . (int) $level);
		}

		// Filter by search in banner name or client name.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$search = $db->quote('%' . strtolower($search) . '%');
			$query->where('(LOWER(b.name) LIKE ' . $search . ' OR LOWER(cl.name) LIKE ' . $search . ')');
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'b.name')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to delete rows.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 */
	public function delete()
	{
		$user       = JFactory::getUser();
		$categoryId = $this->getState('category_id');

		// Access checks.
		if ($categoryId)
		{
			$allow = $user->authorise('core.delete', 'com_banners.category.' . (int) $categoryId);
		}
		else
		{
			$allow = $user->authorise('core.delete', 'com_banners');
		}

		if ($allow)
		{
			// Delete tracks from this banner
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->delete($db->quoteName('#__banner_tracks'));

			// Filter by type
			$type = $this->getState('filter.type');

			if (!empty($type))
			{
				$query->where('track_type = ' . (int) $type);
			}

			// Filter by begin date
			$begin = $this->getState('filter.begin');

			if (!empty($begin))
			{
				$query->where('track_date >= ' . $db->quote($begin));
			}

			// Filter by end date
			$end = $this->getState('filter.end');

			if (!empty($end))
			{
				$query->where('track_date <= ' . $db->quote($end));
			}

			$where = '1';

			// Filter by client
			$clientId = $this->getState('filter.client_id');

			if (!empty($clientId))
			{
				$where .= ' AND cid = ' . (int) $clientId;
			}

			// Filter by category
			if (!empty($categoryId))
			{
				$where .= ' AND catid = ' . (int) $categoryId;
			}

			$query->where('banner_id IN (SELECT id FROM ' . $db->quoteName('#__banners') . ' WHERE ' . $where . ')');

			$db->setQuery($query);
			$this->setError((string) $query);

			try
			{
				$db->execute();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			JError::raiseWarning(403, JText::_('JERROR_CORE_DELETE_NOT_PERMITTED'));
		}

		return true;
	}

	/**
	 * Get file name
	 *
	 * @return  string  The file name
	 *
	 * @since   1.6
	 */
	public function getBaseName()
	{
		if (!isset($this->basename))
		{
			$basename   = str_replace('__SITE__', JFactory::getApplication()->get('sitename'), $this->getState('basename'));
			$categoryId = $this->getState('filter.category_id');

			if (is_numeric($categoryId))
			{
				if ($categoryId > 0)
				{
					$basename = str_replace('__CATID__', $categoryId, $basename);
				}
				else
				{
					$basename = str_replace('__CATID__', '', $basename);
				}

				$categoryName = $this->getCategoryName();
				$basename = str_replace('__CATNAME__', $categoryName, $basename);
			}
			else
			{
				$basename = str_replace(array('__CATID__', '__CATNAME__'), '', $basename);
			}

			$clientId = $this->getState('filter.client_id');

			if (is_numeric($clientId))
			{
				if ($clientId > 0)
				{
					$basename = str_replace('__CLIENTID__', $clientId, $basename);
				}
				else
				{
					$basename = str_replace('__CLIENTID__', '', $basename);
				}

				$clientName = $this->getClientName();
				$basename = str_replace('__CLIENTNAME__', $clientName, $basename);
			}
			else
			{
				$basename = str_replace(array('__CLIENTID__', '__CLIENTNAME__'), '', $basename);
			}

			$type = $this->getState('filter.type');

			if ($type > 0)
			{
				$basename = str_replace('__TYPE__', $type, $basename);
				$typeName = JText::_('COM_BANNERS_TYPE' . $type);
				$basename = str_replace('__TYPENAME__', $typeName, $basename);
			}
			else
			{
				$basename = str_replace(array('__TYPE__', '__TYPENAME__'), '', $basename);
			}

			$begin = $this->getState('filter.begin');

			if (!empty($begin))
			{
				$basename = str_replace('__BEGIN__', $begin, $basename);
			}
			else
			{
				$basename = str_replace('__BEGIN__', '', $basename);
			}

			$end = $this->getState('filter.end');

			if (!empty($end))
			{
				$basename = str_replace('__END__', $end, $basename);
			}
			else
			{
				$basename = str_replace('__END__', '', $basename);
			}

			$this->basename = $basename;
		}

		return $this->basename;
	}

	/**
	 * Get the category name.
	 *
	 * @return  string  The category name
	 *
	 * @since   1.6
	 */
	protected function getCategoryName()
	{
		$categoryId = $this->getState('filter.category_id');

		if ($categoryId)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('title')
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('id') . '=' . $db->quote($categoryId));
			$db->setQuery($query);

			try
			{
				$name = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			return $name;
		}

		return JText::_('COM_BANNERS_NOCATEGORYNAME');
	}

	/**
	 * Get the category name
	 *
	 * @return  string  The category name.
	 *
	 * @since   1.6
	 */
	protected function getClientName()
	{
		$clientId = $this->getState('filter.client_id');

		if ($clientId)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select('name')
				->from($db->quoteName('#__banner_clients'))
				->where($db->quoteName('id') . '=' . $db->quote($clientId));
			$db->setQuery($query);

			try
			{
				$name = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			return $name;
		}

		return JText::_('COM_BANNERS_NOCLIENTNAME');
	}

	/**
	 * Get the file type.
	 *
	 * @return  string  The file type
	 *
	 * @since   1.6
	 */
	public function getFileType()
	{
		return $this->getState('compressed') ? 'zip' : 'csv';
	}

	/**
	 * Get the mime type.
	 *
	 * @return  string  The mime type.
	 *
	 * @since   1.6
	 */
	public function getMimeType()
	{
		return $this->getState('compressed') ? 'application/zip' : 'text/csv';
	}

	/**
	 * Get the content
	 *
	 * @return  string  The content.
	 *
	 * @since   1.6
	 */
	public function getContent()
	{
		if (!isset($this->content))
		{
			$this->content = '"' . str_replace('"', '""', JText::_('COM_BANNERS_HEADING_NAME')) . '","'
				. str_replace('"', '""', JText::_('COM_BANNERS_HEADING_CLIENT')) . '","'
				. str_replace('"', '""', JText::_('JCATEGORY')) . '","'
				. str_replace('"', '""', JText::_('COM_BANNERS_HEADING_TYPE')) . '","'
				. str_replace('"', '""', JText::_('COM_BANNERS_HEADING_COUNT')) . '","'
				. str_replace('"', '""', JText::_('JDATE')) . '"' . "\n";

			foreach ($this->getItems() as $item)
			{
				$this->content .= '"' . str_replace('"', '""', $item->name) . '","'
					. str_replace('"', '""', $item->client_name) . '","'
					. str_replace('"', '""', $item->category_title) . '","'
					. str_replace('"', '""', ($item->track_type == 1 ? JText::_('COM_BANNERS_IMPRESSION') : JText::_('COM_BANNERS_CLICK'))) . '","'
					. str_replace('"', '""', $item->count) . '","'
					. str_replace('"', '""', $item->track_date) . '"' . "\n";
			}

			if ($this->getState('compressed'))
			{
				$app = JFactory::getApplication('administrator');

				$files = array(
					'track' => array(
						'name' => $this->getBasename() . '.csv',
						'data' => $this->content,
						'time' => time()
					)
				);
				$ziproot = $app->get('tmp_path') . '/' . uniqid('banners_tracks_') . '.zip';

				// Run the packager
				jimport('joomla.filesystem.folder');
				jimport('joomla.filesystem.file');
				$delete = JFolder::files($app->get('tmp_path') . '/', uniqid('banners_tracks_'), false, true);

				if (!empty($delete))
				{
					if (!JFile::delete($delete))
					{
						// JFile::delete throws an error
						$this->setError(JText::_('COM_BANNERS_ERR_ZIP_DELETE_FAILURE'));

						return false;
					}
				}

				if (!$packager = JArchive::getAdapter('zip'))
				{
					$this->setError(JText::_('COM_BANNERS_ERR_ZIP_ADAPTER_FAILURE'));

					return false;
				}
				elseif (!$packager->create($ziproot, $files))
				{
					$this->setError(JText::_('COM_BANNERS_ERR_ZIP_CREATE_FAILURE'));

					return false;
				}

				$this->content = file_get_contents($ziproot);
			}
		}

		return $this->content;
	}
}
