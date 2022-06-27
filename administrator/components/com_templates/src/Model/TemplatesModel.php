<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Templates\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Database\ParameterType;
use Joomla\String\StringHelper;

/**
 * Methods supporting a list of template extension records.
 *
 * @since  1.6
 */
class TemplatesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array                $config   An optional associative array of configuration settings.
     * @param   MVCFactoryInterface  $factory  The factory.
     *
     * @see     \Joomla\CMS\MVC\Model\BaseDatabaseModel
     * @since   3.2
     */
    public function __construct($config = array(), MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id', 'a.id',
                'name', 'a.name',
                'folder', 'a.folder',
                'element', 'a.element',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'enabled', 'a.enabled',
                'ordering', 'a.ordering',
            );
        }

        parent::__construct($config, $factory);
    }

    /**
     * Override parent getItems to add extra XML metadata.
     *
     * @return  array
     *
     * @since   1.6
     */
    public function getItems()
    {
        $items = parent::getItems();

        foreach ($items as &$item) {
            $client = ApplicationHelper::getClientInfo($item->client_id);
            $item->xmldata = TemplatesHelper::parseXMLTemplateFile($client->path, $item->element);
            $num = $this->updated($item->extension_id);

            if ($num) {
                $item->updated = $num;
            }
        }

        return $items;
    }

    /**
     * Check if template extension have any updated override.
     *
     * @param   integer  $exid  Extension id of template.
     *
     * @return   boolean  False if records not found/else integer.
     *
     * @since   4.0.0
     */
    public function updated($exid)
    {
        $db = $this->getDatabase();

        // Select the required fields from the table
        $query = $db->getQuery(true)
            ->select($db->quoteName('template'))
            ->from($db->quoteName('#__template_overrides'))
            ->where($db->quoteName('extension_id') . ' = :extensionid')
            ->where($db->quoteName('state') . ' = 0')
            ->bind(':extensionid', $exid, ParameterType::INTEGER);

        // Reset the query.
        $db->setQuery($query);

        // Load the results as a list of stdClass objects.
        $num = count($db->loadObjectList());

        if ($num > 0) {
            return $num;
        }

        return false;
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.extension_id, a.name, a.element, a.client_id'
            )
        );
        $clientId = (int) $this->getState('client_id');
        $query->from($db->quoteName('#__extensions', 'a'))
            ->where($db->quoteName('a.client_id') . ' = :clientid')
            ->where($db->quoteName('a.enabled') . ' = 1')
            ->where($db->quoteName('a.type') . ' = ' . $db->quote('template'))
            ->bind(':clientid', $clientId, ParameterType::INTEGER);

        // Filter by search in title.
        if ($search = $this->getState('filter.search')) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id');
                $query->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . StringHelper::strtolower($search) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        'LOWER(' . $db->quoteName('a.element') . ') LIKE :element',
                        'LOWER(' .  $db->quoteName('a.name') . ') LIKE :name',
                    ],
                    'OR'
                )
                    ->bind(':element', $search)
                    ->bind(':name', $search);
            }
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.element')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

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
     * @return  string  A store id.
     *
     * @since   1.6
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('client_id');
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
     * @since   1.6
     */
    protected function populateState($ordering = 'a.element', $direction = 'asc')
    {
        // Load the filter state.
        $this->setState('filter.search', $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string'));

        // Special case for the client id.
        $clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
        $clientId = (!in_array($clientId, array (0, 1))) ? 0 : $clientId;
        $this->setState('client_id', $clientId);

        // Load the parameters.
        $params = ComponentHelper::getParams('com_templates');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);
    }
}
