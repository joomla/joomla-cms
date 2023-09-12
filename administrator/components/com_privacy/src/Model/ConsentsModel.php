<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_privacy
 *
 * @copyright   (C) 2018 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Privacy\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\DatabaseQuery;
use Joomla\Database\Exception\ExecutionFailureException;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Consents management model class.
 *
 * @since  3.9.0
 */
class ConsentsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @since   3.9.0
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'user_id', 'a.user_id',
                'subject', 'a.subject',
                'created', 'a.created',
                'username', 'u.username',
                'name', 'u.name',
                'state', 'a.state',
            ];
        }

        parent::__construct($config);
    }

    /**
     * Method to get a DatabaseQuery object for retrieving the data set from a database.
     *
     * @return  DatabaseQuery
     *
     * @since   3.9.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'a.*'));
        $query->from($db->quoteName('#__privacy_consents', 'a'));

        // Join over the users for the username and name.
        $query->select($db->quoteName('u.username', 'username'))
            ->select($db->quoteName('u.name', 'name'));
        $query->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = a.user_id');

        // Filter by search in email
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $ids, ParameterType::INTEGER);
            } elseif (stripos($search, 'uid:') === 0) {
                $uid = (int) substr($search, 4);
                $query->where($db->quoteName('a.user_id') . ' = :uid')
                    ->bind(':uid', $uid, ParameterType::INTEGER);
            } elseif (stripos($search, 'name:') === 0) {
                $search = '%' . substr($search, 5) . '%';
                $query->where($db->quoteName('u.name') . ' LIKE :search')
                    ->bind(':search', $search);
            } else {
                $search = '%' . $search . '%';
                $query->where('(' . $db->quoteName('u.username') . ' LIKE :search)')
                    ->bind(':search', $search);
            }
        }

        $state = $this->getState('filter.state');

        if ($state != '') {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        }

        $subject = $this->getState('filter.subject');

        if (!empty($subject)) {
            $query->where($db->quoteName('a.subject') . ' = :subject')
                ->bind(':subject', $subject, ParameterType::STRING);
        }

        // Handle the list ordering.
        $ordering  = $this->getState('list.ordering');
        $direction = $this->getState('list.direction');

        if (!empty($ordering)) {
            $query->order($db->escape($ordering) . ' ' . $db->escape($direction));
        }

        return $query;
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
     * @return  string
     *
     * @since   3.9.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');

        return parent::getStoreId($id);
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
     * @since   3.9.0
     */
    protected function populateState($ordering = 'a.id', $direction = 'desc')
    {
        // Load the filter state.
        $this->setState(
            'filter.search',
            $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search')
        );

        $this->setState(
            'filter.subject',
            $this->getUserStateFromRequest($this->context . '.filter.subject', 'filter_subject')
        );

        $this->setState(
            'filter.state',
            $this->getUserStateFromRequest($this->context . '.filter.state', 'filter_state')
        );

        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_privacy'));

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to invalidate specific consents.
     *
     * @param   array  $pks  The ids of the consents to invalidate.
     *
     * @return  boolean  True on success.
     */
    public function invalidate($pks)
    {
        // Sanitize the ids.
        $pks = (array) $pks;
        $pks = ArrayHelper::toInteger($pks);

        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__privacy_consents'))
                ->set($db->quoteName('state') . ' = -1')
                ->whereIn($db->quoteName('id'), $pks)
                ->where($db->quoteName('state') . ' = 1');
            $db->setQuery($query);
            $db->execute();
        } catch (ExecutionFailureException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }

    /**
     * Method to invalidate a group of specific consents.
     *
     * @param   array  $subject  The subject of the consents to invalidate.
     *
     * @return  boolean  True on success.
     */
    public function invalidateAll($subject)
    {
        try {
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__privacy_consents'))
                ->set($db->quoteName('state') . ' = -1')
                ->where($db->quoteName('subject') . ' = :subject')
                ->where($db->quoteName('state') . ' = 1')
                ->bind(':subject', $subject);
            $db->setQuery($query);
            $db->execute();
        } catch (ExecutionFailureException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return true;
    }
}
