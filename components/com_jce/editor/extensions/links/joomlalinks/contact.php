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

class JoomlalinksContact extends JObject
{
    private $option = 'com_contact';

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
        return '<li id="index.php?option=com_contact" class="folder contact nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon"></span><span class="uk-tree-text">' . JText::_('WF_LINKS_JOOMLALINKS_CONTACTS') . '</span></a></div></li>';
    }

    public function getLinks($args)
    {
        $items = array();
        $view = isset($args->view) ? $args->view : '';

        $language = '';

        require_once JPATH_SITE . '/components/com_contact/helpers/route.php';

        switch ($view) {
            default:
                $categories = WFLinkBrowser::getCategory('com_contact', 1, $this->get('category_alias', 1));

                foreach ($categories as $category) {
                    // language
                    if (isset($category->language)) {
                        $language = $category->language;
                    }
                    $url = ContactHelperRoute::getCategoryRoute($category->id, $language);
                    // convert to SEF
                    $url = self::route($url);

                    $items[] = array(
                        'id' => 'index.php?option=com_contact&view=category&id=' . $category->id,
                        'url' => $url,
                        'name' => $category->title . ' / ' . $category->alias,
                        'class' => 'folder contact',
                    );
                }
                break;
            case 'category':
                $categories = WFLinkBrowser::getCategory('com_contact', $args->id, $this->get('category_alias', 1));

                foreach ($categories as $category) {
                    $children = WFLinkBrowser::getCategory('com_contact', $category->id, $this->get('category_alias', 1));

                    // language
                    if (isset($category->language)) {
                        $language = $category->language;
                    }

                    if ($children) {
                        $id = ContactHelperRoute::getCategoryRoute($category->id, $language);
                    } else {
                        $id = ContactHelperRoute::getCategoryRoute($category->slug, $language);
                    }

                    // convert to SEF
                    $url = self::route($id);

                    $items[] = array(
                        'url' => $url,
                        'id' => $id,
                        'name' => $category->title . ' / ' . $category->alias,
                        'class' => 'folder content',
                    );
                }

                $contacts = self::getContacts($args->id);

                foreach ($contacts as $contact) {
                    // language
                    if (isset($contact->language)) {
                        $language = $contact->language;
                    }

                    $id = ContactHelperRoute::getContactRoute($contact->id, $args->id, $language);
                    $id = self::route($id);

                    $items[] = array(
                        'id' => $id,
                        'name' => $contact->name . ' / ' . $contact->alias,
                        'class' => 'file',
                    );
                }
                break;
        }

        return $items;
    }

    private static function route($url)
    {
        $wf = WFEditorPlugin::getInstance();
        
        if ($wf->getParam('links.joomlalinks.sef_url', 0)) {
            $url = WFLinkBrowser::route($url);
        }

        return $url;
    }

    private static function getContacts($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $query = $db->getQuery(true);
        $query->select('id, name, alias, language')->from('#__contact_details')->where(array('catid=' . (int) $id, 'published = 1'));
        
        if (!$user->authorise('core.admin')) {
            $query->where('access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');
        }

        $db->setQuery($query);

        return $db->loadObjectList();
    }
}
