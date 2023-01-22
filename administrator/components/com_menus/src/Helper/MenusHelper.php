<?php

/**
 * @package     Joomla.Administrator
 * @subpackage  com_menus
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Menus\Administrator\Helper;

use Joomla\CMS\Application\ApplicationHelper;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Associations;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Menu\AdministratorMenuItem;
use Joomla\CMS\Table\Table;
use Joomla\Database\DatabaseInterface;
use Joomla\Database\ParameterType;
use Joomla\Registry\Registry;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Menus component helper.
 *
 * @since  1.6
 */
class MenusHelper extends ContentHelper
{
    /**
     * Defines the valid request variables for the reverse lookup.
     *
     * @var     array
     */
    protected static $_filter = ['option', 'view', 'layout'];

    /**
     * List of preset include paths
     *
     * @var  array
     *
     * @since   4.0.0
     */
    protected static $presets = null;

    /**
     * Gets a standard form of a link for lookups.
     *
     * @param   mixed  $request  A link string or array of request variables.
     *
     * @return  mixed  A link in standard option-view-layout form, or false if the supplied response is invalid.
     *
     * @since   1.6
     */
    public static function getLinkKey($request)
    {
        if (empty($request)) {
            return false;
        }

        // Check if the link is in the form of index.php?...
        if (is_string($request)) {
            $args = [];

            if (strpos($request, 'index.php') === 0) {
                parse_str(parse_url(htmlspecialchars_decode($request), PHP_URL_QUERY), $args);
            } else {
                parse_str($request, $args);
            }

            $request = $args;
        }

        // Only take the option, view and layout parts.
        foreach ($request as $name => $value) {
            if ((!in_array($name, self::$_filter)) && (!($name == 'task' && !array_key_exists('view', $request)))) {
                // Remove the variables we want to ignore.
                unset($request[$name]);
            }
        }

        ksort($request);

        return 'index.php?' . http_build_query($request, '', '&');
    }

    /**
     * Get the menu list for create a menu module
     *
     * @param   int  $clientId  Optional client id - viz 0 = site, 1 = administrator, can be NULL for all
     *
     * @return  array  The menu array list
     *
     * @since    1.6
     */
    public static function getMenuTypes($clientId = 0)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select($db->quoteName('a.menutype'))
            ->from($db->quoteName('#__menu_types', 'a'));

        if (isset($clientId)) {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('a.client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        return $db->loadColumn();
    }

    /**
     * Get a list of menu links for one or all menus.
     *
     * @param   string   $menuType   An option menu to filter the list on, otherwise all menu with given client id links
     *                               are returned as a grouped array.
     * @param   integer  $parentId   An optional parent ID to pivot results around.
     * @param   integer  $mode       An optional mode. If parent ID is set and mode=2, the parent and children are excluded from the list.
     * @param   array    $published  An optional array of states
     * @param   array    $languages  Optional array of specify which languages we want to filter
     * @param   int      $clientId   Optional client id - viz 0 = site, 1 = administrator, can be NULL for all (used only if menutype not given)
     *
     * @return  array|boolean
     *
     * @since   1.6
     */
    public static function getMenuLinks($menuType = null, $parentId = 0, $mode = 0, $published = [], $languages = [], $clientId = 0)
    {
        $hasClientId = $clientId !== null;
        $clientId    = (int) $clientId;

        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select(
                [
                    'DISTINCT ' . $db->quoteName('a.id', 'value'),
                    $db->quoteName('a.title', 'text'),
                    $db->quoteName('a.alias'),
                    $db->quoteName('a.level'),
                    $db->quoteName('a.menutype'),
                    $db->quoteName('a.client_id'),
                    $db->quoteName('a.type'),
                    $db->quoteName('a.published'),
                    $db->quoteName('a.template_style_id'),
                    $db->quoteName('a.checked_out'),
                    $db->quoteName('a.language'),
                    $db->quoteName('a.lft'),
                    $db->quoteName('e.name', 'componentname'),
                    $db->quoteName('e.element'),
                ]
            )
            ->from($db->quoteName('#__menu', 'a'))
            ->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('a.component_id'));

        if (Multilanguage::isEnabled()) {
            $query->select(
                [
                    $db->quoteName('l.title', 'language_title'),
                    $db->quoteName('l.image', 'language_image'),
                    $db->quoteName('l.sef', 'language_sef'),
                ]
            )
                ->join('LEFT', $db->quoteName('#__languages', 'l'), $db->quoteName('l.lang_code') . ' = ' . $db->quoteName('a.language'));
        }

        // Filter by the type if given, this is more specific than client id
        if ($menuType) {
            $query->where('(' . $db->quoteName('a.menutype') . ' = :menuType OR ' . $db->quoteName('a.parent_id') . ' = 0)')
                ->bind(':menuType', $menuType);
        } elseif ($hasClientId) {
            $query->where($db->quoteName('a.client_id') . ' = :clientId')
                ->bind(':clientId', $clientId, ParameterType::INTEGER);
        }

        // Prevent the parent and children from showing if requested.
        if ($parentId && $mode == 2) {
            $query->join('LEFT', $db->quoteName('#__menu', 'p'), $db->quoteName('p.id') . ' = :parentId')
                ->where(
                    '(' . $db->quoteName('a.lft') . ' <= ' . $db->quoteName('p.lft')
                    . ' OR ' . $db->quoteName('a.rgt') . ' >= ' . $db->quoteName('p.rgt') . ')'
                )
                ->bind(':parentId', $parentId, ParameterType::INTEGER);
        }

        if (!empty($languages)) {
            $query->whereIn($db->quoteName('a.language'), (array) $languages, ParameterType::STRING);
        }

        if (!empty($published)) {
            $query->whereIn($db->quoteName('a.published'), (array) $published);
        }

        $query->where($db->quoteName('a.published') . ' != -2');
        $query->order($db->quoteName('a.lft') . ' ASC');

        try {
            // Get the options.
            $db->setQuery($query);
            $links = $db->loadObjectList();
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

            return false;
        }

        if (empty($menuType)) {
            // If the menutype is empty, group the items by menutype.
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__menu_types'))
                ->where($db->quoteName('menutype') . ' <> ' . $db->quote(''))
                ->order(
                    [
                        $db->quoteName('title'),
                        $db->quoteName('menutype'),
                    ]
                );

            if ($hasClientId) {
                $query->where($db->quoteName('client_id') . ' = :clientId')
                    ->bind(':clientId', $clientId, ParameterType::INTEGER);
            }

            try {
                $db->setQuery($query);
                $menuTypes = $db->loadObjectList();
            } catch (\RuntimeException $e) {
                Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

                return false;
            }

            // Create a reverse lookup and aggregate the links.
            $rlu = [];

            foreach ($menuTypes as &$type) {
                $rlu[$type->menutype] = & $type;
                $type->links = [];
            }

            // Loop through the list of menu links.
            foreach ($links as &$link) {
                if (isset($rlu[$link->menutype])) {
                    $rlu[$link->menutype]->links[] = & $link;

                    // Cleanup garbage.
                    unset($link->menutype);
                }
            }

            return $menuTypes;
        } else {
            return $links;
        }
    }

    /**
     * Get the associations
     *
     * @param   integer  $pk  Menu item id
     *
     * @return  array
     *
     * @since   3.0
     */
    public static function getAssociations($pk)
    {
        $langAssociations = Associations::getAssociations('com_menus', '#__menu', 'com_menus.item', $pk, 'id', '', '');
        $associations     = [];

        foreach ($langAssociations as $langAssociation) {
            $associations[$langAssociation->language] = $langAssociation->id;
        }

        return $associations;
    }

    /**
     * Load the menu items from database for the given menutype
     *
     * @param   string   $menutype     The selected menu type
     * @param   boolean  $enabledOnly  Whether to load only enabled/published menu items.
     * @param   int[]    $exclude      The menu items to exclude from the list
     *
     * @return  AdministratorMenuItem  A root node with the menu items as children
     *
     * @since   4.0.0
     */
    public static function getMenuItems($menutype, $enabledOnly = false, $exclude = [])
    {
        $root  = new AdministratorMenuItem();
        $db    = Factory::getContainer()->get(DatabaseInterface::class);
        $query = $db->getQuery(true);

        // Prepare the query.
        $query->select($db->quoteName('m') . '.*')
            ->from($db->quoteName('#__menu', 'm'))
            ->where(
                [
                    $db->quoteName('m.menutype') . ' = :menutype',
                    $db->quoteName('m.client_id') . ' = 1',
                    $db->quoteName('m.id') . ' > 1',
                ]
            )
            ->bind(':menutype', $menutype);

        if ($enabledOnly) {
            $query->where($db->quoteName('m.published') . ' = 1');
        }

        // Filter on the enabled states.
        $query->select($db->quoteName('e.element'))
            ->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('m.component_id') . ' = ' . $db->quoteName('e.extension_id'))
            ->extendWhere(
                'AND',
                [
                    $db->quoteName('e.enabled') . ' = 1',
                    $db->quoteName('e.enabled') . ' IS NULL',
                ],
                'OR'
            );

        if (count($exclude)) {
            $exId = array_map('intval', array_filter($exclude, 'is_numeric'));
            $exEl = array_filter($exclude, 'is_string');

            if ($exId) {
                $query->whereNotIn($db->quoteName('m.id'), $exId)
                    ->whereNotIn($db->quoteName('m.parent_id'), $exId);
            }

            if ($exEl) {
                $query->whereNotIn($db->quoteName('e.element'), $exEl, ParameterType::STRING);
            }
        }

        // Order by lft.
        $query->order($db->quoteName('m.lft'));

        try {
            $menuItems = [];
            $iterator  = $db->setQuery($query)->getIterator();

            foreach ($iterator as $item) {
                $menuItems[$item->id] = new AdministratorMenuItem((array) $item);
            }

            unset($iterator);

            foreach ($menuItems as $menuitem) {
                // Resolve the alias item to get the original item
                if ($menuitem->type == 'alias') {
                    static::resolveAlias($menuitem);
                }

                if ($menuitem->link = in_array($menuitem->type, ['separator', 'heading', 'container']) ? '#' : trim($menuitem->link)) {
                    $menuitem->submenu = [];
                    $menuitem->class   = $menuitem->img ?? '';
                    $menuitem->scope   = $menuitem->scope ?? null;
                    $menuitem->target  = $menuitem->browserNav ? '_blank' : '';
                }

                $menuitem->ajaxbadge  = $menuitem->getParams()->get('ajax-badge');
                $menuitem->dashboard  = $menuitem->getParams()->get('dashboard');

                if ($menuitem->parent_id > 1) {
                    if (isset($menuItems[$menuitem->parent_id])) {
                        $menuItems[$menuitem->parent_id]->addChild($menuitem);
                    }
                } else {
                    $root->addChild($menuitem);
                }
            }
        } catch (\RuntimeException $e) {
            Factory::getApplication()->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');
        }

        return $root;
    }

    /**
     * Method to install a preset menu into database and link them to the given menutype
     *
     * @param   string  $preset    The preset name
     * @param   string  $menutype  The target menutype
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   4.0.0
     */
    public static function installPreset($preset, $menutype)
    {
        $root = static::loadPreset($preset, false);

        if (count($root->getChildren()) == 0) {
            throw new \Exception(Text::_('COM_MENUS_PRESET_LOAD_FAILED'));
        }

        static::installPresetItems($root, $menutype);
    }

    /**
     * Method to install a preset menu item into database and link it to the given menutype
     *
     * @param   AdministratorMenuItem  $node      The parent node of the items to process
     * @param   string                 $menutype  The target menutype
     *
     * @return  void
     *
     * @throws  \Exception
     *
     * @since   4.0.0
     */
    protected static function installPresetItems($node, $menutype)
    {
        $db    = Factory::getDbo();
        $query = $db->getQuery(true);
        $items = $node->getChildren();

        static $components = [];

        if (!$components) {
            $query->select(
                [
                    $db->quoteName('extension_id'),
                    $db->quoteName('element'),
                ]
            )
                ->from($db->quoteName('#__extensions'))
                ->where($db->quoteName('type') . ' = ' . $db->quote('component'));
            $components = $db->setQuery($query)->loadObjectList();
            $components = array_column((array) $components, 'element', 'extension_id');
        }

        Factory::getApplication()->triggerEvent('onPreprocessMenuItems', ['com_menus.administrator.import', &$items, null, true]);

        foreach ($items as $item) {
            /** @var \Joomla\CMS\Table\Menu $table */
            $table = Table::getInstance('Menu');

            $item->alias = $menutype . '-' . $item->title;

            // Temporarily set unicodeslugs if a menu item has an unicode alias
            $unicode     = Factory::getApplication()->set('unicodeslugs', 1);
            $item->alias = ApplicationHelper::stringURLSafe($item->alias);
            Factory::getApplication()->set('unicodeslugs', $unicode);

            if ($item->type == 'separator') {
                // Do not reuse a separator
                $item->title = $item->title ?: '-';
                $item->alias = microtime(true);
            } elseif ($item->type == 'heading' || $item->type == 'container') {
                // Try to match an existing record to have minimum collision for a heading
                $keys  = [
                    'menutype'  => $menutype,
                    'type'      => $item->type,
                    'title'     => $item->title,
                    'parent_id' => (int) $item->getParent()->id,
                    'client_id' => 1,
                ];
                $table->load($keys);
            } elseif ($item->type == 'url' || $item->type == 'component') {
                if (substr($item->link, 0, 8) === 'special:') {
                    $special = substr($item->link, 8);

                    if ($special === 'language-forum') {
                        $item->link = 'index.php?option=com_admin&amp;view=help&amp;layout=langforum';
                    } elseif ($special === 'custom-forum') {
                        $item->link = '';
                    }
                }

                // Try to match an existing record to have minimum collision for a link
                $keys  = [
                    'menutype'  => $menutype,
                    'type'      => $item->type,
                    'link'      => $item->link,
                    'parent_id' => (int) $item->getParent()->id,
                    'client_id' => 1,
                ];
                $table->load($keys);
            }

            // Translate "hideitems" param value from "element" into "menu-item-id"
            if ($item->type == 'container' && count($hideitems = (array) $item->getParams()->get('hideitems'))) {
                foreach ($hideitems as &$hel) {
                    if (!is_numeric($hel)) {
                        $hel = array_search($hel, $components);
                    }
                }

                $query = $db->getQuery(true)
                    ->select($db->quoteName('id'))
                    ->from($db->quoteName('#__menu'))
                    ->whereIn($db->quoteName('component_id'), $hideitems);
                $hideitems = $db->setQuery($query)->loadColumn();

                $item->getParams()->set('hideitems', $hideitems);
            }

            $record = [
                'menutype'     => $menutype,
                'title'        => $item->title,
                'alias'        => $item->alias,
                'type'         => $item->type,
                'link'         => $item->link,
                'browserNav'   => $item->browserNav,
                'img'          => $item->class,
                'access'       => $item->access,
                'component_id' => array_search($item->element, $components) ?: 0,
                'parent_id'    => (int) $item->getParent()->id,
                'client_id'    => 1,
                'published'    => 1,
                'language'     => '*',
                'home'         => 0,
                'params'       => (string) $item->getParams(),
            ];

            if (!$table->bind($record)) {
                throw new \Exception($table->getError());
            }

            $table->setLocation($item->getParent()->id, 'last-child');

            if (!$table->check()) {
                throw new \Exception($table->getError());
            }

            if (!$table->store()) {
                throw new \Exception($table->getError());
            }

            $item->id = $table->get('id');

            if ($item->hasChildren()) {
                static::installPresetItems($item, $menutype);
            }
        }
    }

    /**
     * Add a custom preset externally via plugin or any other means.
     * WARNING: Presets with same name will replace previously added preset *except* Joomla's default preset (joomla)
     *
     * @param   string  $name     The unique identifier for the preset.
     * @param   string  $title    The display label for the preset.
     * @param   string  $path     The path to the preset file.
     * @param   bool    $replace  Whether to replace the preset with the same name if any (except 'joomla').
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function addPreset($name, $title, $path, $replace = true)
    {
        if (static::$presets === null) {
            static::getPresets();
        }

        if ($name == 'joomla') {
            $replace = false;
        }

        if (($replace || !array_key_exists($name, static::$presets)) && is_file($path)) {
            $preset = new \stdClass();

            $preset->name  = $name;
            $preset->title = $title;
            $preset->path  = $path;

            static::$presets[$name] = $preset;
        }
    }

    /**
     * Get a list of available presets.
     *
     * @return  \stdClass[]
     *
     * @since   4.0.0
     */
    public static function getPresets()
    {
        if (static::$presets === null) {
            // Important: 'null' will cause infinite recursion.
            static::$presets = [];

            $components = ComponentHelper::getComponents();
            $lang       = Factory::getApplication()->getLanguage();

            foreach ($components as $component) {
                if (!$component->enabled) {
                    continue;
                }

                $folder = JPATH_ADMINISTRATOR . '/components/' . $component->option . '/presets/';

                if (!Folder::exists($folder)) {
                    continue;
                }

                $lang->load($component->option . '.sys', JPATH_ADMINISTRATOR)
                || $lang->load($component->option . '.sys', JPATH_ADMINISTRATOR . '/components/' . $component->option);

                $presets = Folder::files($folder, '.xml');

                foreach ($presets as $preset) {
                    $name  = File::stripExt($preset);
                    $title = strtoupper($component->option . '_MENUS_PRESET_' . $name);
                    static::addPreset($name, $title, $folder . $preset);
                }
            }

            // Load from template folder automatically
            $app = Factory::getApplication();
            $tpl = JPATH_THEMES . '/' . $app->getTemplate() . '/html/com_menus/presets';

            if (is_dir($tpl)) {
                $files = Folder::files($tpl, '\.xml$');

                foreach ($files as $file) {
                    $name  = substr($file, 0, -4);
                    $title = str_replace('-', ' ', $name);

                    static::addPreset(strtolower($name), ucwords($title), $tpl . '/' . $file);
                }
            }
        }

        return static::$presets;
    }

    /**
     * Load the menu items from a preset file into a hierarchical list of objects
     *
     * @param   string                 $name      The preset name
     * @param   bool                   $fallback  Fallback to default (joomla) preset if the specified one could not be loaded?
     * @param   AdministratorMenuItem  $parent    Root node of the menu
     *
     * @return  AdministratorMenuItem
     *
     * @since   4.0.0
     */
    public static function loadPreset($name, $fallback = true, $parent = null)
    {
        $presets = static::getPresets();

        if (!$parent) {
            $parent = new AdministratorMenuItem();
        }

        if (isset($presets[$name]) && ($xml = simplexml_load_file($presets[$name]->path, null, LIBXML_NOCDATA)) && $xml instanceof \SimpleXMLElement) {
            static::loadXml($xml, $parent);
        } elseif ($fallback && isset($presets['default'])) {
            if (($xml = simplexml_load_file($presets['default']->path, null, LIBXML_NOCDATA)) && $xml instanceof \SimpleXMLElement) {
                static::loadXml($xml, $parent);
            }
        }

        return $parent;
    }

    /**
     * Method to resolve the menu item alias type menu item
     *
     * @param   AdministratorMenuItem  &$item  The alias object
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function resolveAlias(&$item)
    {
        $obj = $item;

        while ($obj->type == 'alias') {
            $aliasTo = (int) $obj->getParams()->get('aliasoptions');

            $db = Factory::getDbo();
            $query = $db->getQuery(true);
            $query->select(
                [
                    $db->quoteName('a.id'),
                    $db->quoteName('a.link'),
                    $db->quoteName('a.type'),
                    $db->quoteName('e.element'),
                ]
            )
                ->from($db->quoteName('#__menu', 'a'))
                ->join('LEFT', $db->quoteName('#__extensions', 'e'), $db->quoteName('e.extension_id') . ' = ' . $db->quoteName('a.component_id'))
                ->where($db->quoteName('a.id') . ' = :aliasTo')
                ->bind(':aliasTo', $aliasTo, ParameterType::INTEGER);

            try {
                $obj = new AdministratorMenuItem($db->setQuery($query)->loadAssoc());

                if (!$obj) {
                    $item->link = '';

                    return;
                }
            } catch (\Exception $e) {
                $item->link = '';

                return;
            }
        }

        $item->id      = $obj->id;
        $item->link    = $obj->link;
        $item->type    = $obj->type;
        $item->element = $obj->element;
    }

    /**
     * Parse the flat list of menu items and prepare the hierarchy of them using parent-child relationship.
     *
     * @param   AdministratorMenuItem  $item  Menu item to preprocess
     *
     * @return  void
     *
     * @since   4.0.0
     */
    public static function preprocess($item)
    {
        // Resolve the alias item to get the original item
        if ($item->type == 'alias') {
            static::resolveAlias($item);
        }

        if ($item->link = in_array($item->type, ['separator', 'heading', 'container']) ? '#' : trim($item->link)) {
            $item->class  = $item->img ?? '';
            $item->scope  = $item->scope ?? null;
            $item->target = $item->browserNav ? '_blank' : '';
        }
    }

    /**
     * Load a menu tree from an XML file
     *
     * @param   \SimpleXMLElement[]    $elements  The xml menuitem nodes
     * @param   AdministratorMenuItem  $parent    The menu hierarchy list to be populated
     * @param   string[]               $replace   The substring replacements for iterator type items
     *
     * @return  void
     *
     * @since  4.0.0
     */
    protected static function loadXml($elements, $parent, $replace = [])
    {
        foreach ($elements as $element) {
            if ($element->getName() != 'menuitem') {
                continue;
            }

            $select = (string) $element['sql_select'];
            $from   = (string) $element['sql_from'];

            /**
             * Following is a repeatable group based on simple database query. This requires sql_* attributes (sql_select and sql_from are required)
             * The values can be used like - "{sql:columnName}" in any attribute of repeated elements.
             * The repeated elements are place inside this xml node but they will be populated in the same level in the rendered menu
             */
            if ($select && $from) {
                $hidden = $element['hidden'] == 'true';
                $where  = (string) $element['sql_where'];
                $order  = (string) $element['sql_order'];
                $group  = (string) $element['sql_group'];
                $lJoin  = (string) $element['sql_leftjoin'];
                $iJoin  = (string) $element['sql_innerjoin'];

                $db    = Factory::getDbo();
                $query = $db->getQuery(true);
                $query->select($select)->from($from);

                if ($where) {
                    $query->where($where);
                }

                if ($order) {
                    $query->order($order);
                }

                if ($group) {
                    $query->group($group);
                }

                if ($lJoin) {
                    $query->join('LEFT', $lJoin);
                }

                if ($iJoin) {
                    $query->join('INNER', $iJoin);
                }

                $results = $db->setQuery($query)->loadObjectList();

                // Skip the entire group if no items to iterate over.
                if ($results) {
                    // Show the repeatable group heading node only if not set as hidden.
                    if (!$hidden) {
                        $child = static::parseXmlNode($element, $replace);
                        $parent->addChild($child);
                    }

                    // Iterate over the matching records, items goes in the same level (not $item->submenu) as this node.
                    if ('self' == (string) $element['sql_target']) {
                        foreach ($results as $result) {
                            static::loadXml($element->menuitem, $child, $result);
                        }
                    } else {
                        foreach ($results as $result) {
                            static::loadXml($element->menuitem, $parent, $result);
                        }
                    }
                }
            } else {
                $item = static::parseXmlNode($element, $replace);

                // Process the child nodes
                static::loadXml($element->menuitem, $item, $replace);

                $parent->addChild($item);
            }
        }
    }

    /**
     * Create a menu item node from an xml element
     *
     * @param   \SimpleXMLElement  $node     A menuitem element from preset xml
     * @param   string[]           $replace  The values to substitute in the title, link and element texts
     *
     * @return  \stdClass
     *
     * @since   4.0.0
     */
    protected static function parseXmlNode($node, $replace = [])
    {
        $item = new AdministratorMenuItem();

        $item->id         = null;
        $item->type       = (string) $node['type'];
        $item->title      = (string) $node['title'];
        $item->alias      = (string) $node['alias'];
        $item->link       = (string) $node['link'];
        $item->target     = (string) $node['target'];
        $item->element    = (string) $node['element'];
        $item->class      = (string) $node['class'];
        $item->icon       = (string) $node['icon'];
        $item->access     = (int) $node['access'];
        $item->scope      = (string) $node['scope'] ?: 'default';
        $item->ajaxbadge  = (string) $node['ajax-badge'];
        $item->dashboard  = (string) $node['dashboard'];

        $params = new Registry(trim($node->params));
        $params->set('menu-permission', (string) $node['permission']);

        if ($item->type == 'separator' && trim($item->title, '- ')) {
            $params->set('text_separator', 1);
        }

        if ($item->type == 'heading' || $item->type == 'container') {
            $item->link = '#';
        }

        if ((string) $node['quicktask']) {
            $params->set('menu-quicktask', (string) $node['quicktask']);
            $params->set('menu-quicktask-title', (string) $node['quicktask-title']);
            $params->set('menu-quicktask-icon', (string) $node['quicktask-icon']);
            $params->set('menu-quicktask-permission', (string) $node['quicktask-permission']);
        }

        // Translate attributes for iterator values
        foreach ($replace as $var => $val) {
            $item->title   = str_replace("{sql:$var}", $val, $item->title);
            $item->element = str_replace("{sql:$var}", $val, $item->element);
            $item->link    = str_replace("{sql:$var}", $val, $item->link);
            $item->class   = str_replace("{sql:$var}", $val, $item->class);
            $item->icon    = str_replace("{sql:$var}", $val, $item->icon);
            $params->set('menu-quicktask', str_replace("{sql:$var}", $val, $params->get('menu-quicktask')));
        }

        $item->setParams($params);

        return $item;
    }
}
