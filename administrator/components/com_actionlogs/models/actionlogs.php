<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * Methods supporting a list of article records.
 *
 * @since  3.9.0
 */
class ActionlogsModelActionlogs extends JModelList
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.9.0
	 */
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(
				'a.id', 'id',
				'a.extension', 'extension',
				'a.user_id', 'user',
				'a.message', 'message',
				'a.log_date', 'log_date',
				'a.ip_address', 'ip_address',
				'dateRange',
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   3.9.0
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = Factory::getApplication();

		$search = $app->getUserStateFromRequest($this->context . 'filter.search', 'filter_search', '', 'string');
		$this->setState('filter.search', $search);

		$user = $app->getUserStateFromRequest($this->context . 'filter.user', 'filter_user', '', 'string');
		$this->setState('filter.user', $user);

		$extension = $app->getUserStateFromRequest($this->context . 'filter.extension', 'filter_extension', '', 'string');
		$this->setState('filter.extension', $extension);

		$ip_address = $app->getUserStateFromRequest($this->context . 'filter.ip_address', 'filter_ip_address', '', 'string');
		$this->setState('filter.ip_address', $ip_address);

		$dateRange = $app->getUserStateFromRequest($this->context . 'filter.dateRange', 'filter_dateRange', '', 'string');
		$this->setState('filter.dateRange', $dateRange);

		parent::populateState($ordering, $direction);
	}

	/**
	 * Build an SQL query to load the list data.
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.9.0
	 */
	protected function getListQuery()
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.*, u.name')
			->from('#__action_logs AS a')
			->leftJoin('#__users AS u ON a.user_id = u.id');

		// Get ordering
		$fullorderCol = $this->state->get('list.fullordering', 'a.id DESC');

		// Apply ordering
		if (!empty($fullorderCol))
		{
			$query->order($db->escape($fullorderCol));
		}

		// Get filter by user
		$user = $this->getState('filter.user');

		// Apply filter by user
		if (!empty($user))
		{
			$query->where($db->quoteName('a.user_id') . ' = ' . (int) $user);
		}

		// Get filter by extension
		$extension = $this->getState('filter.extension');

		// Apply filter by extension
		if (!empty($extension))
		{
			$query->where($db->quoteName('a.extension') . ' LIKE ' . $db->quote($extension . '%'));
		}

		// Get filter by date range
		$dateRange = $this->getState('filter.dateRange');

		// Apply filter by date range
		if (!empty($dateRange))
		{
			$date = $this->buildDateRange($dateRange);

			// If the chosen range is not more than a year ago
			if ($date['dNow'] != false)
			{
				$query->where(
					$db->qn('a.log_date') . ' >= ' . $db->quote($date['dStart']->format('Y-m-d H:i:s')) .
					' AND ' . $db->qn('a.log_date') . ' <= ' . $db->quote($date['dNow']->format('Y-m-d H:i:s'))
				);
			}
		}

		// Filter the items over the search string if set.
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			if (stripos($search, 'id:') === 0)
			{
				$query->where($db->quoteName('a.id') . ' = ' . (int) substr($search, 3));
			}
			elseif (stripos($search, 'item_id:') === 0)
			{
				$query->where($db->quoteName('a.item_id') . ' = ' . (int) substr($search, 8));
			}
			else
			{
				$search = $db->quote('%' . $db->escape($search, true) . '%');
				$query->where('(' . $db->quoteName('u.username') . ' LIKE ' . $search . ')');
			}
		}

		return $query;
	}

	/**
	 * Construct the date range to filter on.
	 *
	 * @param   string  $range  The textual range to construct the filter for.
	 *
	 * @return  array  The date range to filter on.
	 *
	 * @since   3.9.0
	 */
	private function buildDateRange($range)
	{
		// Get UTC for now.
		$dNow   = new Date;
		$dStart = clone $dNow;

		switch ($range)
		{
			case 'past_week':
				$dStart->modify('-7 day');
				break;

			case 'past_1month':
				$dStart->modify('-1 month');
				break;

			case 'past_3month':
				$dStart->modify('-3 month');
				break;

			case 'past_6month':
				$dStart->modify('-6 month');
				break;

			case 'past_year':
				$dStart->modify('-1 year');
				break;

			case 'today':
				// Ranges that need to align with local 'days' need special treatment.
				$offset = Factory::getApplication()->get('offset');

				// Reset the start time to be the beginning of today, local time.
				$dStart = new Date('now', $offset);
				$dStart->setTime(0, 0, 0);

				// Now change the timezone back to UTC.
				$tz = new DateTimeZone('GMT');
				$dStart->setTimezone($tz);
				break;
		}

		return array('dNow' => $dNow, 'dStart' => $dStart);
	}

	/**
	 * Get all log entries for an item
	 *
	 * @param   string   $extension  The extension the item belongs to
	 * @param   integer  $itemId     The item ID
	 *
	 * @return  array
	 *
	 * @since   3.9.0
	 */
	public function getLogsForItem($extension, $itemId)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.*, u.name')
			->from('#__action_logs AS a')
			->innerJoin('#__users AS u ON a.user_id = u.id')
			->where($db->quoteName('a.extension') . ' = ' . $db->quote($extension))
			->where($db->quoteName('a.item_id') . ' = ' . (int) $itemId);

		// Get ordering
		$fullorderCol = $this->getState('list.fullordering', 'a.id DESC');

		// Apply ordering
		if (!empty($fullorderCol))
		{
			$query->order($db->escape($fullorderCol));
		}

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get logs data into JTable object
	 *
	 * @param   integer[]|null  $pks  An optional array of log record IDs to load
	 *
	 * @return  array  All logs in the table
	 *
	 * @since   3.9.0
	 */
	public function getLogsData($pks = null)
	{
		$db    = $this->getDbo();
		$query = $this->getLogDataQuery($pks);

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get logs data as a database iterator
	 *
	 * @param   integer[]|null  $pks  An optional array of log record IDs to load
	 *
	 * @return  JDatabaseIterator
	 *
	 * @since   3.9.0
	 */
	public function getLogDataAsIterator($pks = null)
	{
		$db    = $this->getDbo();
		$query = $this->getLogDataQuery($pks);

		$db->setQuery($query);

		return $db->getIterator();
	}

	/**
	 * Get the query for loading logs data
	 *
	 * @param   integer[]|null  $pks  An optional array of log record IDs to load
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.9.0
	 */
	private function getLogDataQuery($pks = null)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->select('a.*, u.name')
			->from('#__action_logs AS a')
			->innerJoin('#__users AS u ON a.user_id = u.id');

		if (is_array($pks) && count($pks) > 0)
		{
			$query->where($db->quoteName('a.id') . ' IN (' . implode(',', ArrayHelper::toInteger($pks)) . ')');
		}

		return $query;
	}

	/**
	 * Delete logs
	 *
	 * @param   array  $pks  Primary keys of logs
	 *
	 * @return  boolean
	 *
	 * @since   3.9.0
	 */
	public function delete(&$pks)
	{
		$db    = $this->getDbo();
		$query = $db->getQuery(true)
			->delete($db->quoteName('#__action_logs'))
			->where($db->quoteName('id') . ' IN (' . implode(',', ArrayHelper::toInteger($pks)) . ')');
		$db->setQuery($query);

		try
		{
			$db->execute();
		}
		catch (RuntimeException $e)
		{
			$this->setError($e->getMessage());

			return false;
		}

		Factory::getApplication()->triggerEvent('onAfterLogPurge', array());

		return true;
	}

	/**
	 * Removes all of logs from the table.
	 *
	 * @return  boolean result of operation
	 *
	 * @since   3.9.0
	 */
	public function purge()
	{
		try
		{
			$this->getDbo()->truncateTable('#__action_logs');
		}
		catch (Exception $e)
		{
			return false;
		}

		Factory::getApplication()->triggerEvent('onAfterLogPurge', array());

		return true;
	}

	/**
	 * Get the filter form
	 *
	 * @param   array    $data      data
	 * @param   boolean  $loadData  load current data
	 *
	 * @return  \JForm|boolean  The \JForm object or false on error
	 *
	 * @since  3.9.0
	 */
	public function getFilterForm($data = array(), $loadData = true)
	{
		$form      = parent::getFilterForm($data, $loadData);
		$params    = ComponentHelper::getParams('com_actionlogs');
		$ipLogging = (bool) $params->get('ip_logging', 0);

		// Add ip sort options to sort dropdown
		if ($form && $ipLogging)
		{
			/* @var JFormFieldList $field */
			$field = $form->getField('fullordering', 'list');
			$field->addOption(Text::_('COM_ACTIONLOGS_IP_ADDRESS_ASC'), array('value' => 'a.ip_address ASC'));
			$field->addOption(Text::_('COM_ACTIONLOGS_IP_ADDRESS_DESC'), array('value' => 'a.ip_address DESC'));
		}

		return $form;
	}
}
