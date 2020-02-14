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

class JoomlalinksWeblinks extends JObject
{
    private $option = 'com_weblinks';

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
        return '<li id="index.php?option=com_weblinks&view=categories" class="folder menu nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon"></span><span class="uk-tree-text">' . JText::_('WF_LINKS_JOOMLALINKS_WEBLINKS') . '</span></a></div></li>';
    }

    public function getLinks($args)
    {
        $wf = WFEditorPlugin::getInstance();
        $items = array();

        if (!defined('JPATH_PLATFORM')) {
            require_once JPATH_SITE . '/includes/application.php';
        }

        require_once JPATH_SITE . '/components/com_weblinks/helpers/route.php';

        $language = '';

        switch ($args->view) {
            // Get all WebLink categories
            default:
            case 'categories':
                $categories = WFLinkBrowser::getCategory('com_weblinks', 1, $wf->getParam('links.joomlalinks.category_alias', 1));

                foreach ($categories as $category) {
                    $url = '';

                    if (method_exists('WeblinksHelperRoute', 'getCategoryRoute')) {
                        // language
                        if (isset($category->language)) {
                            $language = $category->language;
                        }

                        $id = WeblinksHelperRoute::getCategoryRoute($category->id, $language);

                        if (strpos($id, 'index.php?Itemid=') !== false) {
                            $url = $id;
                            $id = 'index.php?option=com_weblinks&view=category&id=' . $category->id;
                        }
                    } else {
                        $itemid = WFLinkBrowser::getItemId('com_weblinks', array('categories' => null, 'category' => $category->id));
                        $id = 'index.php?option=com_weblinks&view=category&id=' . $category->id . $itemid;
                    }

                    $items[] = array(
                        'url' => self::route($url),
                        'id' => $id,
                        'name' => $category->title . ' / ' . $category->alias,
                        'class' => 'folder weblink',
                    );
                }
                break;
            // Get all links in the category
            case 'category':
                $categories = WFLinkBrowser::getCategory('com_weblinks', $args->id, $wf->getParam('links.joomlalinks.category_alias', 1));

                if (count($categories)) {
                    foreach ($categories as $category) {
                        $children = WFLinkBrowser::getCategory('com_weblinks', $category->id, $wf->getParam('links.joomlalinks.category_alias', 1));

                        $url = '';

                        if ($children) {
                            $id = 'index.php?option=com_weblinks&view=category&id=' . $category->id;
                        } else {
                            if (method_exists('WeblinksHelperRoute', 'getCategoryRoute')) {
                                // language
                                if (isset($category->language)) {
                                    $language = $category->language;
                                }

                                $id = WeblinksHelperRoute::getCategoryRoute($category->id, $language);

                                if (strpos($id, 'index.php?Itemid=') !== false) {
                                    $url = $id;
                                    $id = 'index.php?option=com_weblinks&view=category&id=' . $category->id;
                                }
                            } else {
                                $itemid = WFLinkBrowser::getItemId('com_weblinks', array('categories' => null, 'category' => $category->id));
                                $id = 'index.php?option=com_weblinks&view=category&id=' . $category->id . $itemid;
                            }
                        }

                        $items[] = array(
                            'url' => self::route($url),
                            'id' => $id,
                            'name' => $category->title . ' / ' . $category->alias,
                            'class' => 'folder weblink',
                        );
                    }
                }

                $weblinks = self::getWeblinks($args->id);

                foreach ($weblinks as $weblink) {
                    // language
                    if (isset($weblink->language)) {
                        $language = $weblink->language;
                    }

                    $id = WeblinksHelperRoute::getWeblinkRoute($weblink->slug, $weblink->catslug, $language);

                    if (defined('JPATH_PLATFORM')) {
                        $id .= '&task=weblink.go';
                    }

                    $items[] = array(
                        'id' => self::route($id),
                        'name' => $weblink->title . ' / ' . $weblink->alias,
                        'class' => 'file',
                    );
                }
                break;
        }

        return $items;
    }

    public static function getWeblinks($id)
    {
        $wf = WFEditorPlugin::getInstance();
        
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $dbquery = $db->getQuery(true);

        $section = JText::_('Web Links');

        $query = $db->getQuery(true);

        $case = '';

        if ((int) $wf->getParam('links.joomlalinks.weblinks_alias', 1)) {
            //sqlsrv changes
            $case_when1 = ' CASE WHEN ';
            $case_when1 .= $dbquery->charLength('a.alias', '!=', '0');
            $case_when1 .= ' THEN ';
            $a_id = $dbquery->castAsChar('a.id');
            $case_when1 .= $dbquery->concatenate(array($a_id, 'a.alias'), ':');
            $case_when1 .= ' ELSE ';
            $case_when1 .= $a_id . ' END as slug';

            $case_when2 = ' CASE WHEN ';
            $case_when2 .= $dbquery->charLength('b.alias', '!=', '0');
            $case_when2 .= ' THEN ';
            $c_id = $dbquery->castAsChar('b.id');
            $case_when2 .= $dbquery->concatenate(array($c_id, 'b.alias'), ':');
            $case_when2 .= ' ELSE ';
            $case_when2 .= $c_id . ' END as catslug';

            $case .= ',' . $case_when1 . ',' . $case_when2;
        }

        $query->select('a.id AS slug, b.id AS catslug, a.title AS title, a.description AS text, a.url, a.alias, a.language' . $case);

        $query->from('#__weblinks AS a');
        $query->innerJoin('#__categories AS b ON b.id = ' . (int) $id);
        $query->where('a.catid = ' . (int) $id);

        $query->where('a.state = 1');
        
        if (!$user->authorise('core.admin')) {
            $query->where('b.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
        }

        $query->where('b.published = 1');
        $query->order('a.title');

        $db->setQuery($query, 0);

        return $db->loadObjectList();
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
