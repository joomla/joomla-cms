<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Plugins\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Methods supporting a list of plugin records.
 *
 * @since  1.6
 */
class PluginsModel extends ListModel
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
    public function __construct($config = [], MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'extension_id', 'a.extension_id',
                'name', 'a.name',
                'folder', 'a.folder',
                'element', 'a.element',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'state', 'a.state',
                'enabled', 'a.enabled',
                'access', 'a.access', 'access_level',
                'ordering', 'a.ordering',
                'client_id', 'a.client_id',
            ];
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
     * @since   1.6
     */
    protected function populateState($ordering = 'folder', $direction = 'asc')
    {
        // Load the parameters.
        $params = ComponentHelper::getParams('com_plugins');
        $this->setState('params', $params);

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
     * @return  string       A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.enabled');
        $id .= ':' . $this->getState('filter.folder');
        $id .= ':' . $this->getState('filter.element');

        return parent::getStoreId($id);
    }

    /**
     * Returns an object list.
     *
     * @param   \Joomla\Database\DatabaseQuery  $query       A database query object.
     * @param   integer                         $limitstart  Offset.
     * @param   integer                         $limit       The number of records.
     *
     * @return  array
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $search   = $this->getState('filter.search');
        $ordering = $this->getState('list.ordering', 'ordering');

        // If "Sort Table By:" is not set, set ordering to name
        if ($ordering == '') {
            $ordering = 'name';
        }

        $db = $this->getDatabase();

        if ($ordering == 'name' || (!empty($search) && stripos($search, 'id:') !== 0)) {
            $db->setQuery($query);
            $result = $db->loadObjectList();
            $this->translate($result);

            if (!empty($search)) {
                $escapedSearchString = $this->refineSearchStringToRegex($search, '/');

                foreach ($result as $i => $item) {
                    if (!preg_match("/$escapedSearchString/i", $item->name)) {
                        unset($result[$i]);
                    }
                }
            }

            $orderingDirection = strtolower($this->getState('list.direction'));
            $direction         = ($orderingDirection == 'desc') ? -1 : 1;
            $result            = ArrayHelper::sortObjects($result, $ordering, $direction, true, true);

            $total                                      = count($result);
            $this->cache[$this->getStoreId('getTotal')] = $total;

            if ($total < $limitstart) {
                $limitstart = 0;
            }

            $this->cache[$this->getStoreId('getStart')] = $limitstart;

            return array_slice($result, $limitstart, $limit ?: null);
        } else {
            if ($ordering == 'ordering') {
                $query->order('a.folder ASC');
                $ordering = 'a.ordering';
            }

            $query->order($db->quoteName($ordering) . ' ' . $this->getState('list.direction'));

            if ($ordering == 'folder') {
                $query->order('a.ordering ASC');
            }

            $result = parent::_getList($query, $limitstart, $limit);
            $this->translate($result);

            return $result;
        }
    }

    /**
     * Translate a list of objects.
     *
     * @param   array  &$items  The array of objects.
     *
     * @return  array The array of translated objects.
     */
    protected function translate(&$items)
    {
        $lang = Factory::getLanguage();

        foreach ($items as &$item) {
            $source    = JPATH_PLUGINS . '/' . $item->folder . '/' . $item->element;
            $extension = 'plg_' . $item->folder . '_' . $item->element;
            $lang->load($extension . '.sys', JPATH_ADMINISTRATOR)
                || $lang->load($extension . '.sys', $source);
            $item->name = Text::_($item->name);
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                'a.extension_id , a.name, a.element, a.folder, a.checked_out, a.checked_out_time,' .
                    ' a.enabled, a.access, a.ordering, a.note'
            )
        )
            ->from($db->quoteName('#__extensions') . ' AS a')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'));

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')
            ->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')
            ->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by published state.
        $published = (string) $this->getState('filter.enabled');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.enabled') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->whereIn($db->quoteName('a.enabled'), [0, 1]);
        }

        // Filter by state.
        $query->where('a.state >= 0');

        // Filter by folder.
        if ($folder = $this->getState('filter.folder')) {
            $query->where($db->quoteName('a.folder') . ' = :folder')
                ->bind(':folder', $folder);
        }

        // Filter by element.
        if ($element = $this->getState('filter.element')) {
            $query->where($db->quoteName('a.element') . ' = :element')
                ->bind(':element', $element);
        }

        // Filter by search in name or id.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.extension_id') . ' = :id');
                $query->bind(':id', $ids, ParameterType::INTEGER);
            }
        }

        return $query;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed   The data for the form.
     *
     * @since   3.5
     */
    protected function loadFormData()
    {
        $data = parent::loadFormData();

        // Set the selected filter values for pages that use the Layouts for filtering
        $data->list['sortTable']      = $this->state->get('list.ordering');
        $data->list['directionTable'] = $this->state->get('list.direction');

        return $data;
    }
}
