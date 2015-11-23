<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewProfile extends JViewLegacy
{

	protected $item;

	protected $params;

	protected $print;

	protected $state;

	protected $user;

	public function display ($tpl = null)
	{
		$app = JFactory::getApplication();
		$user = JFactory::getUser();
		$dispatcher = JEventDispatcher::getInstance();
		
		$this->item  = $this->get('Item');
		$this->print = $app->input->getBool('print');
		$this->state = $this->get('State');
		$this->user  = &$user;
		$this->layout= $app->input->getCmd('layout');
		
		switch ($this->layout)
		{
			case 'articles':
			case 'topics':
			case 'favorites':
			case 'reputation':
			case 'activity':
			case 'questions':
			case 'answers':
			case 'polls':
			case 'votes':
			case 'quizzes':
			case 'quizResponses':
			case 'surveys':
			case 'surveyResponses':
			case 'tracks':
			case 'quotes':
				$return = $this->get(JString::ucfirst($this->layout));
				$this->items = $return->items;
				$this->pagination = $return->pagination;
				break;

			case 'summary':
			default:
				$this->summary  = $this->get('Summary');
				$this->layout = 'summary';
				break;
				
		}
		
		// Merge topic params. If this is single-topic view, menu params override topic params
		// Otherwise, topic params override menu item params
		$this->params = $this->state->get('params');
		$active = $app->getMenu()->getActive();
		$temp = clone ($this->params);
		$item = $this->item;
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;
			
			// If the current view is the active item and an topic view for
			// this topic, then the menu item params take priority
			if (strpos($currentLink, 'view=profile') && (strpos($currentLink, '&id=' . (string) $item->id)))
			{
				// Load layout from active query (in case it is an alternative
				// menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
				// Check for alternative layout of topic
				elseif ($layout = $item->params->get('topic_layout'))
				{
					$this->setLayout($layout);
				}
				
				// $item->params are the topic params, $temp are the menu item
				// params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
			}
			else
			{
				// Current view is not a single topic, so the topic params
				// take priority here
				// Merge the menu item params with the topic params so that
				// the topic params take priority
				$temp->merge($item->params);
				$item->params = $temp;
				
				// Check for alternative layouts (since we are not in a
				// single-topic menu item)
				// Single-topic menu item layout takes priority over alt
				// layout for an topic
				if ($layout = $item->params->get('profile_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that item params take priority
			$temp->merge($item->params);
			$item->params = $temp;
			
			// Check for alternative layouts (since we are not in a
			// single-topic menu item)
			// Single-topic menu item layout takes priority over alt layout
			// for an topic
			if ($layout = $item->params->get('profile_layout'))
			{
				$this->setLayout($layout);
			}
		}
		
		$offset = $this->state->get('list.offset');
		
		// Process the content plugins for topic description
		$item->text = $item->about;
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_cjforum.profile',	&$item,	&$this->params,	$offset));
		
		$item->event = new stdClass();
		$results = $dispatcher->trigger('onContentAfterTitle', array('com_cjforum.profile', &$item, &$this->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onContentBeforeIntro', array('com_cjforum.profile', &$item, &$this->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onContentAfterIntro', array('com_cjforum.profile', &$item, &$this->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));
		
		JPluginHelper::importPlugin('cjforum');
		$dispatcher->trigger('onProfilePrepareContent', array('com_cjforum.profile', &$item, &$this->params, $offset));
		
		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->item->params->get('pageclass_sfx'));
		
		$this->_prepareDocument();
		
		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument ()
	{
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$pathway = $app->getPathway();
		$title = null;
		
		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		
		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_CJFORUM_PROFILE'));
		}
		
		$title = $this->params->get('page_title', '');
		
		$id = (int) @$menu->query['id'];
		
		// if the menu item does not concern this topic
		if ($menu && ($menu->query['option'] != 'com_cjforum' || $menu->query['view'] != 'profile' || $id != $this->item->id))
		{
			// If this is not a single topic menu item, set the page title to
			// the topic title
			if ($this->item->name)
			{
				$title = $this->item->name;
			}
			
			$path = array(array('title' => $this->item->name,'link' => ''));
			
			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}
		
		// Check for empty title and add site name if param is set
		if (empty($title))
		{
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		
		if (empty($title))
		{
			$title = $this->item->name;
		}
		$this->document->setTitle($title);
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}