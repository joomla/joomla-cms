<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Redirect\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * Methods supporting a list of redirect links.
 *
 * @since  1.6
 */
class LinksModel extends ListModel
{
	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   1.6
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'state', 'a.state',
				'old_url', 'a.old_url',
				'new_url', 'a.new_url',
				'referer', 'a.referer',
				'hits', 'a.hits',
				'created_date', 'a.created_date',
				'published', 'a.published',
				'header', 'a.header', 'http_status',
			);
		}

		parent::__construct($config, $factory);
	}
	/**
	 * Removes all of the unpublished redirects from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   3.5
	 */
	public function purge()
	{
		$db = $this->getDbo();

		$query = $db->getQuery(true);

		$query->delete('#__redirect_links')->where($db->quoteName('published') . '= 0');

		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (\Exception $e)
		{
			return false;
		}

		return true;
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
	protected function populateState($ordering = 'a.old_url', $direction = 'asc')
	{
		// Load the parameters.
		$params = ComponentHelper::getParams('com_redirect');
		$this->setState('params', $params);

		// List state information.
		parent::populateState($ordering, $direction);
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
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.state');
		$id .= ':' . $this->getState('filter.http_status');

		return parent::getStoreId($id);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  \JDatabaseQuery
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
			$this->getState(
				'list.select',
				'a.*'
			)
		);
		$query->from($db->quoteName('#__redirect_links', 'a'));

		// Filter by published state
		$state = (string) $this->getState('filter.state');

		if (is_numeric($state))
		{
			$state = (int) $state;
			$query->where($db->quoteName('a.published') . ' = :state')
				->bind(':state', $state, ParameterType::INTEGER);
		}
		elseif ($state === '')
		{
			$query->whereIn($db->quoteName('a.published'), [0,1]);
		}

		// Filter the items over the HTTP status code header.
		if ($httpStatusCode = $this->getState('filter.http_status'))
		{
			$httpStatusCode = (int) $httpStatusCode;
			$query->where($db->quoteName('a.header') . ' = :header')
				->bind(':header', $httpStatusCode, ParameterType::INTEGER);
		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$ids = (int) substr($search, 3);
				$query->where($db->quoteName('a.id') . ' = :id');
				$query->bind(':id', $ids, ParameterType::INTEGER);
			}
			else
			{
				$search = '%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%');
				$query->where(
					'(' . $db->quoteName('old_url') . ' LIKE :oldurl'
					. ' OR ' . $db->quoteName('new_url') . ' LIKE :newurl'
					. ' OR ' . $db->quoteName('comment') . ' LIKE :comment'
					. ' OR ' . $db->quoteName('referer') . ' LIKE :referer)'
				)
					->bind(':oldurl', $search)
					->bind(':newurl', $search)
					->bind(':comment', $search)
					->bind(':referer', $search);
			}
		}

		// Add the list ordering clause.
		$query->order($db->escape($this->getState('list.ordering', 'a.old_url')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

		return $query;
	}

	/**
	 * Add the entered URLs into the database
	 *
	 * @param   array  $batchUrls  Array of URLs to enter into the database
	 *
	 * @return boolean
	 */
	public function batchProcess($batchUrls)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		$params  = ComponentHelper::getParams('com_redirect');
		$state   = (int) $params->get('defaultImportState', 0);
		$created = Factory::getDate()->toSql();

		$columns = [
			'old_url',
			'new_url',
			'referer',
			'comment',
			'hits',
			'published',
			'created_date',
			'modified_date',
		];

		$values = [
			':oldurl',
			':newurl',
			$db->quote(''),
			$db->quote(''),
			0,
			':state',
			':created',
			':modified',
		];

		$query
			->insert($db->quoteName('#__redirect_links'), false)
			->columns($db->quoteName($columns))
			->values(implode(', ', $values))
			->bind(':oldurl', $old_url)
			->bind(':newurl', $new_url)
			->bind(':state', $state, ParameterType::INTEGER)
			->bind(':created', $created)
			->bind(':modified', $created);

		$db->setQuery($query);

		foreach ($batchUrls as $batch_url)
		{
			$old_url = $batch_url[0];

			// Destination URL can also be an external URL
			if (!empty($batch_url[1]))
			{
				$new_url = $batch_url[1];
			}
			else
			{
				$new_url = '';
			}

			$db->execute();
		}

		return true;
	}
}
