<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
namespace Joomla\Component\Banners\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\Archive\Archive;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Methods supporting a list of tracks.
 *
 * @since  1.6
 */
class TracksModel extends ListModel
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
		// Load the parameters.
		$this->setState('params', ComponentHelper::getParams('com_banners'));

		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \Joomla\Database\DatabaseQuery
	 *
	 * @since   1.6
	 */
	protected function getListQuery()
	{
		// Create a new query object.
		$db = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query->select(
			[
				$db->quoteName('a.track_date'),
				$db->quoteName('a.track_type'),
				$db->quoteName('a.count'),
				$db->quoteName('b.name', 'banner_name'),
				$db->quoteName('cl.name', 'client_name'),
				$db->quoteName('c.title', 'category_title'),
			]
		);

		// From tracks table.
		$query->from($db->quoteName('#__banner_tracks', 'a'));

		// Join with the banners.
		$query->join('LEFT', $db->quoteName('#__banners', 'b'), $db->quoteName('b.id') . ' = ' . $db->quoteName('a.banner_id'));

		// Join with the client.
		$query->join('LEFT', $db->quoteName('#__banner_clients', 'cl'), $db->quoteName('cl.id') . ' = ' . $db->quoteName('b.cid'));

		// Join with the category.
		$query->join('LEFT', $db->quoteName('#__categories', 'c'), $db->quoteName('c.id') . ' = ' . $db->quoteName('b.catid'));

		// Filter by type.

		if ($type = (int) $this->getState('filter.type'))
		{
			$query->where($db->quoteName('a.track_type') . ' = :type')
				->bind(':type', $type, ParameterType::INTEGER);
		}

		// Filter by client.
		$clientId = $this->getState('filter.client_id');

		if (is_numeric($clientId))
		{
			$clientId = (int) $clientId;
			$query->where($db->quoteName('b.cid') . ' = :clientId')
				->bind(':clientId', $clientId, ParameterType::INTEGER);
		}

		// Filter by category.
		$categoryId = $this->getState('filter.category_id');

		if (is_numeric($categoryId))
		{
			$categoryId = (int) $categoryId;
			$query->where($db->quoteName('b.catid') . ' = :categoryId')
				->bind(':categoryId', $categoryId, ParameterType::INTEGER);
		}

		// Filter by begin date.
		if ($begin = $this->getState('filter.begin'))
		{
			$query->where($db->quoteName('a.track_date') . ' >= :begin')
				->bind(':begin', $begin);
		}

		// Filter by end date.
		if ($end = $this->getState('filter.end'))
		{
			$query->where($db->quoteName('a.track_date') . ' <= :end')
				->bind(':end', $end);
		}

		// Filter on the level.
		if ($level = (int) $this->getState('filter.level'))
		{
			$query->where($db->quoteName('c.level') . ' <= :level')
				->bind(':level', $level, ParameterType::INTEGER);
		}

		// Filter by search in banner name or client name.
		if ($search = $this->getState('filter.search'))
		{
			$search = '%' . StringHelper::strtolower($search) . '%';
			$query->where('(LOWER(' . $db->quoteName('b.name') . ') LIKE :search1 OR LOWER(' . $db->quoteName('cl.name') . ') LIKE :search2)')
				->bind([':search1', ':search2'], $search);
		}

		// Add the list ordering clause.
		$query->order(
			$db->quoteName($db->escape($this->getState('list.ordering', 'b.name'))) . ' ' . $db->escape($this->getState('list.direction', 'ASC'))
		);

		return $query;
	}

	/**
	 * Method to delete rows.
	 *
	 * @return  boolean  Returns true on success, false on failure.
	 */
	public function delete()
	{
		$user       = Factory::getUser();
		$categoryId = (int) $this->getState('category_id');

		// Access checks.
		if ($categoryId)
		{
			$allow = $user->authorise('core.delete', 'com_banners.category.' . $categoryId);
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
			if ($type = (int) $this->getState('filter.type'))
			{
				$query->where($db->quoteName('track_type') . ' = :type')
					->bind(':type', $type, ParameterType::INTEGER);
			}

			// Filter by begin date
			if ($begin = $this->getState('filter.begin'))
			{
				$query->where($db->quoteName('track_date') . ' >= :begin')
					->bind(':begin', $begin);
			}

			// Filter by end date
			if ($end = $this->getState('filter.end'))
			{
				$query->where($db->quoteName('track_date') . ' <= :end')
					->bind(':end', $end);
			}

			$subQuery = $db->getQuery(true);
			$subQuery->select($db->quoteName('id'))
				->from($db->quoteName('#__banners'));

			// Filter by client
			if ($clientId = (int) $this->getState('filter.client_id'))
			{
				$subQuery->where($db->quoteName('cid') . ' = :clientId');
				$query->bind(':clientId', $clientId, ParameterType::INTEGER);
			}

			// Filter by category
			if ($categoryId)
			{
				$subQuery->where($db->quoteName('catid') . ' = :categoryId');
				$query->bind(':categoryId', $categoryId, ParameterType::INTEGER);
			}

			$query->where($db->quoteName('banner_id') . ' IN (' . $subQuery . ')');

			$db->setQuery($query);
			$this->setError((string) $query);

			try
			{
				$db->execute();
			}
			catch (\RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}
		}
		else
		{
			Factory::getApplication()->enqueueMessage(Text::_('JERROR_CORE_DELETE_NOT_PERMITTED'), 'error');
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
			$basename   = str_replace('__SITE__', Factory::getApplication()->get('sitename'), $this->getState('basename'));
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
				$typeName = Text::_('COM_BANNERS_TYPE' . $type);
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
		$categoryId = (int) $this->getState('filter.category_id');

		if ($categoryId)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__categories'))
				->where($db->quoteName('id') . ' = :categoryId')
				->bind(':categoryId', $categoryId, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$name = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			return $name;
		}

		return Text::_('COM_BANNERS_NOCATEGORYNAME');
	}

	/**
	 * Get the client name
	 *
	 * @return  string  The client name.
	 *
	 * @since   1.6
	 */
	protected function getClientName()
	{
		$clientId = (int) $this->getState('filter.client_id');

		if ($clientId)
		{
			$db    = $this->getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('name'))
				->from($db->quoteName('#__banner_clients'))
				->where($db->quoteName('id') . ' = :clientId')
				->bind(':clientId', $clientId, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$name = $db->loadResult();
			}
			catch (\RuntimeException $e)
			{
				$this->setError($e->getMessage());

				return false;
			}

			return $name;
		}

		return Text::_('COM_BANNERS_NOCLIENTNAME');
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
			$this->content = '"' . str_replace('"', '""', Text::_('COM_BANNERS_HEADING_NAME')) . '","'
				. str_replace('"', '""', Text::_('COM_BANNERS_HEADING_CLIENT')) . '","'
				. str_replace('"', '""', Text::_('JCATEGORY')) . '","'
				. str_replace('"', '""', Text::_('COM_BANNERS_HEADING_TYPE')) . '","'
				. str_replace('"', '""', Text::_('COM_BANNERS_HEADING_COUNT')) . '","'
				. str_replace('"', '""', Text::_('JDATE')) . '"' . "\n";

			foreach ($this->getItems() as $item)
			{
				$this->content .= '"' . str_replace('"', '""', $item->banner_name) . '","'
					. str_replace('"', '""', $item->client_name) . '","'
					. str_replace('"', '""', $item->category_title) . '","'
					. str_replace('"', '""', ($item->track_type == 1 ? Text::_('COM_BANNERS_IMPRESSION') : Text::_('COM_BANNERS_CLICK'))) . '","'
					. str_replace('"', '""', $item->count) . '","'
					. str_replace('"', '""', $item->track_date) . '"' . "\n";
			}

			if ($this->getState('compressed'))
			{
				$app = Factory::getApplication();

				$files = array(
					'track' => array(
						'name' => $this->getBaseName() . '.csv',
						'data' => $this->content,
						'time' => time()
					)
				);
				$ziproot = $app->get('tmp_path') . '/' . uniqid('banners_tracks_') . '.zip';

				// Run the packager
				$delete = Folder::files($app->get('tmp_path') . '/', uniqid('banners_tracks_'), false, true);

				if (!empty($delete))
				{
					if (!File::delete($delete))
					{
						// File::delete throws an error
						$this->setError(Text::_('COM_BANNERS_ERR_ZIP_DELETE_FAILURE'));

						return false;
					}
				}

				$archive = new Archive;

				if (!$packager = $archive->getAdapter('zip'))
				{
					$this->setError(Text::_('COM_BANNERS_ERR_ZIP_ADAPTER_FAILURE'));

					return false;
				}
				elseif (!$packager->create($ziproot, $files))
				{
					$this->setError(Text::_('COM_BANNERS_ERR_ZIP_CREATE_FAILURE'));

					return false;
				}

				$this->content = file_get_contents($ziproot);

				// Remove tmp zip file, it's no longer needed.
				File::delete($ziproot);
			}
		}

		return $this->content;
	}
}
