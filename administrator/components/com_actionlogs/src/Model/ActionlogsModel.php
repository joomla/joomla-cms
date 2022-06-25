<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_actionlogs
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Actionlogs\Administrator\Model;

use DateTimeZone;
use Exception;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseIterator;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;
use RuntimeException;

/**
 * Methods supporting a list of article records.
 *
 * @since  3.9.0
 */
class ActionlogsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    public function __construct($config = array())
    {
        if (empty($config['filter_fields'])) {
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
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        parent::populateState($ordering, $direction);
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  DatabaseQuery
     *
     * @since   3.9.0
     *
     * @throws  Exception
     */
    protected function getListQuery()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->select($db->quoteName('u.name'))
            ->from($db->quoteName('#__action_logs', 'a'))
            ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id'));

        // Get ordering
        $fullorderCol = $this->state->get('list.fullordering', 'a.id DESC');

        // Apply ordering
        if (!empty($fullorderCol)) {
            $query->order($db->escape($fullorderCol));
        }

        // Get filter by user
        $user = $this->getState('filter.user');

        // Apply filter by user
        if (!empty($user)) {
            $user = (int) $user;
            $query->where($db->quoteName('a.user_id') . ' = :userid')
                ->bind(':userid', $user, ParameterType::INTEGER);
        }

        // Get filter by extension
        $extension = $this->getState('filter.extension');

        // Apply filter by extension
        if (!empty($extension)) {
            $extension = $extension . '%';
            $query->where($db->quoteName('a.extension') . ' LIKE :extension')
                ->bind(':extension', $extension);
        }

        // Get filter by date range
        $dateRange = $this->getState('filter.dateRange');

        // Apply filter by date range
        if (!empty($dateRange)) {
            $date = $this->buildDateRange($dateRange);

            // If the chosen range is not more than a year ago
            if ($date['dNow'] !== false && $date['dStart'] !== false) {
                $dStart = $date['dStart']->format('Y-m-d H:i:s');
                $dNow   = $date['dNow']->format('Y-m-d H:i:s');
                $query->where(
                    $db->quoteName('a.log_date') . ' BETWEEN :dstart AND :dnow'
                );
                $query->bind(':dstart', $dStart);
                $query->bind(':dnow', $dNow);
            }
        }

        // Filter the items over the search string if set.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $ids, ParameterType::INTEGER);
            } elseif (stripos($search, 'item_id:') === 0) {
                $ids = (int) substr($search, 8);
                $query->where($db->quoteName('a.item_id') . ' = :itemid')
                    ->bind(':itemid', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . $search . '%';
                $query->where($db->quoteName('a.message') . ' LIKE :message')
                    ->bind(':message', $search);
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
     *
     * @throws  Exception
     */
    private function buildDateRange($range)
    {
        // Get UTC for now.
        $dNow   = new Date();
        $dStart = clone $dNow;

        switch ($range) {
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
        $itemId = (int) $itemId;
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true)
            ->select('a.*')
            ->select($db->quoteName('u.name'))
            ->from($db->quoteName('#__action_logs', 'a'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id'))
            ->where($db->quoteName('a.extension') . ' = :extension')
            ->where($db->quoteName('a.item_id') . ' = :itemid')
            ->bind(':extension', $extension)
            ->bind(':itemid', $itemId, ParameterType::INTEGER);

        // Get ordering
        $fullorderCol = $this->getState('list.fullordering', 'a.id DESC');

        // Apply ordering
        if (!empty($fullorderCol)) {
            $query->order($db->escape($fullorderCol));
        }

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get logs data into Table object
     *
     * @param   integer[]|null  $pks  An optional array of log record IDs to load
     *
     * @return  array  All logs in the table
     *
     * @since   3.9.0
     */
    public function getLogsData($pks = null)
    {
        $db    = $this->getDatabase();
        $query = $this->getLogDataQuery($pks);

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * Get logs data as a database iterator
     *
     * @param   integer[]|null  $pks  An optional array of log record IDs to load
     *
     * @return  DatabaseIterator
     *
     * @since   3.9.0
     */
    public function getLogDataAsIterator($pks = null)
    {
        $db    = $this->getDatabase();
        $query = $this->getLogDataQuery($pks);

        $db->setQuery($query);

        return $db->getIterator();
    }

    /**
     * Get the query for loading logs data
     *
     * @param   integer[]|null  $pks  An optional array of log record IDs to load
     *
     * @return  DatabaseQuery
     *
     * @since   3.9.0
     */
    private function getLogDataQuery($pks = null)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->select('a.*')
            ->select($db->quoteName('u.name'))
            ->from($db->quoteName('#__action_logs', 'a'))
            ->join('INNER', $db->quoteName('#__users', 'u') . ' ON ' . $db->quoteName('a.user_id') . ' = ' . $db->quoteName('u.id'));

        if (\is_array($pks) && \count($pks) > 0) {
            $pks = ArrayHelper::toInteger($pks);
            $query->whereIn($db->quoteName('a.id'), $pks);
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
        $keys  = ArrayHelper::toInteger($pks);
        $db    = $this->getDatabase();
        $query = $db->getQuery(true)
            ->delete($db->quoteName('#__action_logs'))
            ->whereIn($db->quoteName('id'), $keys);
        $db->setQuery($query);

        try {
            $db->execute();
        } catch (RuntimeException $e) {
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
        try {
            $this->getDatabase()->truncateTable('#__action_logs');
        } catch (Exception $e) {
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
     * @return  Form|boolean  The Form object or false on error
     *
     * @since   3.9.0
     */
    public function getFilterForm($data = array(), $loadData = true)
    {
        $form      = parent::getFilterForm($data, $loadData);
        $params    = ComponentHelper::getParams('com_actionlogs');
        $ipLogging = (bool) $params->get('ip_logging', 0);

        // Add ip sort options to sort dropdown
        if ($form && $ipLogging) {
            /* @var \Joomla\CMS\Form\Field\ListField $field */
            $field = $form->getField('fullordering', 'list');
            $field->addOption(Text::_('COM_ACTIONLOGS_IP_ADDRESS_ASC'), array('value' => 'a.ip_address ASC'));
            $field->addOption(Text::_('COM_ACTIONLOGS_IP_ADDRESS_DESC'), array('value' => 'a.ip_address DESC'));
        }

        return $form;
    }
}
