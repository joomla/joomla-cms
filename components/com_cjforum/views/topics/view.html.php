<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewTopics extends JViewLegacy
{
	protected $extension = 'com_cjforum';
	protected $defaultPageTitle = 'COM_CJFORUM_TOPICS';
	protected $viewName = 'topics';

	/**
	 * Execute and display a template script.
	 *
	 * @param string $tpl
	 *        	The name of the template file to parse; automatically searches
	 *        	through the template paths.
	 *        	
	 * @return mixed A string if successful, otherwise a Error object.
	 */
	public function display ($tpl = null)
	{
		$app    = JFactory::getApplication();
		$user   = JFactory::getUser();
		$params = $app->getParams();
		$userIds = array();
		
		// Get some data from the models
		$state      = $this->get('State');
		$items      = $this->get('Items');
		$category	= $this->get('Category');
		$pagination = $this->get('Pagination');
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));
		
			return false;
		}
		
		// Escape strings for HTML output
		$this->pageclass_sfx 	= htmlspecialchars($params->get('pageclass_sfx'));
		$this->state      		= &$state;
		$this->items      		= &$items;
		$this->category			= &$category;
		$this->params     		= &$params;
		$this->pagination 		= &$pagination;
		$this->user       		= &$user;
		$this->heading	  		= JText::_('COM_CJFORUM_TOPICS');
		
		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$active = $app->getMenu()->getActive();
		
		if (isset($active->query['layout']))
		{
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}
		
		// Compute the topic slugs and prepare introtext (runs content plugins).
		foreach ($this->items as $item)
		{
			$item->event = new stdClass();
			
			$dispatcher = JEventDispatcher::getInstance();
			
			// Old plugins: Ensure that text property is available
			if (! isset($item->text))
			{
				$item->text = $item->introtext;
			}
			
			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array('com_cjforum.category', &$item, &$item->params, 0));
			
			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;
			
			$results = $dispatcher->trigger('onContentAfterTitle', array('com_cjforum.category', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));
			
			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_cjforum.category', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));
			
			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_cjforum.category', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
			
			$userIds[] = $item->created_by;
		}
		
		if(!empty($userIds))
		{
			$api = new CjLibApi();
			$avatarApp = $params->get('avatar_component', 'cjforum');
			$profileApp = $params->get('profile_component', 'cjforum');
			
			$api->prefetchUserProfiles($avatarApp, $userIds);
			
			if($avatarApp != $profileApp)
			{
				$api->prefetchUserProfiles($profileApp, $userIds);
			}
		}
		
			
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $app->getMenu()->getActive();
		
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		
		$title = $this->params->get('page_title', '');
		
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		
		$this->document->setTitle($title);
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function prepareDocument ()
	{
		$app			= JFactory::getApplication();
		$menus			= $app->getMenu();
		$menu 			= $app->getMenu()->getActive();
		$this->pathway 	= $app->getPathway();
		$title         	= null;
		
		// Because the application sets a default page title, we need to get it from the menu item itself
		$this->menu = $menus->getActive();
		
		if ($this->menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $this->menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_($this->defaultPageTitle));
		}
		
		$title = $this->params->get('page_title', '');
		
		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
		}
		
		$this->document->setTitle($title);
		
		if ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		
		if ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		$id = (int) @$menu->query['id'];
		
		if ($menu && ($menu->query['option'] != 'com_cjforum' || $menu->query['view'] == 'question' || (!empty($this->category->id) && $id != $this->category->id) ))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();
			
			while (($menu->query['option'] != 'com_cjforum' || $menu->query['view'] == 'question' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => CjForumHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}
			
			$path = array_reverse($path);
			
			foreach ($path as $item)
			{
				$this->pathway->addItem($item['title'], $item['link']);
			}
		}
		
		$this->addFeed();
	}
	
	protected function addFeed()
	{
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link    = '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link . '&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
