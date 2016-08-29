<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_userlogs
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * Methods supporting a list of article records.
 *
 * @since  __DEPLOY_VERSION__
 */
class UserlogsModelUserlogs extends JModelList
{
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
				'a.ip_address', 'ip_address'
			);
		}

		parent::__construct($config);
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	protected function populateState($ordering = 'a.id', $direction = 'desc')
	{
		$app = JFactory::getApplication();

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
	 * @since   __DEPLOY_VERSION__
	 */
	protected function getListQuery()
	{
		$this->checkIn();

		$db    = $this->getDbo();
		$query = $db->getQuery(true)
				->select('a.*')
				->from($db->quoteName('#__user_logs', 'a'));

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
			$query->where($db->quoteName('a.extension') . ' = ' . $db->quote($extension));
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
			$search = $db->quote('%' . str_replace(' ', '%', $db->escape(trim($search), true) . '%'));
			$query->where('(a.message LIKE ' . $search . ')');
		}

		return $query;
	}

	/**
	 * Check for old logs that needs to be deleted_comment
	 *
	 * @return void
	 *
	 * @since __DEPLOY_VERSION__
	 */
	protected function checkIn()
	{
		$plugin = JPluginHelper::getPlugin('system', 'userlogs');

		if (!empty($plugin))
		{
			$pluginParams = new Registry($plugin->params);
			$daysToDeleteAfter = (int) $pluginParams->get('logDeletePeriod', 0);

			if ($daysToDeleteAfter > 0)
			{
				$db = JFactory::getDbo();
				$query = $db->getQuery(true);
				$conditions = array($db->quoteName('log_date') . ' < DATE_SUB(NOW(), INTERVAL ' . $daysToDeleteAfter . ' DAY)');

				$query->delete($db->quoteName('#__user_logs'))->where($conditions);
				$db->setQuery($query);

				try
				{
					$db->execute();
				}
				catch (RuntimeException $e)
				{
					JError::raiseWarning(500, $db->getMessage());

					return false;
				}
			}
		}
	}

	/**
	 * Construct the date range to filter on.
	 *
	 * @param   string  $range  The textual range to construct the filter for.
	 *
	 * @return  string  The date range to filter on.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	private function buildDateRange($range)
	{
		// Get UTC for now.
		$dNow   = new JDate;
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

			case 'post_year':
				$dNow = false;
			case 'past_year':
				$dStart->modify('-1 year');
				break;

			case 'today':
				// Ranges that need to align with local 'days' need special treatment.
				$offset = JFactory::getApplication()->get('offset');

				// Reset the start time to be the beginning of today, local time.
				$dStart = new JDate('now', $offset);
				$dStart->setTime(0, 0, 0);

				// Now change the timezone back to UTC.
				$tz = new DateTimeZone('GMT');
				$dStart->setTimezone($tz);
				break;

			case 'never':
				$dNow = false;
				$dStart = $this->_db->getNullDate();
				break;

			default:
				return $range;
			break;
		}

		return array('dNow' => $dNow, 'dStart' => $dStart);
	}

	/**
	 * Get logs data into JTable object
	 *
	 *
	 * @return  Array  All logs in the table
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function getLogsData($pks = null)
	{
		if ($pks == null)
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
					->select('a.*')
					->from($db->quoteName('#__user_logs', 'a'));
			$db->setQuery($query);

			return $db->loadObjectList();
		}
		else
		{
			$items = array();
			JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_userlogs/tables');
			$table = $this->getTable('Userlogs', 'JTable');

			foreach ($pks as $i => $pk)
			{
				$table->load($pk);
				$items[] = (object) array(
					'id'         => $table->id,
					'message'    => $table->message,
					'log_date'   => $table->log_date,
					'extension'  => $table->extension,
					'user_id'    => $table->user_id,
					'ip_address' => $table->ip_address,
				);
			}

			return $items;
		}
	}

	/**
	 * Delete logs
	 *
	 * @param   array  $pks  Primary keys of logs
	 *
	 * @return  boolean
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function delete(&$pks)
	{
		// Check for request forgeries
		JSession::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get the table
		JTable::addIncludePath(JPATH_ADMINISTRATOR . '/components/com_userlogs/tables');
		$table = $this->getTable('Userlogs', 'JTable');

		if (!JFactory::getUser()->authorise('core.delete', $this->option))
		{
			$error = $this->getError();

			if ($error)
			{
				$this->setError($error);
			}
			else
			{
				$this->setError(JText::_('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED'));
			}

			return false;
		}
		else
		{
			foreach ($pks as $i => $pk)
			{
				if (!$table->delete($pk))
				{
					$this->setError($table->getError());

					return false;
				}
			}
		}

		return true;
	}
}
