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

class WFLinkExtension extends WFExtension
{
    /*
     *  @var varchar
     */

    private $extensions = array();
    protected static $instance;
    protected static $links = array();

    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct();

        $extensions = self::loadExtensions('links');

        // Load all link extensions
        foreach ($extensions as $link) {
            $this->extensions[] = $this->getLinkExtension($link->name);
        }

        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'getLinks'));
    }

    public static function getInstance($config = array())
    {
        if (!isset(self::$instance)) {
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    public function display()
    {
        parent::display();

        foreach ($this->extensions as $extension) {
            $extension->display();
        }
    }

    private function getLinkExtension($name)
    {
        if (array_key_exists($name, self::$links) === false || empty(self::$links[$name])) {
            $classname = 'WFLinkBrowser_' . ucfirst($name);
            // create class
            if (class_exists($classname)) {
                self::$links[$name] = new $classname();
            }
        }

        return self::$links[$name];
    }

    public function getLists()
    {
        $list = array();

        foreach ($this->extensions as $extension) {
            if ($extension->isEnabled()) {
                $list[] = $extension->getList();
            }
        }

        return $list;
    }

    public function render()
    {
        $list = $this->getLists();

        if (empty($list)) {
            return '';
        }

        $view = $this->getView(array('name' => 'links', 'layout' => 'links'));
        $view->assign('list', implode("\n", $list));
        $view->display();
    }

    private static function cleanInput($args, $method = 'string')
    {
        $filter = JFilterInput::getInstance();

        foreach ($args as $k => $v) {
            $args->$k = $filter->clean($v, $method);
            $args->$k = (string) filter_var($args->$k, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);
        }

        return $args;
    }

    public function getLinks($args)
    {
        $args = self::cleanInput($args, 'STRING');

        foreach ($this->extensions as $extension) {
            if (in_array($args->option, $extension->getOption())) {
                $items = $extension->getLinks($args);
            }
        }
        $array = array();
        $result = array();
        if (isset($items)) {
            foreach ($items as $item) {
                $array[] = array(
                    'id' => isset($item['id']) ? self::xmlEncode($item['id']) : '',
                    'url' => isset($item['url']) ? self::xmlEncode($item['url']) : '',
                    'name' => self::xmlEncode($item['name']), 'class' => $item['class'],
                );
            }
            $result = array('folders' => $array);
        }

        return $result;
    }

    /**
     * Category function used by many extensions.
     *
     * @return Category list object
     *
     * @since    1.5
     */
    public static function getCategory($section, $parent = 1)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();
        $wf = WFEditorPlugin::getInstance();

        $query = $db->getQuery(true);

        $where = array();

        $version = new JVersion();
        $language = $version->isCompatible('3.0') ? ', language' : '';

        $where[] = 'parent_id = ' . (int) $parent;
        $where[] = 'extension = ' . $db->Quote($section);

        if (!$user->authorise('core.admin')) {
            $where[] = 'access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')';
        }

        if (!$wf->checkAccess('static', 1)) {
            $where[] = 'path != ' . $db->Quote('uncategorised');
        }

        $case = '';

        if ($wf->getParam('category_alias', 1) == 1) {
            //sqlsrv changes
            $case = ', CASE WHEN ';
            $case .= $query->charLength('alias', '!=', '0');
            $case .= ' THEN ';
            $a_id = $query->castAsChar('id');
            $case .= $query->concatenate(array($a_id, 'alias'), ':');
            $case .= ' ELSE ';
            $case .= $a_id . ' END as slug';
        }

        $where[] = 'published = 1';
        $query->select('id AS slug, id AS id, title, alias, access' . $language . $case)->from('#__categories')->where($where)->order('title');

        $db->setQuery($query);

        return $db->loadObjectList();
    }

    /**
     * (Attempt to) Get an Itemid.
     *
     * @param string $component
     * @param array  $needles
     *
     * @return Category list object
     */
    public function getItemId($component, $needles = array())
    {
        $match = null;

        //require_once(JPATH_SITE . '/includes/application.php');
        $app = JApplication::getInstance('site');

        $tag = defined('JPATH_PLATFORM') ? 'component_id' : 'componentid';

        $component = JComponentHelper::getComponent($component);
        $menu = $app->getMenu('site');
        $items = $menu->getItems($tag, $component->id);

        if ($items) {
            foreach ($needles as $needle => $id) {
                foreach ($items as $item) {
                    if ((@$item->query['view'] == $needle) && (@$item->query['id'] == $id)) {
                        $match = $item->id;
                        break;
                    }
                }
                if (isset($match)) {
                    break;
                }
            }
        }

        return $match ? '&Itemid=' . $match : '';
    }

    /**
     * Translates an internal Joomla URL to a humanly readible URL.
     *
     * @param string $url Absolute or Relative URI to Joomla resource
     *
     * @return The translated humanly readible URL
     */
    public static function route($url)
    {
        $app = JApplication::getInstance('site');
        $router = $app->getRouter('site');

        if (!$router) {
            return $url;
        }

        $uri = $router->build($url);
        $url = $uri->toString();
        $url = str_replace('/administrator/', '/', $url);

        return $url;
    }

    /**
     * XML encode a string.
     *
     * @param     string    String to encode
     *
     * @return string Encoded string
     */
    private static function xmlEncode($string)
    {
        return str_replace(array('&', '<', '>', "'", '"'), array('&amp;', '&lt;', '&gt;', '&apos;', '&quot;'), $string);
    }
}

abstract class WFLinkBrowser extends WFLinkExtension
{
}
