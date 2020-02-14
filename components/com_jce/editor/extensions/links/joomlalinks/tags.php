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

class JoomlalinksTags extends JObject
{
    private $option = 'com_tags';

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
        return '<li id="index.php?option=com_tags" class="folder content nolink"><div class="uk-tree-row"><a href="#"><span class="uk-tree-icon"></span><span class="uk-tree-text">' . JText::_('WF_LINKS_JOOMLALINKS_TAGS') . '</span></a></div></li>';
    }

    public function getLinks($args)
    {
        require_once JPATH_SITE . '/components/com_tags/helpers/route.php';

        $items = array();
        $view = isset($args->view) ? $args->view : '';

        $language = '';

        $tags = array();

        if (!isset($args->id)) {
            $args->id = 1;
        }

        // get any articles in this category (in Joomla! 1.6+ a category can contain sub-categories and articles)
        $tags = self::getTags($args->id);

        if (!empty($tags)) {
            // output article links
            foreach ($tags as $tag) {
                if (isset($tag->language)) {
                    $language = $tag->language;
                }

                $id = TagsHelperRoute::getTagRoute($tag->id);

                $id = $this->route($id);

                $items[] = array(
                    'id' => $id,
                    'name' => $tag->title . ' / ' . $tag->alias,
                    'class' => 'file',
                );
            }
        }

        return $items;
    }

    private static function getTags($id)
    {
        $db = JFactory::getDBO();
        $user = JFactory::getUser();

        $wf = WFEditorPlugin::getInstance();

        $query = $db->getQuery(true);
        $query->select('a.id, a.title, a.alias');

        if ($wf->getParam('links.joomlalinks.tag_alias', 1)) {    
            $case_when_item_alias = ' CASE WHEN ';
            $case_when_item_alias .= $query->charLength('a.alias', '!=', '0');
            $case_when_item_alias .= ' THEN ';
            $a_id = $query->castAsChar('a.id');
            $case_when_item_alias .= $query->concatenate(array($a_id, 'a.alias'), ':');
            $case_when_item_alias .= ' ELSE ';
            $case_when_item_alias .= $a_id . ' END as slug';
            $query->select($case_when_item_alias);
        }

        $query->from('#__tags AS a');
        $query->where('a.alias <> ' . $db->quote('root'));
        $query->where($db->qn('a.published') . ' = 1');

        if (!$user->authorise('core.admin')) {
            $groups = implode(',', $user->getAuthorisedViewLevels());
            $query->where('a.access IN (' . $groups . ')');
        }

        if (JLanguageMultilang::isEnabled()) {
            $tag = JFactory::getLanguage()->getTag();
            $query->where('a.language in (' . $db->quote($tag) . ',' . $db->quote('*') . ')');
        }

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
