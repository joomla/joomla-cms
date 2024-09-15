<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Modules\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;
use Joomla\Database\QueryInterface;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Modules Component Module Model
 *
 * @since  1.5
 */
class ModulesModel extends ListModel
{
    /**
     * Constructor.
     *
     * @param   array  $config  An optional associative array of configuration settings.
     *
     * @see     \JController
     * @since   1.6
     */
    public function __construct($config = [])
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = [
                'id', 'a.id',
                'title', 'a.title',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'published', 'a.published', 'state',
                'access', 'a.access',
                'ag.title', 'access_level',
                'ordering', 'a.ordering',
                'module', 'a.module',
                'language', 'a.language',
                'l.title', 'language_title',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'client_id', 'a.client_id',
                'position', 'a.position',
                'pages',
                'name', 'e.name',
                'menuitem',
            ];
        }

        parent::__construct($config);
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
    protected function populateState($ordering = 'a.position', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $layout = $app->getInput()->get('layout', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout) {
            $this->context .= '.' . $layout;
        }

        // Make context client aware
        $this->context .= '.' . $app->getInput()->get->getInt('client_id', 0);

        // If in modal layout on the frontend, state and language are always forced.
        if ($app->isClient('site') && $layout === 'modal') {
            $this->setState('filter.language', 'current');
            $this->setState('filter.state', 1);
        }

        // Special case for the client id.
        if ($app->isClient('site') || $layout === 'modal') {
            $this->setState('client_id', 0);
            $clientId = 0;
        } else {
            $clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
            $clientId = (!\in_array($clientId, [0, 1])) ? 0 : $clientId;
            $this->setState('client_id', $clientId);
        }

        // Use a different filter file when client is administrator
        if ($clientId == 1) {
            $this->filterFormName = 'filter_modulesadmin';
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_modules');
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
     * @return  string    A store id.
     */
    protected function getStoreId($id = '')
    {
        // Compile the store id.
        $id .= ':' . $this->getState('client_id');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.state');
        $id .= ':' . $this->getState('filter.position');
        $id .= ':' . $this->getState('filter.module');
        $id .= ':' . $this->getState('filter.menuitem');
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.language');

        return parent::getStoreId($id);
    }

    /**
     * Returns an object list
     *
     * @param   QueryInterface  $query       The query
     * @param   int             $limitstart  Offset
     * @param   int             $limit       The number of records
     *
     * @return  array
     */
    protected function _getList($query, $limitstart = 0, $limit = 0)
    {
        $listOrder = $this->getState('list.ordering', 'a.position');
        $listDirn  = $this->getState('list.direction', 'asc');

        $db = $this->getDatabase();

        // If ordering by fields that need translate we need to sort the array of objects after translating them.
        if (\in_array($listOrder, ['pages', 'name'])) {
            // Fetch the results.
            $db->setQuery($query);
            $result = $db->loadObjectList();

            // Translate the results.
            $this->translate($result);

            // Sort the array of translated objects.
            $result = ArrayHelper::sortObjects($result, $listOrder, strtolower($listDirn) == 'desc' ? -1 : 1, true, true);

            // Process pagination.
            $total                                      = \count($result);
            $this->cache[$this->getStoreId('getTotal')] = $total;

            if ($total < $limitstart) {
                $limitstart = 0;
                $this->setState('list.start', 0);
            }

            return \array_slice($result, $limitstart, $limit ?: null);
        }

        // If ordering by fields that doesn't need translate just order the query.
        if ($listOrder === 'a.ordering') {
            $query->order($db->quoteName('a.position') . ' ASC')
                ->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn));
        } elseif ($listOrder === 'a.position') {
            $query->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn))
                ->order($db->quoteName('a.ordering') . ' ASC');
        } else {
            $query->order($db->quoteName($listOrder) . ' ' . $db->escape($listDirn));
        }

        // Process pagination.
        $result = parent::_getList($query, $limitstart, $limit);

        // Translate the results.
        $this->translate($result);

        return $result;
    }

    /**
     * Translate a list of objects
     *
     * @param   array  &$items  The array of objects
     *
     * @return  void
     */
    protected function translate(&$items)
    {
        $lang       = Factory::getLanguage();
        $clientPath = $this->getState('client_id') ? JPATH_ADMINISTRATOR : JPATH_SITE;

        foreach ($items as $item) {
            $extension = $item->module;
            $source    = $clientPath . "/modules/$extension";
            $lang->load("$extension.sys", $clientPath)
                || $lang->load("$extension.sys", $source);
            $item->name = Text::_($item->name);

            if (\is_null($item->pages)) {
                $item->pages = Text::_('JNONE');
            } elseif ($item->pages < 0) {
                $item->pages = Text::_('COM_MODULES_ASSIGNED_VARIES_EXCEPT');
            } elseif ($item->pages > 0) {
                $item->pages = Text::_('COM_MODULES_ASSIGNED_VARIES_ONLY');
            } else {
                $item->pages = Text::_('JALL');
            }
        }
    }

    /**
     * Build an SQL query to load the list data.
     *
     * @return  QueryInterface
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Select the required fields.
        $query->select(
            $this->getState(
                'list.select',
                'a.id, a.title, a.note, a.position, a.module, a.language,' .
                    'a.checked_out, a.checked_out_time, a.published AS published, e.enabled AS enabled, a.access, a.ordering, a.publish_up, a.publish_down'
            )
        );

        // From modules table.
        $query->from($db->quoteName('#__modules', 'a'));

        // Join over the language
        $query->select($db->quoteName('l.title', 'language_title'))
            ->select($db->quoteName('l.image', 'language_image'))
            ->join('LEFT', $db->quoteName('#__languages', 'l') . ' ON ' . $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        // Join over the users for the checked out user.
        $query->select($db->quoteName('uc.name', 'editor'))
            ->join('LEFT', $db->quoteName('#__users', 'uc') . ' ON ' . $db->quoteName('uc.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Join over the asset groups.
        $query->select($db->quoteName('ag.title', 'access_level'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag') . ' ON ' . $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

        // Join over the module menus
        $query->select('MIN(mm.menuid) AS pages')
            ->join('LEFT', $db->quoteName('#__modules_menu', 'mm') . ' ON ' . $db->quoteName('mm.moduleid') . ' = ' . $db->quoteName('a.id'));

        // Join over the extensions
        $query->select($db->quoteName('e.name', 'name'))
            ->join('LEFT', $db->quoteName('#__extensions', 'e') . ' ON ' . $db->quoteName('e.element') . ' = ' . $db->quoteName('a.module'));

        // Group (careful with PostgreSQL)
        $query->group(
            'a.id, a.title, a.note, a.position, a.module, a.language, a.checked_out, '
            . 'a.checked_out_time, a.published, a.access, a.ordering, l.title, l.image, uc.name, ag.title, e.name, '
            . 'l.lang_code, uc.id, ag.id, mm.moduleid, e.element, a.publish_up, a.publish_down, e.enabled'
        );

        // Filter by client.
        $clientId = (int) $this->getState('client_id');
        $query->where($db->quoteName('a.client_id') . ' = :aclientid')
            ->where($db->quoteName('e.client_id') . ' = :eclientid')
            ->bind(':aclientid', $clientId, ParameterType::INTEGER)
            ->bind(':eclientid', $clientId, ParameterType::INTEGER);

        // Filter by current user access level.
        $user = $this->getCurrentUser();

        // Get the current user for authorisation checks
        if ($user->authorise('core.admin') !== true) {
            $groups = $user->getAuthorisedViewLevels();
            $query->whereIn($db->quoteName('a.access'), $groups);
        }

        // Filter by access level.
        if ($access = $this->getState('filter.access')) {
            $access = (int) $access;
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Filter by published state.
        $state = $this->getState('filter.state', '');

        if (is_numeric($state)) {
            $state = (int) $state;
            $query->where($db->quoteName('a.published') . ' = :state')
                ->bind(':state', $state, ParameterType::INTEGER);
        } elseif ($state === '') {
            $query->whereIn($db->quoteName('a.published'), [0, 1]);
        }

        // Filter by position.
        if ($position = $this->getState('filter.position')) {
            $position = ($position === 'none') ? '' : $position;
            $query->where($db->quoteName('a.position') . ' = :position')
                ->bind(':position', $position);
        }

        // Filter by module.
        if ($module = $this->getState('filter.module')) {
            $query->where($db->quoteName('a.module') . ' = :module')
                ->bind(':module', $module);
        }

        // Filter by menuitem id (only for site client).
        if ((int) $clientId === 0 && $menuItemId = $this->getState('filter.menuitem')) {
            // If user selected the modules not assigned to any page (menu item).
            if ((int) $menuItemId === -1) {
                $query->having('MIN(' . $db->quoteName('mm.menuid') . ') IS NULL');
            } else {
                // If user selected the modules assigned to some particular page (menu item).
                // Modules in "All" pages.
                $subQuery1 = $db->getQuery(true);
                $subQuery1->select('MIN(' . $db->quoteName('menuid') . ')')
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('moduleid') . ' = ' . $db->quoteName('a.id'));

                // Modules in "Selected" pages that have the chosen menu item id.
                $menuItemId      = (int) $menuItemId;
                $minusMenuItemId = $menuItemId * -1;
                $subQuery2       = $db->getQuery(true);
                $subQuery2->select($db->quoteName('moduleid'))
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('menuid') . ' = :menuitemid2');

                // Modules in "All except selected" pages that doesn't have the chosen menu item id.
                $subQuery3 = $db->getQuery(true);
                $subQuery3->select($db->quoteName('moduleid'))
                    ->from($db->quoteName('#__modules_menu'))
                    ->where($db->quoteName('menuid') . ' = :menuitemid3');

                // Filter by modules assigned to the selected menu item.
                $query->where('(
                    (' . $subQuery1 . ') = 0
                    OR ((' . $subQuery1 . ') > 0 AND ' . $db->quoteName('a.id') . ' IN (' . $subQuery2 . '))
                    OR ((' . $subQuery1 . ') < 0 AND ' . $db->quoteName('a.id') . ' NOT IN (' . $subQuery3 . '))
                    )');
                $query->bind(':menuitemid2', $menuItemId, ParameterType::INTEGER);
                $query->bind(':menuitemid3', $minusMenuItemId, ParameterType::INTEGER);
            }
        }

        // Filter by search in title or note or id:.
        $search = $this->getState('filter.search');

        if (!empty($search)) {
            if (stripos($search, 'id:') === 0) {
                $ids = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :id')
                    ->bind(':id', $ids, ParameterType::INTEGER);
            } else {
                $search = '%' . StringHelper::strtolower($search) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        'LOWER(' . $db->quoteName('a.title') . ') LIKE :title',
                        'LOWER(' . $db->quoteName('a.note') . ') LIKE :note',
                    ],
                    'OR'
                )
                    ->bind(':title', $search)
                    ->bind(':note', $search);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            if ($language === 'current') {
                $language = [Factory::getLanguage()->getTag(), '*'];
                $query->whereIn($db->quoteName('a.language'), $language, ParameterType::STRING);
            } else {
                $query->where($db->quoteName('a.language') . ' = :language')
                    ->bind(':language', $language);
            }
        }

        return $query;
    }

    /**
     * Manipulate the query to be used to evaluate if this is an Empty State to provide specific conditions for this extension.
     *
     * @return QueryInterface
     *
     * @since 4.0.0
     */
    protected function getEmptyStateQuery()
    {
        $query = parent::getEmptyStateQuery();

        $clientId = (int) $this->getState('client_id');

        $query->where($this->getDatabase()->quoteName('a.client_id') . ' = :client_id')
            ->bind(':client_id', $clientId, ParameterType::INTEGER);

        return $query;
    }
}
