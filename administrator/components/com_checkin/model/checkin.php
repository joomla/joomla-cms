<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Checkin Model
 *
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 * @since       3.2
 */
class CheckinModelCheckin extends JModelCmslist
{
	/*
	 * Total number of items that are checked out
	 *
	 * @var  integer
	 */
	protected $total;

	/*
	 * Array of tables to be checked in
	 *
	 * @var  array
	 */
	protected $tables;

	/**
	 * Method to auto-populate the model state.
	 *
	 * @Note. Calling getState in this method will result in recursion.
	 * @param   string  $ordering   An optional ordering field.
	 * @param   string  $direction  An optional direction (asc|desc).
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->state->set('filter.search', $search);

		// List state information.
		parent::populateState('table', 'asc');
	}

	/**
	 * Checks in requested tables
	 *
	 * @param   array    $ids  An array of table names. Optional.
	 *
	 * @return  integer  Checked in item count
	 *
	 * @since   3.2
	 */
	public function checkin($ids = array())
	{
		$app = JFactory::getApplication();
		$db = $this->_db;
		$nullDate = $db->getNullDate();

		if (!is_array($ids))
		{
			return;
		}

		// This integer will hold the checked item count
		$results = 0;

		foreach ($ids as $tn)
		{
			// Make sure we get the right tables based on prefix
			if (stripos($tn, $app->getCfg('dbprefix')) !== 0)
			{
				continue;
			}

			$fields = $db->getTableColumns($tn);

			if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
			{
				continue;
			}

			$query = $db->getQuery(true)
				->update($db->quoteName($tn))
				->set('checked_out = 0')
				->set('checked_out_time = ' . $db->quote($nullDate))
				->where('checked_out > 0');

			$db->setQuery($query);

			if ($db->execute())
			{
				$results = $results + $db->getAffectedRows();
			}
		}
		return $results;
	}

	/**
	 * Get total of tables
	 *
	 * @return  int    Total to check-in tables
	 *
	 * @since   3.2
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
	 * Get items
	 *
	 * @return  array  Checked in table names as keys and checked in item count as values
	 *
	 * @since   3.2
	 */
	public function getItems()
	{
		if (!isset($this->items))
		{
			$app = JFactory::getApplication();
			$db = $this->_db;
			$tables = $db->getTableList();

			// this array will hold table name as key and checked in item count as value
			$results = array();

			foreach ($tables as $i => $tn)
			{
				// make sure we get the right tables based on prefix
				if (stripos($tn, $app->getCfg('dbprefix')) !== 0)
				{
					unset($tables[$i]);
					continue;
				}

				if ($this->state->get('filter.search') && stripos($tn, $this->state->get('filter.search')) === false)
				{
					unset($tables[$i]);
					continue;
				}

				$fields = $db->getTableColumns($tn);

				if (!(isset($fields['checked_out']) && isset($fields['checked_out_time'])))
				{
					unset($tables[$i]);
					continue;
				}
			}
			foreach ($tables as $tn)
			{
				$query = $db->getQuery(true)
					->select('COUNT(*)')
					->from($db->quoteName($tn))
					->where('checked_out > 0');

				$db->setQuery($query);
				if ($db->execute())
				{
					$results[$tn] = $db->loadResult();
				}
				else
				{
					continue;
				}
			}

			$this->total = count($results);

			if ($this->getState('list.ordering') == 'table')
			{
				if ($this->getState('list.direction') == 'asc')
				{
					ksort($results);
				}
				else
				{
					krsort($results);
				}
			}
			else
			{
				if ($this->getState('list.direction') == 'asc')
				{
					asort($results);
				}
				else
				{
					arsort($results);
				}
			}

			$results = array_slice($results, $this->state->get('list.start'), $this->state->get('list.limit') ? $this->state->get('list.limit') : null);
			$this->items = $results;
		}

		return $this->items;
	}
}
