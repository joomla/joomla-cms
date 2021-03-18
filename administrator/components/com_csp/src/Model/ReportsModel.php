<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_csp
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Csp\Administrator\Model;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

/**
 * CSP Component Reports Model
 *
 * @since  4.0.0
 */
class ReportsModel extends ListModel
{
	/**
	 * Constructor
	 *
	 * @param   array                $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @since   4.0.0
	 * @throws  \Exception
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'id', 'a.id',
				'document_uri', 'a.document_uri',
				'blocked_uri', 'a.blocked_uri',
				'directive', 'a.directive',
				'client', 'a.client',
				'published', 'a.published',
				'created', 'a.created',
			);
		}

		parent::__construct($config, $factory);
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
	 * @since   4.0.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'asc')
	{
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
	 * @since   4.0.0
	 */
	protected function getStoreId($id = '')
	{
		// Compile the store id.
		$id .= ':' . $this->getState('filter.search');
		$id .= ':' . $this->getState('filter.published');

		return parent::getStoreId($id);
	}

	/**
	 * Method to create a query for a list of items.
	 *
	 * @return  string
	 *
	 * @since   4.0.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true);

		// Select the required fields from the table.
		$query
			->select('*')
			->from($db->quoteName('#__csp', 'a'));

		// Filter by client
		$client = (string) $this->getState('filter.client');

		if (!empty($client))
		{
			$query->where($db->quoteName('a.client') . ' = :client')
				->bind(':client', $client);
		}

		// Filter by published state
		$published = (string) $this->getState('filter.published');

		if (is_numeric($published))
		{
			$published = (int) $published;
			$query->where($db->quoteName('a.published') . ' = :published')
				->bind(':published', $published, ParameterType::INTEGER);
		}
		elseif ($published === '')
		{
			$query->whereIn($db->quoteName('a.published'), [0, 1]);
		}

		// Filter by directive
		$directive = (string) $this->getState('filter.directive');

		if (!empty($directive))
		{
			$query->where($db->quoteName('a.directive') . ' = :directive')
				->bind(':directive', $directive);
		}

		// Filter by search in title
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
				$search = '%' . trim($search) . '%';
				$query->where(
					'(' . $db->quoteName('a.document_uri') . ' LIKE :documenturi'
					. ' OR ' . $db->quoteName('a.blocked_uri') . ' LIKE :blockeduri'
					. ' OR ' . $db->quoteName('a.directive') . ' LIKE :directive)'
				)
					->bind(':documenturi', $search)
					->bind(':blockeduri', $search)
					->bind(':directive', $search);
			}
		}

		// Add the list ordering clause
		$listOrdering = $this->getState('list.ordering', 'a.id');
		$listDirn     = $db->escape($this->getState('list.direction', 'ASC'));

		$query->order($db->escape($listOrdering) . ' ' . $listDirn);

		return $query;
	}
}
