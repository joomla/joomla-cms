<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Checkin Model
 *
 * @since  1.6
 */
class CheckinModelCheckin extends JModelList
{
	protected $total;

	protected $tables;

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
	 * @since   1.6
	 */
	protected function populateState($ordering = null, $direction = null)
	{
		$search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
		$this->setState('filter.search', $search);

		// List state information.
		parent::populateState('table', 'asc');
	}

	/**
	 * Checks in requested tables
	 *
	 * @param   array  $ids  An array of table names. Optional.
	 *
	 * @return  integer  Checked in item count
	 *
	 * @since   1.6
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

		// This int will hold the checked item count.
		$results = 0;

		foreach ($ids as $tn)
		{
			// Make sure we get the right tables based on prefix.
			if (stripos($tn, $app->get('dbprefix')) !== 0)
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
	 * @since   1.6
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
	 * @return  array  Checked in table names as keys and checked in item count as values.
	 *
	 * @since   1.6
	 */
	public function getItems()
	{
		if (!isset($this->items))
		{
			$app    = JFactory::getApplication();
			$db     = $this->_db;
			$tables = $db->getTableList();

			// This array will hold table name as key and checked in item count as value.
			$results = array();

			foreach ($tables as $i => $tn)
			{
				// Make sure we get the right tables based on prefix.
				if (stripos($tn, $app->get('dbprefix')) !== 0)
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

			$results = array_slice($results, $this->getState('list.start'), $this->getState('list.limit') ? $this->getState('list.limit') : null);
			$this->items = $results;
		}

		return $this->items;
	}
}
