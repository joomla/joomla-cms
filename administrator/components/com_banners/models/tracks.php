<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Methods supporting a list of tracks.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 * @since       1.6
 */
class BannersModelTracks extends JModelList
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
				'name', 'b.name',
				'cl.name', 'client_name',
				'cat.title', 'category_title',
				'track_type', 'a.track_type',
				'count', 'a.count',
				'track_date', 'a.track_date',
			);
		}

		parent::__construct($config);
	}

	/**
	 * @since   1.6
	 */
	protected $basename;

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
		// Load the filter state.
		$type = $this->getUserStateFromRequest($this->context . '.filter.type', 'filter_type');
		$this->setState('filter.type', $type);

		$begin = $this->getUserStateFromRequest($this->context . '.filter.begin', 'filter_begin', '', 'string');
		$this->setState('filter.begin', $begin);

		$end = $this->getUserStateFromRequest($this->context . '.filter.end', 'filter_end', '', 'string');
		$this->setState('filter.end', $end);

		$categoryId = $this->getUserStateFromRequest($this->context . '.filter.category_id', 'filter_category_id', '');
		$this->setState('filter.category_id', $categoryId);

		$clientId = $this->getUserStateFromRequest($this->context . '.filter.client_id', 'filter_client_id', '');
		$this->setState('filter.client_id', $clientId);

		// Load the parameters.
		$params = JComponentHelper::getParams('com_banners');
		$this->setState('params', $params);

		// List state information.
		parent::populateState('b.name', 'asc');
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
		require_once JPATH_COMPONENT . '/helpers/banners.php';

		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			'a.track_date as track_date,'
			. 'a.track_type as track_type,'
			. $db->quoteName('a.count') . ' as ' . $db->quoteName('count')
		);
		$query->from($db->quoteName('#__banner_tracks') . ' AS a');

		// Join with the banners
		$query->join('LEFT', $db->quoteName('#__banners') . ' as b ON b.id=a.banner_id')
			->select('b.name as name');

		// Join with the client
		$query->join('LEFT', $db->quoteName('#__banner_clients') . ' as cl ON cl.id=b.cid')
			->select('cl.name as client_name');

		// Join with the category
		$query->join('LEFT', $db->quoteName('#__categories') . ' as cat ON cat.id=b.catid')
			->select('cat.title as category_title');

		// Filter by type
		$type = $this->getState('filter.type');

		if (!empty($type))
		{
			$query->where('a.track_type = ' . (int) $type);
		}

		// Filter by client
		$clientId = $this->getState('filter.client_id');

		if (is_numeric($clientId))
		{
			$query->where('b.cid = ' . (int) $clientId);
		}

		// Filter by category
		$catedoryId = $this->getState('filter.category_id');

		if (is_numeric($catedoryId))
		{
			$query->where('b.catid = ' . (int) $catedoryId);
		}

		// Filter by begin date

		$begin = $this->getState('filter.begin');

		if (!empty($begin))
		{
			$query->where('a.track_date >= ' . $db->quote($begin));
		}

		// Filter by end date
		$end = $this->getState('filter.end');

		if (!empty($end))
		{
			$query->where('a.track_date <= ' . $db->quote($end));
		}

		// Add the list ordering clause.
		$orderCol = $this->getState('list.ordering', 'name');
		$query->order($db->escape($orderCol) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Method to delete rows.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 */
	public function delete()
	{
		$user = JFactory::getUser();
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
			$db = $this->getDbo();
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
	 * @return  string    The file name
	 *
	 * @since   1.6
	 */
	public function getBaseName()
	{
		if (!isset($this->basename))
		{
			$app = JFactory::getApplication();
			$basename = $this->getState('basename');
			$basename = str_replace('__SITE__', $app->get('sitename'), $basename);
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
				$basename = str_replace('__CATID__', '', $basename);
				$basename = str_replace('__CATNAME__', '', $basename);
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
				$basename = str_replace('__CLIENTID__', '', $basename);
				$basename = str_replace('__CLIENTNAME__', '', $basename);
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
				$basename = str_replace('__TYPE__', '', $basename);
				$basename = str_replace('__TYPENAME__', '', $basename);
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
	 * @return  string    The category name
	 *
	 * @since   1.6
	 */
	protected function getCategoryName()
	{
		$categoryId = $this->getState('filter.category_id');

		if ($categoryId)
		{
			$db = $this->getDbo();
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
		}
		else
		{
			$name = JText::_('COM_BANNERS_NOCATEGORYNAME');
		}

		return $name;
	}

	/**
	 * Get the category name
	 *
	 * @return  string    The category name.
	 *
	 * @since   1.6
	 */
	protected function getClientName()
	{
		$clientId = $this->getState('filter.client_id');

		if ($clientId)
		{
			$db = $this->getDbo();
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
		}
		else
		{
			$name = JText::_('COM_BANNERS_NOCLIENTNAME');
		}

		return $name;
	}

	/**
	 * Get the file type.
	 *
	 * @return  string    The file type
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
	 * @return  string    The mime type.
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
	 * @return  string    The content.
	 *
	 * @since   1.6
	 */
	public function getContent()
	{
		if (!isset($this->content))
		{
			$this->content = '';
			$this->content .=
				'"' . str_replace('"', '""', JText::_('COM_BANNERS_HEADING_NAME')) . '","' .
				str_replace('"', '""', JText::_('COM_BANNERS_HEADING_CLIENT')) . '","' .
				str_replace('"', '""', JText::_('JCATEGORY')) . '","' .
				str_replace('"', '""', JText::_('COM_BANNERS_HEADING_TYPE')) . '","' .
				str_replace('"', '""', JText::_('COM_BANNERS_HEADING_COUNT')) . '","' .
				str_replace('"', '""', JText::_('JDATE')) . '"' . "\n";

			foreach ($this->getItems() as $item)
			{
				$this->content .=
					'"' . str_replace('"', '""', $item->name) . '","' .
					str_replace('"', '""', $item->client_name) . '","' .
					str_replace('"', '""', $item->category_title) . '","' .
					str_replace('"', '""', ($item->track_type == 1 ? JText::_('COM_BANNERS_IMPRESSION') : JText::_('COM_BANNERS_CLICK'))) . '","' .
					str_replace('"', '""', $item->count) . '","' .
					str_replace('"', '""', $item->track_date) . '"' . "\n";
			}

			if ($this->getState('compressed'))
			{
				$app = JFactory::getApplication('administrator');

				$files = array();
				$files['track'] = array();
				$files['track']['name'] = $this->getBasename() . '.csv';
				$files['track']['data'] = $this->content;
				$files['track']['time'] = time();
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
