<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Model;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\Database\ParameterType;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu List Model for Menus.
 *
 * @since  1.6
 */
class MenusModel extends ListModel
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
                'title', 'a.title',
                'menutype', 'a.menutype',
                'client_id', 'a.client_id',
            ];
        }

        parent::__construct($config, $factory);
    }

    /**
     * Overrides the getItems method to attach additional metrics to the list.
     *
     * @return  mixed  An array of data items on success, false on failure.
     *
     * @since   1.6.1
     */
    public function getItems()
    {
        // Get a storage key.
        $store = $this->getStoreId('getItems');

        // Try to load the data from internal storage.
        if (!empty($this->cache[$store])) {
            return $this->cache[$store];
        }

        // Load the list items.
        $items = parent::getItems();

        // If empty or an error, just return.
        if (empty($items)) {
            return [];
        }

        // Getting the following metric by joins is WAY TOO SLOW.
        // Faster to do three queries for very large menu trees.

        // Get the menu types of menus in the list.
        $db = $this->getDatabase();
        $menuTypes = array_column((array) $items, 'menutype');

        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('m.menutype'),
                    'COUNT(DISTINCT ' . $db->quoteName('m.id') . ') AS ' . $db->quoteName('count_published'),
                ]
            )
            ->from($db->quoteName('#__menu', 'm'))
            ->where($db->quoteName('m.published') . ' = :published')
            ->whereIn($db->quoteName('m.menutype'), $menuTypes, ParameterType::STRING)
            ->group($db->quoteName('m.menutype'))
            ->bind(':published', $published, ParameterType::INTEGER);

        $db->setQuery($query);

        // Get the published menu counts.
        try {
            $published      = 1;
            $countPublished = $db->loadAssocList('menutype', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get the unpublished menu counts.
        try {
            $published        = 0;
            $countUnpublished = $db->loadAssocList('menutype', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Get the trashed menu counts.
        try {
            $published    = -2;
            $countTrashed = $db->loadAssocList('menutype', 'count_published');
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Inject the values back into the array.
        foreach ($items as $item) {
            $item->count_published   = $countPublished[$item->menutype] ?? 0;
            $item->count_unpublished = $countUnpublished[$item->menutype] ?? 0;
            $item->count_trashed     = $countTrashed[$item->menutype] ?? 0;
        }

        // Add the items to the internal cache.
        $this->cache[$store] = $items;

        return $this->cache[$store];
    }

    /**
     * Method to build an SQL query to load the list data.
     *
     * @return  string  An SQL query
     *
     * @since   1.6
     */
    protected function getListQuery()
    {
        // Create a new query object.
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true);
        $clientId = (int) $this->getState('client_id');

        // Select all fields from the table.
        $query->select(
            $this->getState(
                'list.select',
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.menutype'),
                    $db->quoteName('a.title'),
                    $db->quoteName('a.description'),
                    $db->quoteName('a.client_id'),
                ]
            )
        )
            ->from($db->quoteName('#__menu_types', 'a'))
            ->where(
                [
                    $db->quoteName('a.id') . ' > 0',
                    $db->quoteName('a.client_id') . ' = :clientId',
                ]
            )
            ->bind(':clientId', $clientId, ParameterType::INTEGER);

        // Filter by search in title or menutype
        if ($search = trim($this->getState('filter.search', ''))) {
            $search = '%' . str_replace(' ', '%', $search) . '%';
            $query->extendWhere(
                'AND',
                [
                    $db->quoteName('a.title') . ' LIKE :search1' ,
                    $db->quoteName('a.menutype') . ' LIKE :search2',
                ],
                'OR'
            )
                ->bind([':search1', ':search2'], $search);
        }

        // Add the list ordering clause.
        $query->order($db->escape($this->getState('list.ordering', 'a.id')) . ' ' . $db->escape($this->getState('list.direction', 'ASC')));

        return $query;
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
    protected function populateState($ordering = 'a.title', $direction = 'asc')
    {
        $search   = $this->getUserStateFromRequest($this->context . '.search', 'filter_search');
        $this->setState('filter.search', $search);

        $clientId = (int) $this->getUserStateFromRequest($this->context . '.client_id', 'client_id', 0, 'int');
        $this->setState('client_id', $clientId);

        // List state information.
        parent::populateState($ordering, $direction);
    }

    /**
     * Gets the extension id of the core mod_menu module.
     *
     * @return  integer
     *
     * @since   2.5
     */
    public function getModMenuId()
    {
        $clientId = (int) $this->getState('client_id');
        $db       = $this->getDatabase();
        $query    = $db->getQuery(true)
            ->select($db->quoteName('e.extension_id'))
            ->from($db->quoteName('#__extensions', 'e'))
            ->where(
                [
                    $db->quoteName('e.type') . ' = ' . $db->quote('module'),
                    $db->quoteName('e.element') . ' = ' . $db->quote('mod_menu'),
                    $db->quoteName('e.client_id') . ' = :clientId',
                ]
            )
            ->bind(':clientId', $clientId, ParameterType::INTEGER);
        $db->setQuery($query);

        return $db->loadResult();
    }

    /**
     * Gets a list of all mod_mainmenu modules and collates them by menutype
     *
     * @return  array
     *
     * @since   1.6
     */
    public function &getModules()
    {
        $model = $this->bootComponent('com_menus')
            ->getMVCFactory()->createModel('Menu', 'Administrator', ['ignore_request' => true]);
        $result = $model->getModules();

        return $result;
    }

    /**
     * Returns the missing module languages.
     *
     * @return  array
     *
     * @since   4.2.0
     */
    public function getMissingModuleLanguages(): array
    {
        // Check custom administrator menu modules
        if (!ModuleHelper::isAdminMultilang()) {
            return [];
        }

        $languages = LanguageHelper::getInstalledLanguages(1, true);
        $langCodes = [];

        foreach ($languages as $language) {
            if (isset($language->metadata['nativeName'])) {
                $languageName = $language->metadata['nativeName'];
            } else {
                $languageName = $language->metadata['name'];
            }

            $langCodes[$language->metadata['tag']] = $languageName;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        $query->select($db->quoteName('m.language'))
            ->from($db->quoteName('#__modules', 'm'))
            ->where(
                [
                    $db->quoteName('m.module') . ' = ' . $db->quote('mod_menu'),
                    $db->quoteName('m.published') . ' = 1',
                    $db->quoteName('m.client_id') . ' = 1',
                ]
            )
            ->group($db->quoteName('m.language'));

        $mLanguages = $db->setQuery($query)->loadColumn();

        // Check if we have a mod_menu module set to All languages or a mod_menu module for each admin language.
        if (!in_array('*', $mLanguages) && count($langMissing = array_diff(array_keys($langCodes), $mLanguages))) {
            return array_intersect_key($langCodes, array_flip($langMissing));
        }

        return [];
    }
}
