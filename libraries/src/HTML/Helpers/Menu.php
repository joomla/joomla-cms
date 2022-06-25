<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2007 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\HTML\Helpers;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\Database\ParameterType;

/**
 * Utility class working with menu select lists
 *
 * @since  1.5
 */
abstract class Menu
{
    /**
     * Cached array of the menus.
     *
     * @var    array
     * @since  1.6
     */
    protected static $menus = array();

    /**
     * Cached array of the menus items.
     *
     * @var    array
     * @since  1.6
     */
    protected static $items = array();

    /**
     * Get a list of the available menus.
     *
     * @param   int  $clientId  The client id
     *
     * @return  array
     *
     * @since   1.6
     */
    public static function menus($clientId = 0)
    {
        $key = serialize($clientId);

        if (!isset(static::$menus[$key])) {
            $db = Factory::getDbo();

            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('id'),
                        $db->quoteName('menutype', 'value'),
                        $db->quoteName('title', 'text'),
                        $db->quoteName('client_id'),
                    ]
                )
                ->from($db->quoteName('#__menu_types'))
                ->order(
                    [
                        $db->quoteName('client_id'),
                        $db->quoteName('title'),
                    ]
                );

            if (isset($clientId)) {
                $clientId = (int) $clientId;
                $query->where($db->quoteName('client_id') . ' = :client')
                    ->bind(':client', $clientId, ParameterType::INTEGER);
            }

            static::$menus[$key] = $db->setQuery($query)->loadObjectList();
        }

        return static::$menus[$key];
    }

    /**
     * Returns an array of menu items grouped by menu.
     *
     * @param   array  $config  An array of configuration options [published, checkacl, clientid].
     *
     * @return  array
     *
     * @since   1.6
     */
    public static function menuItems($config = array())
    {
        $key = serialize($config);

        if (empty(static::$items[$key])) {
            // B/C - not passed  = 0, null can be passed for both clients
            $clientId = array_key_exists('clientid', $config) ? $config['clientid'] : 0;
            $menus    = static::menus($clientId);

            $db    = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('a.id', 'value'),
                        $db->quoteName('a.title', 'text'),
                        $db->quoteName('a.level'),
                        $db->quoteName('a.menutype'),
                        $db->quoteName('a.client_id'),
                    ]
                )
                ->from($db->quoteName('#__menu', 'a'))
                ->where($db->quoteName('a.parent_id') . ' > 0');

            // Filter on the client id
            if (isset($clientId)) {
                $query->where($db->quoteName('a.client_id') . ' = :client')
                    ->bind(':client', $clientId, ParameterType::INTEGER);
            }

            // Filter on the published state
            if (isset($config['published'])) {
                if (is_numeric($config['published'])) {
                    $query->where($db->quoteName('a.published') . ' = :published')
                        ->bind(':published', $config['published'], ParameterType::INTEGER);
                } elseif ($config['published'] === '') {
                    $query->where($db->quoteName('a.published') . ' IN (0,1)');
                }
            }

            $query->order($db->quoteName('a.lft'));

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Collate menu items based on menutype
            $lookup = array();

            foreach ($items as &$item) {
                if (!isset($lookup[$item->menutype])) {
                    $lookup[$item->menutype] = array();
                }

                $lookup[$item->menutype][] = &$item;

                // Translate the menu item title when client is administrator
                if ($clientId === 1) {
                    $item->text = Text::_($item->text);
                }

                $item->text = str_repeat('- ', $item->level) . $item->text;
            }

            static::$items[$key] = array();

            $user = Factory::getUser();

            $aclcheck = !empty($config['checkacl']) ? (int) $config['checkacl'] : 0;

            foreach ($menus as &$menu) {
                if ($aclcheck) {
                    $action = $aclcheck == $menu->id ? 'edit' : 'create';

                    if (!$user->authorise('core.' . $action, 'com_menus.menu.' . $menu->id)) {
                        continue;
                    }
                }

                // Start group:
                $optGroup = new \stdClass();
                $optGroup->value = '<OPTGROUP>';
                $optGroup->text = $menu->text;
                static::$items[$key][] = $optGroup;

                // Special "Add to this Menu" option:
                static::$items[$key][] = HTMLHelper::_('select.option', $menu->value . '.1', Text::_('JLIB_HTML_ADD_TO_THIS_MENU'));

                // Menu items:
                if (isset($lookup[$menu->value])) {
                    foreach ($lookup[$menu->value] as &$item) {
                        static::$items[$key][] = HTMLHelper::_('select.option', $menu->value . '.' . $item->value, $item->text);
                    }
                }

                // Finish group:
                $closeOptGroup = new \stdClass();
                $closeOptGroup->value = '</OPTGROUP>';
                $closeOptGroup->text = $menu->text;

                static::$items[$key][] = $closeOptGroup;
            }
        }

        return static::$items[$key];
    }

    /**
     * Displays an HTML select list of menu items.
     *
     * @param   string  $name      The name of the control.
     * @param   string  $selected  The value of the selected option.
     * @param   string  $attribs   Attributes for the control.
     * @param   array   $config    An array of options for the control [id, published, checkacl, clientid].
     *
     * @return  string
     *
     * @since   1.6
     */
    public static function menuItemList($name, $selected = null, $attribs = null, $config = array())
    {
        static $count;

        $options = static::menuItems($config);

        return HTMLHelper::_(
            'select.genericlist',
            $options,
            $name,
            array(
                'id'             => $config['id'] ?? 'assetgroups_' . (++$count),
                'list.attr'      => $attribs ?? 'class="inputbox" size="1"',
                'list.select'    => (int) $selected,
                'list.translate' => false,
            )
        );
    }

    /**
     * Build the select list for Menu Ordering
     *
     * @param   object   $row   The row object
     * @param   integer  $id    The id for the row. Must exist to enable menu ordering
     *
     * @return  string
     *
     * @since   1.5
     */
    public static function ordering(&$row, $id)
    {
        if ($id) {
            $db = Factory::getDbo();
            $query = $db->getQuery(true)
                ->select(
                    [
                        $db->quoteName('ordering', 'value'),
                        $db->quoteName('title', 'text'),
                    ]
                )
                ->from($db->quoteName('#__menu'))
                ->where(
                    [
                        $db->quoteName('menutype') . ' = :menutype',
                        $db->quoteName('parent_id') . ' = :parent',
                        $db->quoteName('published') . ' != -2',
                    ]
                )
                ->order($db->quoteName('ordering'))
                ->bind(':menutype', $row->menutype)
                ->bind(':parent', $row->parent_id, ParameterType::INTEGER);
            $order = HTMLHelper::_('list.genericordering', $query);
            $ordering = HTMLHelper::_(
                'select.genericlist',
                $order,
                'ordering',
                array('list.attr' => 'class="inputbox" size="1"', 'list.select' => (int) $row->ordering)
            );
        } else {
            $ordering = '<input type="hidden" name="ordering" value="' . $row->ordering . '">' . Text::_('JGLOBAL_NEWITEMSLAST_DESC');
        }

        return $ordering;
    }

    /**
     * Build the multiple select list for Menu Links/Pages
     *
     * @param   boolean  $all         True if all can be selected
     * @param   boolean  $unassigned  True if unassigned can be selected
     * @param   int      $clientId    The client id
     *
     * @return  string
     *
     * @since   1.5
     */
    public static function linkOptions($all = false, $unassigned = false, $clientId = 0)
    {
        $db = Factory::getDbo();

        // Get a list of the menu items
        $query = $db->getQuery(true)
            ->select(
                [
                    $db->quoteName('m.id'),
                    $db->quoteName('m.parent_id'),
                    $db->quoteName('m.title'),
                    $db->quoteName('m.menutype'),
                    $db->quoteName('m.client_id'),
                ]
            )
            ->from($db->quoteName('#__menu', 'm'))
            ->where($db->quoteName('m.published') . ' = 1')
            ->order(
                [
                    $db->quoteName('m.client_id'),
                    $db->quoteName('m.menutype'),
                    $db->quoteName('m.parent_id'),
                ]
            );

        if (isset($clientId)) {
            $clientId = (int) $clientId;
            $query->where($db->quoteName('m.client_id') . ' = :client')
                ->bind(':client', $clientId, ParameterType::INTEGER);
        }

        $db->setQuery($query);

        $mitems = $db->loadObjectList();

        if (!$mitems) {
            $mitems = array();
        }

        // Establish the hierarchy of the menu
        $children = array();

        // First pass - collect children
        foreach ($mitems as $v) {
            $pt            = $v->parent_id;
            $list          = @$children[$pt] ? $children[$pt] : array();
            $list[]        = $v;
            $children[$pt] = $list;
        }

        // Second pass - get an indent list of the items
        $list = static::treerecurse((int) $mitems[0]->parent_id, '', array(), $children, 9999, 0, 0);

        // Code that adds menu name to Display of Page(s)
        $mitems = array();

        if ($all | $unassigned) {
            $mitems[] = HTMLHelper::_('select.option', '<OPTGROUP>', Text::_('JOPTION_MENUS'));

            if ($all) {
                $mitems[] = HTMLHelper::_('select.option', 0, Text::_('JALL'));
            }

            if ($unassigned) {
                $mitems[] = HTMLHelper::_('select.option', -1, Text::_('JOPTION_UNASSIGNED'));
            }

            $mitems[] = HTMLHelper::_('select.option', '</OPTGROUP>');
        }

        $lastMenuType = null;
        $tmpMenuType  = null;

        foreach ($list as $list_a) {
            if ($list_a->menutype != $lastMenuType) {
                if ($tmpMenuType) {
                    $mitems[] = HTMLHelper::_('select.option', '</OPTGROUP>');
                }

                $mitems[]     = HTMLHelper::_('select.option', '<OPTGROUP>', $list_a->menutype);
                $lastMenuType = $list_a->menutype;
                $tmpMenuType  = $list_a->menutype;
            }

            $mitems[] = HTMLHelper::_('select.option', $list_a->id, $list_a->title);
        }

        if ($lastMenuType !== null) {
            $mitems[] = HTMLHelper::_('select.option', '</OPTGROUP>');
        }

        return $mitems;
    }

    /**
     * Build the list representing the menu tree
     *
     * @param   integer  $id         Id of the menu item
     * @param   string   $indent     The indentation string
     * @param   array    $list       The list to process
     * @param   array    $children   The children of the current item
     * @param   integer  $maxlevel   The maximum number of levels in the tree
     * @param   integer  $level      The starting level
     * @param   int      $type       Set the type of spacer to use. Use 1 for |_ or 0 for -
     *
     * @return  array
     *
     * @since   1.5
     */
    public static function treerecurse($id, $indent, $list, &$children, $maxlevel = 9999, $level = 0, $type = 1)
    {
        if ($level <= $maxlevel && isset($children[$id]) && is_array($children[$id])) {
            if ($type) {
                $pre    = '<sup>|_</sup>&#160;';
                $spacer = '.&#160;&#160;&#160;&#160;&#160;&#160;';
            } else {
                $pre    = '- ';
                $spacer = '&#160;&#160;';
            }

            foreach ($children[$id] as $v) {
                $id = $v->id;

                if ($v->parent_id == 0) {
                    $txt = $v->title;
                } else {
                    $txt = $pre . $v->title;
                }

                $list[$id]           = $v;
                $list[$id]->treename = $indent . $txt;

                if (isset($children[$id]) && is_array($children[$id])) {
                    $list[$id]->children = count($children[$id]);
                    $list                = static::treerecurse($id, $indent . $spacer, $list, $children, $maxlevel, $level + 1, $type);
                } else {
                    $list[$id]->children = 0;
                }
            }
        }

        return $list;
    }
}
