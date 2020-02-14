<?php

/**
 * @copyright     Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license       GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class JoomlalinksMenu extends JObject
{
    private $option = 'com_menu';

    /**
     * Returns a reference to a editor object.
     *
     * This method must be invoked as:
     *         <pre>  $browser =JContentEditor::getInstance();</pre>
     *
     * @return JCE The editor object
     *
     * @since    1.5
     */
    public static function getInstance($options = array())
    {
        static $instance;

        if (!is_object($instance)) {
            $instance = new self($options);
        }

        return $instance;
    }

    public function getOption()
    {
        return $this->option;
    }

    public function getList()
    {
        return '<li id="index.php?option=com_menu" class="folder menu nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon"></span><span class="uk-tree-text">' . JText::_('WF_LINKS_JOOMLALINKS_MENU') . '</span></a></div></li>';
    }

    public function getLinks($args)
    {
        $items = array();
        $view = isset($args->view) ? $args->view : '';
        switch ($view) {
            // create top-level (non-linkable) menu types
            default:
                $types = self::getMenuTypes();
                foreach ($types as $type) {
                    $items[] = array(
                        'id' => 'index.php?option=com_menu&view=menu&type=' . $type->id,
                        'name' => $type->title,
                        'class' => 'folder menu nolink',
                    );
                }
                break;
            // get menus and sub-menus
            case 'menu':
                $type = isset($args->type) ? $args->type : 0;
                $id = $type ? 0 : $args->id;

                $menus = self::getMenu($id, $type);

                foreach ($menus as $menu) {
                    $class = array();

                    // bypass errors in menu parameters syntax
                    try {
                        $params = new JRegistry($menu->params);
                    } catch (Exception $e) {
                        $params = new JRegistry();
                    }

                    switch ($menu->type) {
                        case 'separator':
                            if (!$menu->link) {
                                $class[] = 'nolink';
                            }

                            $link = '';
                            break;

                        case 'alias':
                            // If this is an alias use the item id stored in the parameters to make the link.
                            $link = 'index.php?Itemid=' . $params->get('aliasoptions');
                            break;

                        default:
                            // resolve link
                            $link = $this->resolveLink($menu);
                            break;
                    }

                    $children = (int) self::getChildren($menu->id);
                    $title = isset($menu->name) ? $menu->name : $menu->title;

                    if ($children) {
                        $class = array_merge($class, array('folder', 'menu'));
                    } else {
                        $class[] = 'file';
                    }

                    if ($params->get('secure')) {
                        $link = self::toSSL($link);
                    }

                    // language
                    if (isset($menu->language)) {
                        $link .= $this->getLangauge($menu->language);
                    }

                    $items[] = array(
                        'id' => $children ? 'index.php?option=com_menu&view=menu&id=' . $menu->id : $link,
                        'url' => self::route($link),
                        'name' => $title . ' / ' . $menu->alias,
                        'class' => implode(' ', $class),
                    );
                }
                break;
            // get menu items
            case 'submenu':
                $menus = self::getMenu($args->id);
                foreach ($menus as $menu) {
                    if ($menu->type == 'menulink') {
                        //$menu = AdvlinkMenu::_alias($menu->id);
                    }

                    $children = (int) self::getChildren($menu->id);

                    $title = isset($menu->name) ? $menu->name : $menu->title;

                    // get params
                    $params = new JRegistry($menu->params);

                    // resolve link
                    $link = $this->resolveLink($menu);

                    // language
                    if (isset($menu->language)) {
                        $link .= $this->getLangauge($menu->language);
                    }

                    if ($params->get('secure')) {
                        $link = self::toSSL($link);
                    }

                    $items[] = array(
                        'id' => self::route($link),
                        'name' => $title . ' / ' . $menu->alias,
                        'class' => $children ? 'folder menu' : 'file',
                    );
                }
                break;
        }

        return $items;
    }

    /**
     * Convert link to SSL.
     *
     * @param type $link
     *
     * @return string
     */
    private static function toSSL($link)
    {
        if (strcasecmp(substr($link, 0, 4), 'http') && (strpos($link, 'index.php?') !== false)) {
            $uri = JURI::getInstance();

            // Get prefix
            $prefix = $uri->toString(array('host', 'port'));

            // trim slashes
            $link = trim($link, '/');

            // Build the URL.
            $link = 'https://' . $prefix . '/' . $link;
        }

        return $link;
    }

    private function resolveLink($menu)
    {
        // get link from menu object
        $link = $menu->link;

        // internal link
        if ($link && strpos($link, 'index.php') === 0) {
            if ((int) $this->get('menu_resolve_alias', 1)) {
                // no Itemid
                if (strpos($link, 'Itemid=') === false) {
                    $link .= '&Itemid=' . $menu->id;
                }
                // short link
            } else {
                $link = 'index.php?Itemid=' . $menu->id;
            }
        }

        return $link;
    }

    private static function getMenuTypes()
    {
        $db = JFactory::getDBO();

        $query = $db->getQuery(true);

        $query->select('*')->from('#__menu_types')->where('client_id = 0')->order('title');

        $db->setQuery($query, 0);

        return $db->loadObjectList();
    }

    private static function getAlias($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $query = $db->getQuery(true);

        $query->select('params')->from('#__menu')->where('id = ' . (int) $id);

        $db->setQuery($query, 0);
        $params = new JRegistry($db->loadResult());

        $query->clear();
        $query->select('id, name, link, alias')->from('#__menu')->where(array('published = 1', 'id = ' . (int) $params->get('menu_item')));
        
        if (!$user->authorise('core.admin')) {
            $query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
        }
        
        $query->order('name');

        $db->setQuery($query, 0);

        return $db->loadObject();
    }

    private static function getChildren($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $query = $db->getQuery(true);

        $query->select('COUNT(id)')->from('#__menu')->where(array('published = 1', 'client_id = 0'));
        
        if (!$user->authorise('core.admin')) {
            $query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
        }

        if ($id) {
            $query->where('parent_id = ' . (int) $id);
        }

        $db->setQuery($query, 0);

        return $db->loadResult();
    }

    private static function getMenu($parent = 0, $type = 0)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $query = $db->getQuery(true);

        $query->select('m.*')->from('#__menu AS m');

        if ($type) {
            $query->innerJoin('#__menu_types AS s ON s.id = ' . (int) $type);
            $query->where('m.menutype = s.menutype');
        }

        if ($parent == 0) {
            $parent = 1;
        }

        $query->where(array('m.published = 1', 'm.parent_id = ' . (int) $parent));

        if (!$user->authorise('core.admin')) {
            $query->where('m.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
        }

        // only site menu items
        $query->where('m.client_id = 0');

        $query->order('m.lft ASC');

        $db->setQuery($query, 0);

        return $db->loadObjectList();
    }

    private function getLangauge($language)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);

        $link = '';

        $query->select('a.sef AS sef');
        $query->select('a.lang_code AS lang_code');
        $query->from('#__languages AS a');
        $db->setQuery($query);
        $langs = $db->loadObjectList();

        foreach ($langs as $lang) {
            if ($language == $lang->lang_code) {
                $language = $lang->sef;
                $link .= '&lang=' . $language;
            }
        }
        return $link;
    }

    private static function route($url)
    {
        $wf = WFEditorPlugin::getInstance();
        
        if ($wf->getParam('links.joomlalinks.sef_url', 0)) {
            $url = WFLinkBrowser::route($url);
        }

        return $url;
    }
}
