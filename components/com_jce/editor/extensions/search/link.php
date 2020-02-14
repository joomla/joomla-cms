<?php

/**
 * @copyright 	Copyright (c) 2009-2019 Ryan Demmer. All rights reserved
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses
 */
defined('JPATH_PLATFORM') or die;

class WFLinkSearchExtension extends WFSearchExtension
{
    private $enabled = array();
    
    /**
     * Constructor activating the default information of the class.
     */
    public function __construct()
    {
        parent::__construct();

        $request = WFRequest::getInstance();
        $request->setRequest(array($this, 'doSearch'));

        $request->setRequest(array($this, 'getAreas'));

        $wf = WFEditorPlugin::getInstance();

        // get plugins
        $plugins = $wf->getParam('search.link.plugins', array());

        // use tested defaults
        if (empty($plugins)) {
            $plugins = array('categories', 'contacts', 'content', 'newsfeeds', 'weblinks', 'tags');
        }

        foreach ($plugins as $plugin) {
            if (JPluginHelper::isEnabled('search', $plugin)) {
                JPluginHelper::importPlugin('search', $plugin);

                $this->enabled[] = $plugin;
            }
        }
    }

    public function display()
    {
        parent::display();

        $document = WFDocument::getInstance();
        $document->addScript(array('link'), 'extensions.search.js');
        $document->addStylesheet(array('link'), 'extensions.search.css');
    }

    public function isEnabled()
    {
        $wf = WFEditorPlugin::getInstance();
        return (bool) $wf->getParam('search.link.enable', 1) && !empty($this->enabled);
    }

    /**
     * Method to get the search areas.
     */
    public function getAreas()
    {
        $app = JFactory::getApplication('site');

        $areas = array();
        $results = array();

        $searchareas = $app->triggerEvent('onContentSearchAreas');

        foreach ($searchareas as $area) {
            if (is_array($area)) {
                $areas = array_merge($areas, $area);
            }
        }

        foreach ($areas as $k => $v) {
            $results[$k] = JText::_($v);
        }

        return $results;
    }

    /*
     * Render Search fields
     * This method uses portions of SearchViewSearch::display from components/com_search/views/search/view.html.php
     * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
     */

    public function render()
    {
        if (!$this->isEnabled()) {
            return '';
        }

        // built select lists
        $orders = array();
        $orders[] = JHtml::_('select.option', 'newest', JText::_('WF_SEARCH_NEWEST_FIRST'));
        $orders[] = JHtml::_('select.option', 'oldest', JText::_('WF_SEARCH_OLDEST_FIRST'));
        $orders[] = JHtml::_('select.option', 'popular', JText::_('WF_SEARCH_MOST_POPULAR'));
        $orders[] = JHtml::_('select.option', 'alpha', JText::_('WF_SEARCH_ALPHABETICAL'));
        $orders[] = JHtml::_('select.option', 'category', JText::_('WF_CATEGORY'));

        $lists = array();
        $lists['ordering'] = JHtml::_('select.genericlist', $orders, 'ordering', 'class="inputbox"', 'value', 'text');

        $searchphrases = array();
        $searchphrases[] = JHtml::_('select.option', 'all', JText::_('WF_SEARCH_ALL_WORDS'));
        $searchphrases[] = JHtml::_('select.option', 'any', JText::_('WF_SEARCH_ANY_WORDS'));
        $searchphrases[] = JHtml::_('select.option', 'exact', JText::_('WF_SEARCH_EXACT_PHRASE'));
        $lists['searchphrase'] = JHtml::_('select.radiolist', $searchphrases, 'searchphrase', '', 'value', 'text', 'all');

        $view = $this->getView(array('name' => 'search', 'layout' => 'search'));

        $view->assign('searchareas', self::getAreas());
        $view->assign('lists', $lists);
        $view->display();
    }

    /**
     * Process search.
     *
     * @param type $query Search query
     *
     * @return array Rerach Results
     *
     * This method uses portions of SearchController::search from components/com_search/controller.php
     *
     * @copyright Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved
     */
    public function doSearch($query)
    {
        $wf = WFEditorPlugin::getInstance();
        
        $results = array();
        
        if (empty($query)) {
            return $results;
        }

        // search area
        $area = null;

        // available search areas
        $areas = $this->getAreas();

        // query using a specific plugin
        if (strpos($query, ':') !== false) {
            preg_match('#^(' . implode('|', $areas) . ')\:(.+)#', $query, $matches);

            if ($matches) {
                $area   = array($matches[1]);
                $query  = $matches[2];
            }
        }

        if (!class_exists('JSite')) {
            // Load JSite class
            JLoader::register('JSite', JPATH_SITE.'/includes/application.php');
        }

        $app = JFactory::getApplication('site');
        $filter = JFilterInput::getInstance();
        $router = $app::getRouter('site');

        // get SearchHelper
        require_once JPATH_ADMINISTRATOR.'/components/com_search/helpers/search.php';

        // get router mode
        $sef 	= (int) $wf->getParam('search.link.sef_url', 0);
        
        $limit 	= (int) $wf->getParam('search.link.limit', 50);

        // set router off so a raw url is returned by the Search plugin
        if ($router) {
            //$router->setMode(0);
        }

        // slashes cause errors, <> get stripped anyway later on. # causes problems.
        $searchword = trim(str_replace(array('#', '>', '<', '\\'), '', $filter->clean($query)));

        $ordering       = null;
        $searchphrase   = 'all';

        // if searchword enclosed in double quotes, strip quotes and do exact match
        if (substr($searchword, 0, 1) == '"' && substr($searchword, -1) == '"') {
            $searchword = substr($searchword, 1, -1);
            $searchphrase = 'exact';
        }

        // get passed through ordering
        $ordering = $app->input->post->getWord('ordering', $ordering);

        // get passed through area
        $area = $app->input->post->getCmd('areas', (array) $area);

        if (empty($area)) {
            $area = null;
        }

        // trigger search on loaded plugins
        $searches = $app->triggerEvent('onContentSearch', array(
            $searchword,
            $searchphrase,
            $ordering,
            $area,
        ));

        $rows = array();

        foreach ($searches as $search) {
            $rows = array_merge((array) $rows, (array) $search);
        }
        
        // get first 10
        $rows = array_slice($rows, 0, $limit);

        for ($i = 0, $count = count($rows); $i < $count; ++$i) {
            $row = &$rows[$i];

            $result = new StdClass;

            if ($searchphrase == 'exact') {
                $searchwords = array($searchword);
                $needle = $searchword;
            } else {
                $searchworda = preg_replace('#\xE3\x80\x80#s', ' ', $searchword);
                $searchwords = preg_split("/\s+/u", $searchworda);
                $needle = $searchwords[0];
            }

            // get anchors if any...
            $row->anchors = self::getAnchors($row->text);

            $row->text = SearchHelper::prepareSearchContent($row->text, $needle);

            // remove base url
            if (strpos($row->href, JURI::base(true)) !== false) {
                $row->href = substr_replace($row->href, '', 0, strlen(JURI::base(true)) + 1);
            }

            // remove the alias from a link
            if ((int) $wf->getParam('search.link.remove_alias', 0) && strpos($row->href, ':') !== false) {
                $row->href = preg_replace('#\:[\w-]+#ui', '', $row->href);
            }

            // convert to SEF
            if ($router && $sef) {
                $router->setMode(1);

                $url = str_replace('&amp;', '&', $row->href);

                $uri = $router->build($url);
                $url = $uri->toString();

                $row->href = str_replace('/administrator/', '/', $url);
            }

            $result->title  = $row->title;
            $result->text   = $row->text;
            $result->link   = $row->href;

            if (!empty($row->anchors)) {
                $result->anchors = $row->anchors;
            }

            $results[] = $result;
        }

        return $results;
    }

    private static function getAnchors($content)
    {
        preg_match_all('#<a([^>]+)(name|id)="([a-z]+[\w\-\:\.]*)"([^>]*)>#i', $content, $matches, PREG_SET_ORDER);

        $anchors = array();

        if (!empty($matches)) {
            foreach ($matches as $match) {
                if (strpos($match[0], 'href') === false) {
                    $anchors[] = $match[3];
                }
            }
        }

        return $anchors;
    }
}