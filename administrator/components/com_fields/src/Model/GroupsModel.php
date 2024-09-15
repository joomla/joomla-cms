<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Fields\Administrator\Model;

use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Groups Model
 *
 * @since  3.7.0
 */
class GroupsModel extends ListModel
{
    /**
     * Context string for the model type.  This is used to handle uniqueness
     * when dealing with the getStoreId() method and caching data structures.
     *
     * @var    string
     * @since   3.7.0
     */
    protected $context = 'com_fields.groups';

    /**
     * Constructor
     *
     * @param   array                 $config   An array of configuration options (name, state, dbo, table_path, ignore_request).
     * @param   ?MVCFactoryInterface  $factory  The factory.
     *
     * @since   3.7.0
     * @throws  \Exception
     */
    public function __construct($config = [], ?MVCFactoryInterface $factory = null)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'type', 'a.type',
                'state', 'a.state',
                'access', 'a.access',
                'access_level',
                'language', 'a.language',
                'ordering', 'a.ordering',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'created', 'a.created',
                'created_by', 'a.created_by',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Method to auto-populate the model state.
     *
     * This method should only be called once per instantiation and is designed
     * to be called on the first call to the getState() method unless the model
     * configuration flag to ignore the request is set.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @param   string  $ordering   An optional ordering field.
     * @param   string  $direction  An optional direction (asc|desc).
     *
     * @return  void
     *
     * @since   3.7.0
     */
    protected function populateState($ordering = null, $direction = null)
    {
        // List state information.
        parent::populateState('a.ordering', 'asc');

        $context = $this->getUserStateFromRequest($this->context . '.context', 'context', 'com_content', 'CMD');
        $this->setState('filter.context', $context);
    }

    /**
     * Method to get a store id based on the model configuration state.
     *
     * This is necessary because the model is used by the component and
     * different modules that might need different sets of data or different
     * ordering requirements.
     *
     * @param   string  $id  An identifier string to generate the store id.
     *
     * @return  string  A store id.
     *
     * @since   3.7.0
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.context');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . print_r($this->getState('filter.language'), true);

        return parent::getStoreId($id);
    }

    /**
     * Method to get a QueryInterface object for retrieving the data set from a database.
     *
     * @return  QueryInterface   An object implementing QueryInterface to retrieve the data set.
     *
     * @since   3.7.0
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $user  = $this->getCurrentUser();

        // Select the required fields from the table.
        $query->select($this->getState('list.select', 'a.*'));
        $query->from('#__fields_groups AS a');

        // Join over the language
        $query->select('l.title AS language_title, l.image AS language_image')
            ->join('LEFT', $db->quoteName('#__languages') . ' AS l ON l.lang_code = a.language');

        // Join over the users for the checked out user.
        $query->select('uc.name AS editor')->join('LEFT', '#__users AS uc ON uc.id=a.checked_out');

        // Join over the asset groups.
        $query->select('ag.title AS access_level')->join('LEFT', '#__viewlevels AS ag ON ag.id = a.access');

        // Join over the users for the author.
        $query->select('ua.name AS author_name')->join('LEFT', '#__users AS ua ON ua.id = a.created_by');

        // Filter by context
        if ($context = $this->getState('filter.context', 'com_fields')) {
            $query->where($db->quoteName('a.context') . ' = :context')
                ->bind(':context', $context);
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            if (\is_array($access)) {
                $access = ArrayHelper::toInteger($access);
                $query->whereIn($db->quoteName('a.access'), $access);
            } else {
                $access = (int) $access;
                $query->where($db->quoteName('a.access') . ' = :access')
                    ->bind(':access', $access, ParameterType::INTEGER);
            }
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        // Filter by published state
        $state = $this->getState('filter.state');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.state') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } elseif (!$state) {
            $query->whereIn($db->quoteName('a.state'), [0, 1]);
        }

        // Filter by search in title
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->where($db->quoteName('a.title') . ' LIKE :search')
                    ->bind(':search', $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $language = (array) $language;

            $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
        }

        // Add the list ordering clause
        $listOrdering = $this->getState('list.ordering', 'a.ordering');
        $listDirn     = $db->escape($this->getState('list.direction', 'ASC'));

        $query->order($db->escape($listOrdering) . ' ' . $listDirn);

        return $query;
    }

    /**
     * Gets an array of objects from the results of database query.
     *
     * @param   string   $query       The query.
     * @param   integer  $limitstart  Offset.
     * @param   integer  $limit       The number of records.
     *
     * @return  array  An array of results.
     *
     * @since   3.8.7
     * @throws  \RuntimeException
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $result = parent::_getList($query, $limitstart, $limit);

        if (\is_array($result)) {
            foreach ($result as $group) {
                $group->params = new Registry($group->params);
            }
        }

        return $result;
    }
}
