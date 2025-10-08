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
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Requests management model class.
 *
 * @since  3.9.0
 */
class RequestsModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                 $config   An optional associative array of configuration settings.
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   3.9.0
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'email', 'a.email',
                'requested_at', 'a.requested_at',
                'request_type', 'a.request_type',
                'status', 'a.status',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to get a QueryInterface object for retrieving the data set from a database.
     *
     * @return  QueryInterface
     *
     * @since   3.9.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->createQuery();

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'a.*'));
        $query->from($db->quoteName('#__privacy_requests', 'a'));

        // Filter by status
        $status = $this->getState('filter.status');

        if (is_numeric($status)) {
            $status = (int) $status;
            $query->where($db->quoteName('a.status') . ' = :status')
                ->bind(':status', $status, ParameterType::INTEGER);
        }

        // Filter by request type
        $requestType = $this->getState('filter.request_type', '');

        if ($requestType) {
            $query->where($db->quoteName('a.request_type') . ' = :requesttype')
                ->bind(':requesttype', $requestType);
        }

        // Filter by search in email
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . $search . '%';
                $query->where('(' . $db->quoteName('a.email') . ' LIKE :search)')
                    ->bind(':search', $search);
            }
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
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.request_type');

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
        // Load the parameters.
        $this->setState('params', ComponentHelper::getParams('com_privacy'));

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Method to return number privacy requests older than X days.
     *
     * @return  integer
     *
     * @since   3.9.0
     */
    public function getNumberUrgentRequests()
    {
        // Load the parameters.
        $params = ComponentHelper::getComponent('com_privacy')->getParams();
        $notify = (int) $params->get('notify', 14);
        $now    = Factory::getDate()->toSql();
        $period = '-' . $notify;

        $db    = $this->getDatabase();
        $query = $db->createQuery()
            ->select('COUNT(*)');
        $query->from($db->quoteName('#__privacy_requests'));
        $query->where($db->quoteName('status') . ' = 1 ');
        $query->where($query->dateAdd($db->quote($now), $period, 'DAY') . ' > ' . $db->quoteName('requested_at'));
        $db->setQuery($query);

        return (int) $db->loadResult();
    }
}
