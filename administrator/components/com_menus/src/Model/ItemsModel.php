<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Model;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Item List Model for Menus.
 *
 * @since  1.6
 */
class ItemsModel extends ListModel
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
                'id', 'a.id',
                'menutype', 'a.menutype', 'menutype_title',
                'title', 'a.title',
                'alias', 'a.alias',
                'published', 'a.published',
                'access', 'a.access', 'access_level',
                'language', 'a.language',
                'checked_out', 'a.checked_out',
                'checked_out_time', 'a.checked_out_time',
                'lft', 'a.lft',
                'rgt', 'a.rgt',
                'level', 'a.level',
                'path', 'a.path',
                'client_id', 'a.client_id',
                'home', 'a.home',
                'parent_id', 'a.parent_id',
                'publish_up', 'a.publish_up',
                'publish_down', 'a.publish_down',
                'a.ordering'
            ];

            if (Associations::isEnabled()) {
                $config['filter_fields'][] = 'association';
            }
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
    protected function populateState($ordering = 'a.lft', $direction = 'asc')
    {
        $app = Factory::getApplication();

        $forcedLanguage = $app->input->get('forcedLanguage', '', 'cmd');

        // Adjust the context to support modal layouts.
        if ($layout = $app->input->get('layout')) {
            $this->context .= '.' . $layout;
        }

        // Adjust the context to support forced languages.
        if ($forcedLanguage) {
            $this->context .= '.' . $forcedLanguage;
        }

        $search = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
        $this->setState('filter.search', $search);

        $published = $this->getUserStateFromRequest($this->context . '.published', 'filter_published', '');
        $this->setState('filter.published', $published);

        $access = $this->getUserStateFromRequest($this->context . '.filter.access', 'filter_access');
        $this->setState('filter.access', $access);

        $parentId = $this->getUserStateFromRequest($this->context . '.filter.parent_id', 'filter_parent_id');
        $this->setState('filter.parent_id', $parentId);

        $level = $this->getUserStateFromRequest($this->context . '.filter.level', 'filter_level');
        $this->setState('filter.level', $level);

        // Watch changes in client_id and menutype and keep sync whenever needed.
        $currentClientId = $app->getUserState($this->context . '.client_id', 0);
        $clientId        = $app->input->getInt('client_id', $currentClientId);

        // Load mod_menu.ini file when client is administrator
        if ($clientId == 1) {
            Factory::getLanguage()->load('mod_menu', JPATH_ADMINISTRATOR);
        }

        $currentMenuType = $app->getUserState($this->context . '.menutype', '');
        $menuType        = $app->input->getString('menutype', $currentMenuType);

        // If client_id changed clear menutype and reset pagination
        if ($clientId != $currentClientId) {
            $menuType = '';

            $app->input->set('limitstart', 0);
            $app->input->set('menutype', '');
        }

        // If menutype changed reset pagination.
        if ($menuType != $currentMenuType) {
            $app->input->set('limitstart', 0);
        }

        if (!$menuType) {
            $app->setUserState($this->context . '.menutype', '');
            $this->setState('menutypetitle', '');
            $this->setState('menutypeid', '');
        } elseif ($menuType == 'main') {
            // Special menu types, if selected explicitly, will be allowed as a filter
            // Adjust client_id to match the menutype. This is safe as client_id was not changed in this request.
            $app->input->set('client_id', 1);

            $app->setUserState($this->context . '.menutype', $menuType);
            $this->setState('menutypetitle', ucfirst($menuType));
            $this->setState('menutypeid', -1);
        } elseif ($cMenu = $this->getMenu($menuType, true)) {
            // Get the menutype object with appropriate checks.
            // Adjust client_id to match the menutype. This is safe as client_id was not changed in this request.
            $app->input->set('client_id', $cMenu->client_id);

            $app->setUserState($this->context . '.menutype', $menuType);
            $this->setState('menutypetitle', $cMenu->title);
            $this->setState('menutypeid', $cMenu->id);
        } else {
            // This menutype does not exist, leave client id unchanged but reset menutype and pagination
            $menuType = '';

            $app->input->set('limitstart', 0);
            $app->input->set('menutype', $menuType);

            $app->setUserState($this->context . '.menutype', $menuType);
            $this->setState('menutypetitle', '');
            $this->setState('menutypeid', '');
        }

        // Client id filter
        $clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
        $this->setState('filter.client_id', $clientId);

        // Use a different filter file when client is administrator
        if ($clientId == 1) {
            $this->filterFormName = 'filter_itemsadmin';
        }

        $this->setState('filter.menutype', $menuType);

        $language = $this->getUserStateFromRequest($this->context . '.filter.language', 'filter_language', '');
        $this->setState('filter.language', $language);

        // Component parameters.
        $params = ComponentHelper::getParams('com_menus');
        $this->setState('params', $params);

        // List state information.
        parent::populateState($ordering, $direction);

        // Force a language.
        if (!empty($forcedLanguage)) {
            $this->setState('filter.language', $forcedLanguage);
        }
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
        $id .= ':' . $this->getState('filter.access');
        $id .= ':' . $this->getState('filter.published');
        $id .= ':' . $this->getState('filter.language');
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.parent_id');
        $id .= ':' . $this->getState('filter.menutype');
        $id .= ':' . $this->getState('filter.client_id');

        return parent::getStoreId($id);
    }

    /**
     * Builds an SQL query to load the list data.
     *
     * @return  \Joomla\Database\DatabaseQuery    A query object.
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true);
        $user     = Factory::getUser();
        $clientId = (int) $this->getState('filter.client_id');

        // Select all fields from the table.
        $query->select(
            // We can't quote state values because they could contain expressions.
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.menutype'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.note'),
                    $db->quoteName('a.path'),
                    $db->quoteName('a.link'),
                    $db->quoteName('a.type'),
                    $db->quoteName('a.parent_id'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.component_id'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.checked_out_time'),
                    $db->quoteName('a.browserNav'),
                    $db->quoteName('a.access'),
                    $db->quoteName('a.img'),
                    $db->quoteName('a.template_style_id'),
                    $db->quoteName('a.params'),
                    $db->quoteName('a.lft'),
                    $db->quoteName('a.rgt'),
                    $db->quoteName('a.home'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.client_id'),
                    $db->quoteName('a.publish_up'),
                    $db->quoteName('a.publish_down'),
                ]
            )
        )
            ->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('l.sef', 'language_sef'),
                    $db->quoteName('u.name', 'editor'),
                    $db->quoteName('c.element', 'componentname'),
                    $db->quoteName('ag.title', 'access_level'),
                    $db->quoteName('mt.id', 'menutype_id'),
                    $db->quoteName('mt.title', 'menutype_title'),
                    $db->quoteName('e.enabled'),
                    $db->quoteName('e.name'),
                    'CASE WHEN ' . $db->quoteName('a.type') . ' = ' . $db->quote('component')
                    . ' THEN ' . $db->quoteName('a.published') . ' +2 * (' . $db->quoteName('e.enabled') . ' -1)'
                    . ' ELSE ' . $db->quoteName('a.published') . ' END AS ' . $db->quoteName('published'),
                ]
            )
            ->from($db->quoteName('#__menu', 'a'));

        // Join over the language
        $query->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));

        // Join over the users.
        $query->join('LEFT', $db->quoteName('#__users', 'u'), $db->quoteName('u.id') . ' = ' . $db->quoteName('a.checked_out'));

        // Join over components
        $query->join('LEFT', $db->quoteName('#__extensions', 'c'), $db->quoteName('c.extension_id') . ' = ' . $db->quoteName('a.component_id'));

        // Join over the asset groups.
        $query->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'));

        // Join over the menu types.
        $query->join('LEFT', $db->quoteName('#__menu_types', 'mt'), $db->quoteName('mt.menutype') . ' = ' . $db->quoteName('a.menutype'));

        // Join over the extensions
        $query->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('a.component_id'));

        // Join over the associations.
        if (Associations::isEnabled()) {
            $subQuery = $db->getQuery(true)
                ->select('COUNT(' . $db->quoteName('asso1.id') . ') > 1')
                ->from($db->quoteName('#__associations', 'asso1'))
                ->join('INNER', $db->quoteName('#__associations', 'asso2'), $db->quoteName('asso1.key') . ' = ' . $db->quoteName('asso2.key'))
                ->where(
                    [
                        $db->quoteName('asso1.id') . ' = ' . $db->quoteName('a.id'),
                        $db->quoteName('asso1.context') . ' = ' . $db->quote('com_menus.item'),
                    ]
                );

            $query->select('(' . $subQuery . ') AS ' . $db->quoteName('association'));
        }

        // Exclude the root category.
        $query->where(
            [
                $db->quoteName('a.id') . ' > 1',
                $db->quoteName('a.client_id') . ' = :clientId',
            ]
        )
            ->bind(':clientId', $clientId, ParameterType::INTEGER);

        // Filter on the published state.
        $published = $this->getState('filter.published');

        if (is_numeric($published)) {
            $published = (int) $published;
            $query->where($db->quoteName('a.published') . ' = :published')
                ->bind(':published', $published, ParameterType::INTEGER);
        } elseif ($published === '') {
            $query->where($db->quoteName('a.published') . ' IN (0, 1)');
        }

        // Filter by search in title, alias or id
        if ($search = trim($this->getState('filter.search', ''))) {
            if (stripos($search, 'id:') === 0) {
                $search = (int) substr($search, 3);
                $query->where($db->quoteName('a.id') . ' = :search')
                    ->bind(':search', $search, ParameterType::INTEGER);
            } elseif (stripos($search, 'link:') === 0) {
                if ($search = str_replace(' ', '%', trim(substr($search, 5)))) {
                    $query->where($db->quoteName('a.link') . ' LIKE :search')
                        ->bind(':search', $search);
                }
            } else {
                $search = '%' . str_replace(' ', '%', trim($search)) . '%';
                $query->extendWhere(
                    'AND',
                    [
                        $db->quoteName('a.title') . ' LIKE :search1',
                        $db->quoteName('a.alias') . ' LIKE :search2',
                        $db->quoteName('a.note') . ' LIKE :search3',
                    ],
                    'OR'
                )
                    ->bind([':search1', ':search2', ':search3'], $search);
            }
        }

        // Filter the items over the parent id if set.
        $parentId = (int) $this->getState('filter.parent_id');
        $level    = (int) $this->getState('filter.level');

        if ($parentId) {
            // Create a subquery for the sub-items list
            $subQuery = $db->getQuery(true)
                ->select($db->quoteName('sub.id'))
                ->from($db->quoteName('#__menu', 'sub'))
                ->join(
                    'INNER',
                    $db->quoteName('#__menu', 'this'),
                    $db->quoteName('sub.lft') . ' > ' . $db->quoteName('this.lft')
                    . ' AND ' . $db->quoteName('sub.rgt') . ' < ' . $db->quoteName('this.rgt')
                )
                ->where($db->quoteName('this.id') . ' = :parentId1');

            if ($level) {
                $subQuery->where($db->quoteName('sub.level') . ' <= ' . $db->quoteName('this.level') . ' + :level - 1');
                $query->bind(':level', $level, ParameterType::INTEGER);
            }

            // Add the subquery to the main query
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.parent_id') . ' = :parentId2',
                    $db->quoteName('a.parent_id') . ' IN (' . (string) $subQuery . ')',
                ],
                'OR'
            )
                ->bind([':parentId1', ':parentId2'], $parentId, ParameterType::INTEGER);
        } elseif ($level) {
            // Filter on the level.
            $query->where($db->quoteName('a.level') . ' <= :level')
                ->bind(':level', $level, ParameterType::INTEGER);
        }

        // Filter the items over the menu id if set.
        $menuType = $this->getState('filter.menutype');

        // A value "" means all
        if ($menuType == '') {
            // Load all menu types we have manage access
            $query2 = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('id'),
                        $db->quoteName('menutype'),
                    ]
                )
                ->from($db->quoteName('#__menu_types'))
                ->where($db->quoteName('client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER)
                ->order($db->quoteName('title'));

            // Show protected items on explicit filter only
            $query->where($db->quoteName('a.menutype') . ' != ' . $db->quote('main'));

            $menuTypes = $db->setQuery($query2)->loadObjectList();

            if ($menuTypes) {
                $types = [];

                foreach ($menuTypes as $type) {
                    if ($user->authorise('core.manage', 'com_menus.menu.' . (int) $type->id)) {
                        $types[] = $type->menutype;
                    }
                }

                if ($types) {
                    $query->whereIn($db->quoteName('a.menutype'), $types);
                } else {
                    $query->where(0);
                }
            }
        } elseif (strlen($menuType)) {
            // Default behavior => load all items from a specific menu
            $query->where($db->quoteName('a.menutype') . ' = :menuType')
                ->bind(':menuType', $menuType);
        } else {
            // Empty menu type => error
            $query->where('1 != 1');
        }

        // Filter on the access level.
        if ($access = (int) $this->getState('filter.access')) {
            $query->where($db->quoteName('a.access') . ' = :access')
                ->bind(':access', $access, ParameterType::INTEGER);
        }

        // Implement View Level Access
        if (!$user->authorise('core.admin')) {
            if ($groups = $user->getAuthorisedViewLevels()) {
                $query->whereIn($db->quoteName('a.access'), $groups);
            }
        }

        // Filter on the language.
        if ($language = $this->getState('filter.language')) {
            $query->where($db->quoteName('a.language') . ' = :language')
                ->bind(':language', $language);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.lft')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
    }

    /**
     * Method to allow derived classes to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import (defaults to "content").
     *
     * @return  void
     *
     * @since   3.2
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $name = $form->getName();

        if ($name == 'com_menus.items.filter') {
            $clientId = $this->getState('filter.client_id');
            $form->setFieldAttribute('menutype', 'clientid', $clientId);
        } elseif (false !== strpos($name, 'com_menus.items.modal.')) {
            $form->removeField('client_id');

            $clientId = $this->getState('filter.client_id');
            $form->setFieldAttribute('menutype', 'clientid', $clientId);
        }
    }

    /**
     * Get the client id for a menu
     *
     * @param   string   $menuType  The menutype identifier for the menu
     * @param   boolean  $check     Flag whether to perform check against ACL as well as existence
     *
     * @return  integer
     *
     * @since   3.7.0
     */
    protected function getMenu($menuType, $check = false)
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('a') . '.*')
            ->from($db->quoteName('#__menu_types', 'a'))
            ->where($db->quoteName('menutype') . ' = :menuType')
            ->bind(':menuType', $menuType);

        $cMenu = $db->setQuery($query)->loadObject();

        if ($check) {
            // Check if menu type exists.
            if (!$cMenu) {
                Log::add(Text::_('COM_MENUS_ERROR_MENUTYPE_NOT_FOUND'), Log::ERROR, 'jerror');

                return false;
            } elseif (!Factory::getUser()->authorise('core.manage', 'com_menus.menu.' . $cMenu->id)) {
                // Check if menu type is valid against ACL.
                Log::add(Text::_('JERROR_ALERTNOAUTHOR'), Log::ERROR, 'jerror');

                return false;
            }
        }

        return $cMenu;
    }

    /**
     * Method to get an array of data items.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   3.0.1
     */
    public function getItems()
    {
        $store = $this->getStoreId();

        if (!isset($this->cache[$store])) {
            $items  = parent::getItems();
            $lang   = Factory::getLanguage();
            $client = $this->state->get('filter.client_id');

            if ($items) {
                foreach ($items as $item) {
                    if ($extension = $item->componentname) {
                        $lang->load("$extension.sys", JPATH_ADMINISTRATOR)
                        || $lang->load("$extension.sys", JPATH_ADMINISTRATOR . '/components/' . $extension);
                    }

                    // Translate component name
                    if ($client === 1) {
                        $item->title = Text::_($item->title);
                    }
                }
            }

            $this->cache[$store] = $items;
        }

        return $this->cache[$store];
    }
}
