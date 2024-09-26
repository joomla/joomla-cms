<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Model;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\Component\Menus\Administrator\Helper\MenusHelper;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;
use Joomla\String\StringHelper;
use Joomla\Utilities\ArrayHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menu Item Model for Menus.
 *
 * @since  1.6
 */
class ItemModel extends AdminModel
{
    /**
     * The type alias for this content type.
     *
     * @var    string
     * @since  3.4
     */
    public $typeAlias = 'com_menus.item';

    /**
     * The context used for the associations table
     *
     * @var    string
     * @since  3.4.4
     */
    protected $associationsContext = 'com_menus.item';

    /**
     * @var    string  The prefix to use with controller messages.
     * @since  1.6
     */
    protected $text_prefix = 'COM_MENUS_ITEM';

    /**
     * @var    string  The help screen key for the menu item.
     * @since  1.6
     */
    protected $helpKey = 'Menu_Item:_New_Item';

    /**
     * @var    string  The help screen base URL for the menu item.
     * @since  1.6
     */
    protected $helpURL;

    /**
     * @var    boolean  True to use local lookup for the help screen.
     * @since  1.6
     */
    protected $helpLocal = false;

    /**
     * Batch copy/move command. If set to false,
     * the batch copy/move command is not supported
     *
     * @var   string
     */
    protected $batch_copymove = 'menu_id';

    /**
     * Allowed batch commands
     *
     * @var   array
     */
    protected $batch_commands = [
        'assetgroup_id' => 'batchAccess',
        'language_id'   => 'batchLanguage',
    ];

    /**
     * Method to test whether a record can be deleted.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to delete the record. Defaults to the permission set in the component.
     *
     * @since   1.6
     */
    protected function canDelete($record)
    {
        if (empty($record->id) || $record->published != -2) {
            return false;
        }

        $menuTypeId = 0;

        if (!empty($record->menutype)) {
            $menuTypeId = $this->getMenuTypeId($record->menutype);
        }

        return $this->getCurrentUser()->authorise('core.delete', 'com_menus.menu.' . (int) $menuTypeId);
    }

    /**
     * Method to test whether the state of a record can be edited.
     *
     * @param   object  $record  A record object.
     *
     * @return  boolean  True if allowed to change the state of the record. Defaults to the permission for the component.
     *
     * @since   3.6
     */
    protected function canEditState($record)
    {
        $menuTypeId = !empty($record->menutype) ? $this->getMenuTypeId($record->menutype) : 0;
        $assetKey   = $menuTypeId ? 'com_menus.menu.' . (int) $menuTypeId : 'com_menus';

        return $this->getCurrentUser()->authorise('core.edit.state', $assetKey);
    }

    /**
     * Batch copy menu items to a new menu or parent.
     *
     * @param   integer  $value     The new menu or sub-item.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  mixed  An array of new IDs on success, boolean false on failure.
     *
     * @since   1.6
     */
    protected function batchCopy($value, $pks, $contexts)
    {
        // $value comes as {menutype}.{parent_id}
        $parts    = explode('.', $value);
        $menuType = $parts[0];
        $parentId = ArrayHelper::getValue($parts, 1, 0, 'int');

        $table  = $this->getTable();
        $db     = $this->getDatabase();
        $query  = $db->getQuery(true);
        $newIds = [];

        // Check that the parent exists
        if ($parentId) {
            if (!$table->load($parentId)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
        }

        // If the parent is 0, set it to the ID of the root item in the tree
        if (empty($parentId)) {
            if (!$parentId = $table->getRootId()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Check that user has create permission for menus
        $user = $this->getCurrentUser();

        $menuTypeId = (int) $this->getMenuTypeId($menuType);

        if (!$user->authorise('core.create', 'com_menus.menu.' . $menuTypeId)) {
            $this->setError(Text::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_CREATE'));

            return false;
        }

        // We need to log the parent ID
        $parents = [];

        // Calculate the emergency stop count as a precaution against a runaway loop bug
        $query->select('COUNT(' . $db->quoteName('id') . ')')
            ->from($db->quoteName('#__menu'));
        $db->setQuery($query);

        try {
            $count = $db->loadResult();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        // Parent exists so let's proceed
        while (!empty($pks) && $count > 0) {
            // Pop the first id off the stack
            $pk = array_shift($pks);

            $table->reset();

            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Copy is a bit tricky, because we also need to copy the children
            $query = $db->getQuery(true)
                ->select($db->quoteName('id'))
                ->from($db->quoteName('#__menu'))
                ->where(
                    [
                        $db->quoteName('lft') . ' > :lft',
                        $db->quoteName('rgt') . ' < :rgt',
                    ]
                )
                ->bind(':lft', $table->lft, ParameterType::INTEGER)
                ->bind(':rgt', $table->rgt, ParameterType::INTEGER);
            $db->setQuery($query);
            $childIds = $db->loadColumn();

            // Add child IDs to the array only if they aren't already there.
            foreach ($childIds as $childId) {
                if (!in_array($childId, $pks)) {
                    $pks[] = $childId;
                }
            }

            // Make a copy of the old ID and Parent ID
            $oldId       = $table->id;
            $oldParentId = $table->parent_id;

            // Reset the id because we are making a copy.
            $table->id = 0;

            // If we a copying children, the Old ID will turn up in the parents list
            // otherwise it's a new top level item
            $table->parent_id = isset($parents[$oldParentId]) ? $parents[$oldParentId] : $parentId;
            $table->menutype  = $menuType;

            // Set the new location in the tree for the node.
            $table->setLocation($table->parent_id, 'last-child');

            // @todo: Deal with ordering?
            // $table->ordering = 1;
            $table->level = null;
            $table->lft   = null;
            $table->rgt   = null;
            $table->home  = 0;

            // Alter the title & alias
            list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
            $table->title        = $title;
            $table->alias        = $alias;

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Get the new item ID
            $newId = $table->get('id');

            // Add the new ID to the array
            $newIds[$pk] = $newId;

            // Now we log the old 'parent' to the new 'parent'
            $parents[$oldId] = $table->id;
            $count--;
        }

        // Rebuild the hierarchy.
        if (!$table->rebuild()) {
            $this->setError($table->getError());

            return false;
        }

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return $newIds;
    }

    /**
     * Batch move menu items to a new menu or parent.
     *
     * @param   integer  $value     The new menu or sub-item.
     * @param   array    $pks       An array of row IDs.
     * @param   array    $contexts  An array of item contexts.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    protected function batchMove($value, $pks, $contexts)
    {
        // $value comes as {menutype}.{parent_id}
        $parts    = explode('.', $value);
        $menuType = $parts[0];
        $parentId = ArrayHelper::getValue($parts, 1, 0, 'int');

        $table = $this->getTable();
        $db    = $this->getDatabase();

        // Check that the parent exists.
        if ($parentId) {
            if (!$table->load($parentId)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Non-fatal error
                    $this->setError(Text::_('JGLOBAL_BATCH_MOVE_PARENT_NOT_FOUND'));
                    $parentId = 0;
                }
            }
        }

        // Check that user has create and edit permission for menus
        $user = $this->getCurrentUser();

        $menuTypeId = (int) $this->getMenuTypeId($menuType);

        if (!$user->authorise('core.create', 'com_menus.menu.' . $menuTypeId)) {
            $this->setError(Text::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_CREATE'));

            return false;
        }

        if (!$user->authorise('core.edit', 'com_menus.menu.' . $menuTypeId)) {
            $this->setError(Text::_('COM_MENUS_BATCH_MENU_ITEM_CANNOT_EDIT'));

            return false;
        }

        // We are going to store all the children and just moved the menutype
        $children = [];

        // Parent exists so let's proceed
        foreach ($pks as $pk) {
            // Check that the row actually exists
            if (!$table->load($pk)) {
                if ($error = $table->getError()) {
                    // Fatal error
                    $this->setError($error);

                    return false;
                } else {
                    // Not fatal error
                    $this->setError(Text::sprintf('JGLOBAL_BATCH_MOVE_ROW_NOT_FOUND', $pk));
                    continue;
                }
            }

            // Set the new location in the tree for the node.
            $table->setLocation($parentId, 'last-child');

            // Set the new Parent Id
            $table->parent_id = $parentId;

            // Check if we are moving to a different menu
            if ($menuType != $table->menutype) {
                // Add the child node ids to the children array.
                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('lft') . ' BETWEEN :lft AND :rgt')
                    ->bind(':lft', $table->lft, ParameterType::INTEGER)
                    ->bind(':rgt', $table->rgt, ParameterType::INTEGER);
                $db->setQuery($query);
                $children = array_merge($children, (array) $db->loadColumn());
            }

            // Check the row.
            if (!$table->check()) {
                $this->setError($table->getError());

                return false;
            }

            // Store the row.
            if (!$table->store()) {
                $this->setError($table->getError());

                return false;
            }

            // Rebuild the tree path.
            if (!$table->rebuildPath()) {
                $this->setError($table->getError());

                return false;
            }
        }

        // Process the child rows
        if (!empty($children)) {
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            $children = ArrayHelper::toInteger($children);

            // Update the menutype field in all nodes where necessary.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__menu'))
                ->set($db->quoteName('menutype') . ' = :menuType')
                ->whereIn($db->quoteName('id'), $children)
                ->bind(':menuType', $menuType);

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to check if you can save a record.
     *
     * @param   array   $data  An array of input data.
     * @param   string  $key   The name of the key for the primary key.
     *
     * @return  boolean
     *
     * @since   1.6
     */
    protected function canSave($data = [], $key = 'id')
    {
        return $this->getCurrentUser()->authorise('core.edit', $this->option);
    }

    /**
     * Method to get the row form.
     *
     * @param   array    $data      Data for the form.
     * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
     *
     * @return  mixed  A Form object on success, false on failure
     *
     * @since   1.6
     */
    public function getForm($data = [], $loadData = true)
    {
        // The folder and element vars are passed when saving the form.
        if (empty($data)) {
            $item = $this->getItem();

            // The type should already be set.
            $this->setState('item.link', $item->link);
        } else {
            $this->setState('item.link', ArrayHelper::getValue($data, 'link'));
            $this->setState('item.type', ArrayHelper::getValue($data, 'type'));
        }

        $clientId = $this->getState('item.client_id');

        // Get the form.
        if ($clientId == 1) {
            $form = $this->loadForm('com_menus.item.admin', 'itemadmin', ['control' => 'jform', 'load_data' => $loadData], true);
        } else {
            $form = $this->loadForm('com_menus.item', 'item', ['control' => 'jform', 'load_data' => $loadData], true);
        }

        if (empty($form)) {
            return false;
        }

        if ($loadData) {
            $data = $this->loadFormData();
        }

        // Modify the form based on access controls.
        if (!$this->canEditState((object) $data)) {
            // Disable fields for display.
            $form->setFieldAttribute('menuordering', 'disabled', 'true');
            $form->setFieldAttribute('published', 'disabled', 'true');

            // Disable fields while saving.
            // The controller has already verified this is an article you can edit.
            $form->setFieldAttribute('menuordering', 'filter', 'unset');
            $form->setFieldAttribute('published', 'filter', 'unset');
        }

        // Filter available menus
        $action = $this->getState('item.id') > 0 ? 'edit' : 'create';

        $form->setFieldAttribute('menutype', 'accesstype', $action);
        $form->setFieldAttribute('type', 'clientid', $clientId);

        return $form;
    }

    /**
     * Method to get the data that should be injected in the form.
     *
     * @return  mixed  The data for the form.
     *
     * @since   1.6
     */
    protected function loadFormData()
    {
        // Check the session for previously entered form data, providing it has an ID and it is the same.
        $itemData = (array) $this->getItem();

        // When a new item is requested, unset the access as it will be set later from the filter
        if (empty($itemData['id'])) {
            unset($itemData['access']);
        }

        $sessionData = (array) Factory::getApplication()->getUserState('com_menus.edit.item.data', []);

        // Only merge if there is a session and itemId or itemid is null.
        if (
            isset($sessionData['id']) && isset($itemData['id']) && $sessionData['id'] === $itemData['id']
            || is_null($itemData['id'])
        ) {
            $data = array_merge($itemData, $sessionData);
        } else {
            $data = $itemData;
        }

        // For a new menu item, pre-select some filters (Status, Language, Access) in edit form if those have been selected in Menu Manager
        if (empty($data['id'])) {
            // Get selected fields
            $filters           = Factory::getApplication()->getUserState('com_menus.items.filter');
            $data['parent_id'] = $data['parent_id'] ?? ($filters['parent_id'] ?? null);
            $data['published'] = $data['published'] ?? ($filters['published'] ?? null);
            $data['language']  = $data['language'] ?? ($filters['language'] ?? null);
            $data['access']    = $data['access'] ?? ($filters['access'] ?? Factory::getApplication()->get('access'));
        }

        if (isset($data['menutype']) && !$this->getState('item.menutypeid')) {
            $menuTypeId = (int) $this->getMenuTypeId($data['menutype']);

            $this->setState('item.menutypeid', $menuTypeId);
        }

        $data = (object) $data;

        $this->preprocessData('com_menus.item', $data);

        return $data;
    }

    /**
     * Get the necessary data to load an item help screen.
     *
     * @return  object  An object with key, url, and local properties for loading the item help screen.
     *
     * @since   1.6
     */
    public function getHelp()
    {
        return (object) ['key' => $this->helpKey, 'url' => $this->helpURL, 'local' => $this->helpLocal];
    }

    /**
     * Method to get a menu item.
     *
     * @param   integer  $pk  An optional id of the object to get, otherwise the id from the model state is used.
     *
     * @return  mixed  Menu item data object on success, false on failure.
     *
     * @since   1.6
     */
    public function getItem($pk = null)
    {
        $pk = (!empty($pk)) ? $pk : (int) $this->getState('item.id');

        // Get a level row instance.
        $table = $this->getTable();

        // Attempt to load the row.
        $table->load($pk);

        // Check for a table object error.
        if ($error = $table->getError()) {
            $this->setError($error);

            return false;
        }

        // Prime required properties.

        if ($type = $this->getState('item.type')) {
            $table->type = $type;
        }

        if (empty($table->id)) {
            $table->parent_id = $this->getState('item.parent_id');
            $table->menutype  = $this->getState('item.menutype');
            $table->client_id = $this->getState('item.client_id');
            $table->params    = '{}';
        }

        // If the link has been set in the state, possibly changing link type.
        if ($link = $this->getState('item.link')) {
            // Check if we are changing away from the actual link type.
            if (MenusHelper::getLinkKey($table->link) !== MenusHelper::getLinkKey($link) && (int) $table->id === (int) $this->getState('item.id')) {
                $table->link = $link;
            }
        }

        switch ($table->type) {
            case 'alias':
            case 'url':
                $table->component_id = 0;
                $args                = [];

                if ($table->link) {
                    $q = parse_url($table->link, PHP_URL_QUERY);

                    if ($q) {
                        parse_str($q, $args);
                    }
                }

                break;

            case 'separator':
            case 'heading':
            case 'container':
                $table->link         = '';
                $table->component_id = 0;
                break;

            case 'component':
            default:
                // Enforce a valid type.
                $table->type = 'component';

                // Ensure the integrity of the component_id field is maintained, particularly when changing the menu item type.
                $args = [];

                if ($table->link) {
                    $q = parse_url($table->link, PHP_URL_QUERY);

                    if ($q) {
                        parse_str($q, $args);
                    }
                }

                if (isset($args['option'])) {
                    // Load the language file for the component.
                    $lang = Factory::getLanguage();
                    $lang->load($args['option'], JPATH_ADMINISTRATOR)
                        || $lang->load($args['option'], JPATH_ADMINISTRATOR . '/components/' . $args['option']);

                    // Determine the component id.
                    $component = ComponentHelper::getComponent($args['option']);

                    if (isset($component->id)) {
                        $table->component_id = $component->id;
                    }
                }
                break;
        }

        // We have a valid type, inject it into the state for forms to use.
        $this->setState('item.type', $table->type);

        // Convert to the \Joomla\CMS\Object\CMSObject before adding the params.
        $properties = $table->getProperties(1);
        $result     = ArrayHelper::toObject($properties);

        // Convert the params field to an array.
        $registry       = new Registry($table->params);
        $result->params = $registry->toArray();

        // Merge the request arguments in to the params for a component.
        if ($table->type == 'component') {
            // Note that all request arguments become reserved parameter names.
            $result->request = $args;
            $result->params  = array_merge($result->params, $args);

            // Special case for the Login menu item.
            // Display the login or logout redirect URL fields if not empty
            if ($table->link == 'index.php?option=com_users&view=login') {
                if (!empty($result->params['login_redirect_url'])) {
                    $result->params['loginredirectchoice'] = '0';
                }

                if (!empty($result->params['logout_redirect_url'])) {
                    $result->params['logoutredirectchoice'] = '0';
                }
            }
        }

        if ($table->type == 'alias') {
            // Note that all request arguments become reserved parameter names.
            $result->params = array_merge($result->params, $args);
        }

        if ($table->type == 'url') {
            // Note that all request arguments become reserved parameter names.
            $result->params = array_merge($result->params, $args);
        }

        // Load associated menu items, only supported for frontend for now
        if ($this->getState('item.client_id') == 0 && Associations::isEnabled()) {
            if ($pk != null) {
                $result->associations = MenusHelper::getAssociations($pk);
            } else {
                $result->associations = [];
            }
        }

        $result->menuordering = $pk;

        return $result;
    }

    /**
     * Get the list of modules not in trash.
     *
     * @return  mixed  An array of module records (id, title, position), or false on error.
     *
     * @since   1.6
     */
    public function getModules()
    {
        $clientId = (int) $this->getState('item.client_id');
        $id       = (int) $this->getState('item.id');

        // Currently any setting that affects target page for a backend menu is not supported, hence load no modules.
        if ($clientId == 1) {
            return false;
        }

        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        /**
         * Join on the module-to-menu mapping table.
         * We are only interested if the module is displayed on ALL or THIS menu item (or the inverse ID number).
         * sqlsrv changes for modulelink to menu manager
         */
        $query->select(
            [
                $db->quoteName('a.id'),
                $db->quoteName('a.title'),
                $db->quoteName('a.position'),
                $db->quoteName('a.published'),
                $db->quoteName('map.menuid'),
            ]
        )
            ->from($db->quoteName('#__modules', 'a'))
            ->join(
                'LEFT',
                $db->quoteName('#__modules_menu', 'map'),
                $db->quoteName('map.moduleid') . ' = ' . $db->quoteName('a.id')
                    . ' AND ' . $db->quoteName('map.menuid') . ' IN (' . implode(',', $query->bindArray([0, $id, -$id])) . ')'
            );

        $subQuery = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules_menu'))
            ->where(
                [
                    $db->quoteName('moduleid') . ' = ' . $db->quoteName('a.id'),
                    $db->quoteName('menuid') . ' < 0',
                ]
            );

        $query->select('(' . $subQuery . ') AS ' . $db->quoteName('except'));

        // Join on the asset groups table.
        $query->select($db->quoteName('ag.title', 'access_title'))
            ->join('LEFT', $db->quoteName('#__viewlevels', 'ag'), $db->quoteName('ag.id') . ' = ' . $db->quoteName('a.access'))
            ->where(
                [
                    $db->quoteName('a.published') . ' >= 0',
                    $db->quoteName('a.client_id') . ' = :clientId',
                ]
            )
            ->bind(':clientId', $clientId, ParameterType::INTEGER)
            ->order(
                [
                    $db->quoteName('a.position'),
                    $db->quoteName('a.ordering'),
                ]
            );

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $result;
    }

    /**
     * Get the list of all view levels
     *
     * @return  \stdClass[]|boolean  An array of all view levels (id, title).
     *
     * @since   3.4
     */
    public function getViewLevels()
    {
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);

        // Get all the available view levels
        $query->select($db->quoteName('id'))
            ->select($db->quoteName('title'))
            ->from($db->quoteName('#__viewlevels'))
            ->order($db->quoteName('id'));

        $db->setQuery($query);

        try {
            $result = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            $this->setError($e->getMessage());

            return false;
        }

        return $result;
    }

    /**
     * Returns a Table object, always creating it
     *
     * @param   string  $type    The table type to instantiate.
     * @param   string  $prefix  A prefix for the table class name. Optional.
     * @param   array   $config  Configuration array for model. Optional.
     *
     * @return  \Joomla\Cms\Table\Table|\Joomla\Cms\Table\Nested  A database object.
     *
     * @since   1.6
     */
    public function getTable($type = 'Menu', $prefix = 'Administrator', $config = [])
    {
        return parent::getTable($type, $prefix, $config);
    }

    /**
     * A protected method to get the where clause for the reorder.
     * This ensures that the row will be moved relative to a row with the same menutype.
     *
     * @param   \Joomla\CMS\Table\Menu  $table
     *
     * @return  array  An array of conditions to add to add to ordering queries.
     *
     * @since   1.6
     */
    protected function getReorderConditions($table)
    {
        $db = $this->getDatabase();

        return [
            $db->quoteName('menutype') . ' = ' . $db->quote($table->menutype),
        ];
    }

    /**
     * Auto-populate the model state.
     *
     * Note. Calling getState in this method will result in recursion.
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function populateState()
    {
        $app = Factory::getApplication();

        // Load the User state.
        $pk = $app->getInput()->getInt('id');
        $this->setState('item.id', $pk);

        if (!$app->isClient('api')) {
            $parentId = $app->getUserState('com_menus.edit.item.parent_id');
            $menuType = $app->getUserStateFromRequest('com_menus.items.menutype', 'menutype', '', 'string');
        } else {
            $parentId = null;
            $menuType = $app->getInput()->get('com_menus.items.menutype');
        }

        if (!$parentId) {
            $parentId = $app->getInput()->getInt('parent_id');
        }

        $this->setState('item.parent_id', $parentId);

        // If we have a menutype we take client_id from there, unless forced otherwise
        if ($menuType) {
            $menuTypeObj = $this->getMenuType($menuType);

            // An invalid menutype will be handled as clientId = 0 and menuType = ''
            $menuType   = (string) $menuTypeObj->menutype;
            $menuTypeId = (int) $menuTypeObj->client_id;
            $clientId   = (int) $menuTypeObj->client_id;
        } else {
            $menuTypeId = 0;
            $clientId   = $app->isClient('api') ? $app->getInput()->get('client_id') :
                $app->getUserState('com_menus.items.client_id', 0);
        }

        // Forced client id will override/clear menuType if conflicted
        $forcedClientId = $app->getInput()->get('client_id', null, 'string');

        if (!$app->isClient('api')) {
            // Set the menu type and client id on the list view state, so we return to this menu after saving.
            $app->setUserState('com_menus.items.menutype', $menuType);
            $app->setUserState('com_menus.items.client_id', $clientId);
        }

        // Current item if not new, we don't allow changing client id at all
        if ($pk) {
            $table = $this->getTable();
            $table->load($pk);
            $forcedClientId = $table->get('client_id', $forcedClientId);
        }

        if (isset($forcedClientId) && $forcedClientId != $clientId) {
            $clientId   = $forcedClientId;
            $menuType   = '';
            $menuTypeId = 0;
        }

        $this->setState('item.menutype', $menuType);
        $this->setState('item.client_id', $clientId);
        $this->setState('item.menutypeid', $menuTypeId);

        if (!($type = $app->getUserState('com_menus.edit.item.type'))) {
            $type = $app->getInput()->get('type');

            /**
             * Note: a new menu item will have no field type.
             * The field is required so the user has to change it.
             */
        }

        $this->setState('item.type', $type);

        $link = $app->isClient('api') ? $app->getInput()->get('link', null, 'string') :
            $app->getUserState('com_menus.edit.item.link');

        if ($link) {
            $this->setState('item.link', $link);
        }

        // Load the parameters.
        $params = ComponentHelper::getParams('com_menus');
        $this->setState('params', $params);
    }

    /**
     * Loads the menutype object by a given menutype string
     *
     * @param   string  $menutype  The given menutype
     *
     * @return  \stdClass
     *
     * @since   3.7.0
     */
    protected function getMenuType($menutype)
    {
        $table = $this->getTable('MenuType');

        $table->load(['menutype' => $menutype]);

        return (object) $table->getProperties();
    }

    /**
     * Loads the menutype ID by a given menutype string
     *
     * @param   string  $menutype  The given menutype
     *
     * @return  integer
     *
     * @since   3.6
     */
    protected function getMenuTypeId($menutype)
    {
        $menu = $this->getMenuType($menutype);

        return (int) $menu->id;
    }

    /**
     * Method to preprocess the form.
     *
     * @param   Form    $form   A Form object.
     * @param   mixed   $data   The data expected for the form.
     * @param   string  $group  The name of the plugin group to import.
     *
     * @return  void
     *
     * @since   1.6
     * @throws  \Exception if there is an error in the form event.
     */
    protected function preprocessForm(Form $form, $data, $group = 'content')
    {
        $link     = $this->getState('item.link');
        $type     = $this->getState('item.type');
        $clientId = $this->getState('item.client_id');
        $formFile = false;

        // Load the specific type file
        $typeFile   = $clientId == 1 ? 'itemadmin_' . $type : 'item_' . $type;
        $clientInfo = ApplicationHelper::getClientInfo($clientId);

        // Initialise form with component view params if available.
        if ($type == 'component') {
            $link = $link ? htmlspecialchars_decode($link) : '';

            // Parse the link arguments.
            $args = [];

            if ($link) {
                parse_str(parse_url(htmlspecialchars_decode($link), PHP_URL_QUERY), $args);
            }

            // Confirm that the option is defined.
            $option = '';
            $base   = '';

            if (isset($args['option'])) {
                // The option determines the base path to work with.
                $option = $args['option'];
                $base   = $clientInfo->path . '/components/' . $option;
            }

            if (isset($args['view'])) {
                $view = $args['view'];

                // Determine the layout to search for.
                if (isset($args['layout'])) {
                    $layout = $args['layout'];
                } else {
                    $layout = 'default';
                }

                // Check for the layout XML file. Use standard xml file if it exists.
                $tplFolders = [
                    $base . '/tmpl/' . $view,
                    $base . '/views/' . $view . '/tmpl',
                    $base . '/view/' . $view . '/tmpl',
                ];
                $path = Path::find($tplFolders, $layout . '.xml');

                if (is_file($path)) {
                    $formFile = $path;
                }

                // If custom layout, get the xml file from the template folder
                // template folder is first part of file name -- template:folder
                if (!$formFile && (strpos($layout, ':') > 0)) {
                    list($altTmpl, $altLayout) = explode(':', $layout);

                    $templatePath = Path::clean($clientInfo->path . '/templates/' . $altTmpl . '/html/' . $option . '/' . $view . '/' . $altLayout . '.xml');

                    if (is_file($templatePath)) {
                        $formFile = $templatePath;
                    }
                }
            }

            // Now check for a view manifest file
            if (!$formFile) {
                if (isset($view)) {
                    $metadataFolders = [
                        $base . '/view/' . $view,
                        $base . '/views/' . $view,
                    ];
                    $metaPath = Path::find($metadataFolders, 'metadata.xml');

                    if (is_file($path = Path::clean($metaPath))) {
                        $formFile = $path;
                    }
                } elseif ($base) {
                    // Now check for a component manifest file
                    $path = Path::clean($base . '/metadata.xml');

                    if (is_file($path)) {
                        $formFile = $path;
                    }
                }
            }
        }

        if ($formFile) {
            // If an XML file was found in the component, load it first.
            // We need to qualify the full path to avoid collisions with component file names.

            if ($form->loadFile($formFile, true, '/metadata') == false) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }

            // Attempt to load the xml file.
            if (!$xml = simplexml_load_file($formFile)) {
                throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
            }

            // Get the help data from the XML file if present.
            $help = $xml->xpath('/metadata/layout/help');
        } else {
            // We don't have a component. Load the form XML to get the help path
            $xmlFile = Path::find(JPATH_ADMINISTRATOR . '/components/com_menus/forms', $typeFile . '.xml');

            if ($xmlFile) {
                if (!$xml = simplexml_load_file($xmlFile)) {
                    throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
                }

                // Get the help data from the XML file if present.
                $help = $xml->xpath('/form/help');
            }
        }

        if (!empty($help)) {
            $helpKey = trim((string) $help[0]['key']);
            $helpURL = trim((string) $help[0]['url']);
            $helpLoc = trim((string) $help[0]['local']);

            $this->helpKey   = $helpKey ?: $this->helpKey;
            $this->helpURL   = $helpURL ?: $this->helpURL;
            $this->helpLocal = (($helpLoc == 'true') || ($helpLoc == '1') || ($helpLoc == 'local'));
        }

        if (!$form->loadFile($typeFile, true, false)) {
            throw new \Exception(Text::_('JERROR_LOADFILE_FAILED'));
        }

        // Association menu items, we currently do not support this for admin menu… may be later
        if ($clientId == 0 && Associations::isEnabled()) {
            $languages = LanguageHelper::getContentLanguages(false, false, null, 'ordering', 'asc');

            if (count($languages) > 1) {
                $addform = new \SimpleXMLElement('<form />');
                $fields  = $addform->addChild('fields');
                $fields->addAttribute('name', 'associations');
                $fieldset = $fields->addChild('fieldset');
                $fieldset->addAttribute('name', 'item_associations');
                $fieldset->addAttribute('addfieldprefix', 'Joomla\Component\Menus\Administrator\Field');

                foreach ($languages as $language) {
                    $field = $fieldset->addChild('field');
                    $field->addAttribute('name', $language->lang_code);
                    $field->addAttribute('type', 'modal_menu');
                    $field->addAttribute('language', $language->lang_code);
                    $field->addAttribute('label', $language->title);
                    $field->addAttribute('translate_label', 'false');
                    $field->addAttribute('select', 'true');
                    $field->addAttribute('new', 'true');
                    $field->addAttribute('edit', 'true');
                    $field->addAttribute('clear', 'true');
                    $field->addAttribute('propagate', 'true');
                    $option = $field->addChild('option', 'COM_MENUS_ITEM_FIELD_ASSOCIATION_NO_VALUE');
                    $option->addAttribute('value', '');
                }

                $form->load($addform, false);
            }
        }

        // Trigger the default form events.
        parent::preprocessForm($form, $data, $group);
    }

    /**
     * Method rebuild the entire nested set tree.
     *
     * @return  boolean  Boolean true on success, boolean false
     *
     * @since   1.6
     */
    public function rebuild()
    {
        // Initialise variables.
        $db    = $this->getDatabase();
        $query = $db->getQuery(true);
        $table = $this->getTable();

        try {
            $rebuildResult = $table->rebuild();
        } catch (\Exception $e) {
            $this->setError($e->getMessage());

            return false;
        }

        if (!$rebuildResult) {
            $this->setError($table->getError());

            return false;
        }

        $query->select(
            [
                $db->quoteName('id'),
                $db->quoteName('params'),
            ]
        )
            ->from($db->quoteName('#__menu'))
            ->where(
                [
                    $db->quoteName('params') . ' NOT LIKE ' . $db->quote('{%'),
                    $db->quoteName('params') . ' <> ' . $db->quote(''),
                ]
            );
        $db->setQuery($query);

        try {
            $items = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        $query = $db->getQuery(true)
            ->update($db->quoteName('#__menu'))
            ->set($db->quoteName('params') . ' = :params')
            ->where($db->quoteName('id') . ' = :id')
            ->bind(':params', $params)
            ->bind(':id', $id, ParameterType::INTEGER);
        $db->setQuery($query);

        foreach ($items as &$item) {
            // Update query parameters.
            $id     = $item->id;
            $params = new Registry($item->params);

            try {
                $db->execute();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to save the form data.
     *
     * @param   array  $data  The form data.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function save($data)
    {
        $pk      = isset($data['id']) ? $data['id'] : (int) $this->getState('item.id');
        $isNew   = true;
        $db      = $this->getDatabase();
        $query   = $db->getQuery(true);
        $table   = $this->getTable();
        $context = $this->option . '.' . $this->name;

        // Include the plugins for the on save events.
        PluginHelper::importPlugin($this->events_map['save']);

        // Load the row if saving an existing item.
        if ($pk > 0) {
            $table->load($pk);
            $isNew = false;
        }

        if (!$isNew) {
            if ($table->parent_id == $data['parent_id']) {
                // If first is chosen make the item the first child of the selected parent.
                if ($data['menuordering'] == -1) {
                    $table->setLocation($data['parent_id'], 'first-child');
                } elseif ($data['menuordering'] == -2) {
                    // If last is chosen make it the last child of the selected parent.
                    $table->setLocation($data['parent_id'], 'last-child');
                } elseif ($data['menuordering'] && $table->id != $data['menuordering'] || empty($data['id'])) {
                    // Don't try to put an item after itself. All other ones put after the selected item.
                    // $data['id'] is empty means it's a save as copy
                    $table->setLocation($data['menuordering'], 'after');
                } elseif ($data['menuordering'] && $table->id == $data['menuordering']) {
                    // \Just leave it where it is if no change is made.
                    unset($data['menuordering']);
                }
            } else {
                // Set the new parent id if parent id not matched and put in last position
                $table->setLocation($data['parent_id'], 'last-child');
            }

            // Check if we are moving to a different menu
            if ($data['menutype'] != $table->menutype) {
                // Add the child node ids to the children array.
                $query->clear()
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__menu'))
                    ->where($db->quoteName('lft') . ' BETWEEN ' . (int) $table->lft . ' AND ' . (int) $table->rgt);
                $db->setQuery($query);
                $children = (array) $db->loadColumn();
            }
        } else {
            // We have a new item, so it is not a change.
            $menuType = $this->getMenuType($data['menutype']);

            $data['client_id'] = $menuType->client_id;

            $table->setLocation($data['parent_id'], 'last-child');
        }

        // Bind the data.
        if (!$table->bind($data)) {
            $this->setError($table->getError());

            return false;
        }

        // Alter the title & alias for save2copy when required. Also, unset the home record.
        if (Factory::getApplication()->getInput()->get('task') === 'save2copy' && $data['id'] === 0) {
            $origTable = $this->getTable();
            $origTable->load($this->getState('item.id'));

            if ($table->title === $origTable->title) {
                list($title, $alias) = $this->generateNewTitle($table->parent_id, $table->alias, $table->title);
                $table->title        = $title;
                $table->alias        = $alias;
            }

            if ($table->alias === $origTable->alias) {
                $table->alias = '';
            }

            $table->published = 0;
            $table->home      = 0;
        }

        // Check the data.
        if (!$table->check()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the before save event.
        $result = Factory::getApplication()->triggerEvent($this->event_before_save, [$context, &$table, $isNew, $data]);

        // Store the data.
        if (in_array(false, $result, true) || !$table->store()) {
            $this->setError($table->getError());

            return false;
        }

        // Trigger the after save event.
        Factory::getApplication()->triggerEvent($this->event_after_save, [$context, &$table, $isNew]);

        // Rebuild the tree path.
        if (!$table->rebuildPath($table->id)) {
            $this->setError($table->getError());

            return false;
        }

        // Rebuild the paths of the menu item's children:
        if (!$table->rebuild($table->id, $table->lft, $table->level, $table->path)) {
            $this->setError($table->getError());

            return false;
        }

        // Process the child rows
        if (!empty($children)) {
            // Remove any duplicates and sanitize ids.
            $children = array_unique($children);
            $children = ArrayHelper::toInteger($children);

            // Update the menutype field in all nodes where necessary.
            $query = $db->getQuery(true)
                ->update($db->quoteName('#__menu'))
                ->set($db->quoteName('menutype') . ' = :menutype')
                ->whereIn($db->quoteName('id'), $children)
                ->bind(':menutype', $data['menutype']);

            try {
                $db->setQuery($query);
                $db->execute();
            } catch (\RuntimeException $e) {
                $this->setError($e->getMessage());

                return false;
            }
        }

        $this->setState('item.id', $table->id);
        $this->setState('item.menutype', $table->menutype);

        // Load associated menu items, for now not supported for admin menu… may be later
        if ($table->get('client_id') == 0 && Associations::isEnabled()) {
            // Adding self to the association
            $associations = isset($data['associations']) ? $data['associations'] : [];

            // Unset any invalid associations
            $associations = ArrayHelper::toInteger($associations);

            foreach ($associations as $tag => $id) {
                if (!$id) {
                    unset($associations[$tag]);
                }
            }

            // Detecting all item menus
            $all_language = $table->language == '*';

            if ($all_language && !empty($associations)) {
                Factory::getApplication()->enqueueMessage(Text::_('COM_MENUS_ERROR_ALL_LANGUAGE_ASSOCIATED'), 'notice');
            }

            // Get associationskey for edited item
            $db    = $this->getDatabase();
            $query = $db->getQuery(true)
                ->select($db->quoteName('key'))
                ->from($db->quoteName('#__associations'))
                ->where(
                    [
                        $db->quoteName('context') . ' = :context',
                        $db->quoteName('id') . ' = :id',
                    ]
                )
                ->bind(':context', $this->associationsContext)
                ->bind(':id', $table->id, ParameterType::INTEGER);
            $db->setQuery($query);
            $oldKey = $db->loadResult();

            if ($associations || $oldKey !== null) {
                // Deleting old associations for the associated items
                $where = [];
                $query = $db->getQuery(true)
                    ->delete($db->quoteName('#__associations'))
                    ->where($db->quoteName('context') . ' = :context')
                    ->bind(':context', $this->associationsContext);

                if ($associations) {
                    $where[] = $db->quoteName('id') . ' IN (' . implode(',', $query->bindArray(array_values($associations))) . ')';
                }

                if ($oldKey !== null) {
                    $where[] = $db->quoteName('key') . ' = :oldKey';
                    $query->bind(':oldKey', $oldKey);
                }

                $query->extendWhere('AND', $where, 'OR');

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }

            // Adding self to the association
            if (!$all_language) {
                $associations[$table->language] = (int) $table->id;
            }

            if (count($associations) > 1) {
                // Adding new association for these items
                $key   = md5(json_encode($associations));
                $query = $db->getQuery(true)
                    ->insert($db->quoteName('#__associations'))
                    ->columns(
                        [
                            $db->quoteName('id'),
                            $db->quoteName('context'),
                            $db->quoteName('key'),
                        ]
                    );

                foreach ($associations as $id) {
                    $query->values(
                        implode(
                            ',',
                            $query->bindArray(
                                [$id, $this->associationsContext, $key],
                                [ParameterType::INTEGER, ParameterType::STRING, ParameterType::STRING]
                            )
                        )
                    );
                }

                try {
                    $db->setQuery($query);
                    $db->execute();
                } catch (\RuntimeException $e) {
                    $this->setError($e->getMessage());

                    return false;
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        if (isset($data['link'])) {
            $base   = Uri::base();
            $juri   = Uri::getInstance($base . $data['link']);
            $option = $juri->getVar('option');

            // Clean the cache
            parent::cleanCache($option);
        }

        if (Factory::getApplication()->getInput()->get('task') === 'editAssociations') {
            return $this->redirectToAssociations($data);
        }

        return true;
    }

    /**
     * Method to save the reordered nested set tree.
     * First we save the new order values in the lft values of the changed ids.
     * Then we invoke the table rebuild to implement the new ordering.
     *
     * @param   array  $idArray   Rows identifiers to be reordered
     * @param   array  $lftArray  lft values of rows to be reordered
     *
     * @return  boolean false on failure or error, true otherwise.
     *
     * @since   1.6
     */
    public function saveorder($idArray = null, $lftArray = null)
    {
        // Get an instance of the table object.
        $table = $this->getTable();

        if (!$table->saveorder($idArray, $lftArray)) {
            $this->setError($table->getError());

            return false;
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the home state of one or more items.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the home state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function setHome(&$pks, $value = 1)
    {
        $table = $this->getTable();
        $pks   = (array) $pks;

        $languages = [];
        $onehome   = false;

        // Remember that we can set a home page for different languages,
        // so we need to loop through the primary key array.
        foreach ($pks as $i => $pk) {
            if ($table->load($pk)) {
                if (!array_key_exists($table->language, $languages)) {
                    $languages[$table->language] = true;

                    if ($table->home == $value) {
                        unset($pks[$i]);
                        Factory::getApplication()->enqueueMessage(Text::_('COM_MENUS_ERROR_ALREADY_HOME'), 'notice');
                    } elseif ($table->menutype == 'main') {
                        // Prune items that you can't change.
                        unset($pks[$i]);
                        Factory::getApplication()->enqueueMessage(Text::_('COM_MENUS_ERROR_MENUTYPE_HOME'), 'error');
                    } else {
                        $table->home = $value;

                        if ($table->language == '*') {
                            $table->published = 1;
                        }

                        if (!$this->canSave($table)) {
                            // Prune items that you can't change.
                            unset($pks[$i]);
                            Factory::getApplication()->enqueueMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');
                        } elseif (!$table->check()) {
                            // Prune the items that failed pre-save checks.
                            unset($pks[$i]);
                            Factory::getApplication()->enqueueMessage($table->getError(), 'error');
                        } elseif (!$table->store()) {
                            // Prune the items that could not be stored.
                            unset($pks[$i]);
                            Factory::getApplication()->enqueueMessage($table->getError(), 'error');
                        }
                    }
                } else {
                    unset($pks[$i]);

                    if (!$onehome) {
                        $onehome = true;
                        Factory::getApplication()->enqueueMessage(Text::sprintf('COM_MENUS_ERROR_ONE_HOME'), 'notice');
                    }
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        return true;
    }

    /**
     * Method to change the published state of one or more records.
     *
     * @param   array    $pks    A list of the primary keys to change.
     * @param   integer  $value  The value of the published state.
     *
     * @return  boolean  True on success.
     *
     * @since   1.6
     */
    public function publish(&$pks, $value = 1)
    {
        $table = $this->getTable();
        $pks   = (array) $pks;

        // Default menu item existence checks.
        if ($value != 1) {
            foreach ($pks as $i => $pk) {
                if ($table->load($pk) && $table->home && $table->language == '*') {
                    // Prune items that you can't change.
                    Factory::getApplication()->enqueueMessage(Text::_('JLIB_DATABASE_ERROR_MENU_UNPUBLISH_DEFAULT_HOME'), 'error');
                    unset($pks[$i]);
                    break;
                }
            }
        }

        // Clean the cache
        $this->cleanCache();

        // Ensure that previous checks doesn't empty the array
        if (empty($pks)) {
            return true;
        }

        return parent::publish($pks, $value);
    }

    /**
     * Method to change the title & alias.
     *
     * @param   integer  $parentId  The id of the parent.
     * @param   string   $alias     The alias.
     * @param   string   $title     The title.
     *
     * @return  array  Contains the modified title and alias.
     *
     * @since   1.6
     */
    protected function generateNewTitle($parentId, $alias, $title)
    {
        // Alter the title & alias
        $table = $this->getTable();

        while ($table->load(['alias' => $alias, 'parent_id' => $parentId])) {
            if ($title == $table->title) {
                $title = StringHelper::increment($title);
            }

            $alias = StringHelper::increment($alias, 'dash');
        }

        return [$title, $alias];
    }

    /**
     * Custom clean the cache
     *
     * @param   string   $group     Cache group name.
     * @param   integer  $clientId  No longer used, will be removed without replacement
     *                              @deprecated   4.3 will be removed in 6.0
     *
     * @return  void
     *
     * @since   1.6
     */
    protected function cleanCache($group = null, $clientId = 0)
    {
        parent::cleanCache('com_menus');
        parent::cleanCache('com_modules');
        parent::cleanCache('mod_menu');
    }
}
