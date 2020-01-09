<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_trash
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Trash\Administrator\Model;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;

/**
 * Trash Model
 *
 * @since  __DEPLOY_VERSION__
 */
class TrashModel extends ListModel
{
	/**
	 * Count of the total items in trashed state
	 *
	 * @var  integer
	 */
	protected $total;

	/**
	 * Constructor.
	 *
	 * @param   array                $config   An optional associative array of configuration settings.
	 * @param   MVCFactoryInterface  $factory  The factory.
	 *
	 * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
	 * @since   __DEPLOY_VERSION__
	 */
	public function __construct($config = array(), MVCFactoryInterface $factory = null)
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'table',
				'count',
			);
		}

		parent::__construct($config, $factory);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note: Calling getState in this method will result in recursion.
	 *
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'table', $direction = 'asc')
	{
		// List state information.
		parent::populateState($ordering, $direction);
	}

	/**
	 * Deletes items in requested tables
	 *
	 * @param   array  $ids  An array of table names. Optional.
	 *
	 * @return  mixed  The database results or 0
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function trash($ids = array())
	{
		$db = $this->getDbo();

		if (!is_array($ids))
		{
			return 0;
		}

		// This int will hold the trashed item count.
		$results = 0;

		$app = Factory::getApplication();

		foreach ($ids as $tn)
		{
			// Make sure we get the right tables based on prefix.
			if (stripos($tn, $app->get('dbprefix')) !== 0)
			{
				continue;
			}

			// Set the column name for the where clause
			$fields = $db->getTableColumns($tn);

			if (isset($fields['state']))
			{
				$column = 'state';
			}
			elseif (isset($fields['published']))
			{
				$column = 'published';
			}

			$query = $db->getQuery(true)
				->delete($db->quoteName($tn))
				->where($column . ' = -2');

			$db->setQuery($query);

			if ($db->execute())
			{
				$results = $results + $db->getAffectedRows();
				$app->triggerEvent('onAfterTrash', array($tn));
			}
		}

		return $results;
	}

	/**
	 * Get total of tables
	 *
	 * @return  integer  Total to trash tables
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getTotal()
	{
		if (!isset($this->total))
		{
			$this->getItems();
		}

		return $this->total;
	}

	/**
	 * Get tables
	 *
	 * @return  array  Table names as keys and item count as values.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getItems()
	{
		if (!isset($this->items))
		{
			$db     = $this->getDbo();
			$tables = $db->getTableList();

			// This array will hold table name as key and item count as value.
			$results = array();

			foreach ($tables as $i => $tn)
			{
				// Make sure we get the right tables based on prefix.
				if (stripos($tn, Factory::getApplication()->get('dbprefix')) !== 0)
				{
					unset($tables[$i]);
					continue;
				}

				if ($this->getState('filter.search') && stripos($tn, $this->getState('filter.search')) === false)
				{
					unset($tables[$i]);
					continue;
				}

				$fields = $db->getTableColumns($tn);

				// Only work with the tables that have either a state or a published column
				if (!(isset($fields['state'])) && !(isset($fields['published'])))
				{
					unset($tables[$i]);
					continue;
				}
			}

			foreach ($tables as $tn)
			{
				// Set the column name for the where clause
				$fields = $db->getTableColumns($tn);

				if (isset($fields['state']))
				{
					$column = 'state';
				}
				elseif (isset($fields['published']))
				{
					$column = 'published';
				}

				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->quoteName($tn))
					->where($column . ' = -2');

				$db->setQuery($query);

				if ($db->execute())
				{
					$results[$tn] = $db->loadResult();

					// Show only tables with items to trash.
					if ((int) $results[$tn] === 0)
					{
						unset($results[$tn]);
					}
				}
				else
				{
					continue;
				}
			}

			$this->total = count($results);

			// Order items by table
			if ($this->getState('list.ordering') == 'table')
			{
				if (strtolower($this->getState('list.direction')) == 'asc')
				{
					ksort($results);
				}
				else
				{
					krsort($results);
				}
			}
			// Order items by number of items
			else
			{
				if (strtolower($this->getState('list.direction')) == 'asc')
				{
					asort($results);
				}
				else
				{
					arsort($results);
				}
			}

			// Pagination
			$limit = (int) $this->getState('list.limit');

			if ($limit !== 0)
			{
				$this->items = array_slice($results, $this->getState('list.start'), $limit);
			}
			else
			{
				$this->items = $results;
			}
		}

		return $this->items;
	}
}
