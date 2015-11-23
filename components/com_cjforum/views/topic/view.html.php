<?php
/**
 * @package     corejoomla.administrator
 * @subpackage  com_cjforum
 *
 * @copyright   Copyright (C) 2009 - 2014 corejoomla.com. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

class CjForumViewTopic extends JViewLegacy
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

		// Merge topic params. If this is single-topic view, menu params override topic params
		// Otherwise, topic params override menu item params
		$this->params = $this->state->get('params');
		$active = $app->getMenu()->getActive();
		$temp = clone ($this->params);
		$itemids = array(array('id'=>$this->item->id, 'type'=>ITEM_TYPE_TOPIC));
		
		if($this->item->params->get('access-reply'))
		{
			$replyModel = JModelLegacy::getInstance( 'reply', 'CjForumModel' );
			$this->form = $replyModel->getForm();
		}
		
		if($user->authorise('core.view.replies', 'com_cjforum.topic.'.$this->item->id))
		{
			$app->input->set('filter_order', $this->params->get('replies_ordering', 'a.created'));
			$app->input->set('filter_order_Dir', $this->params->get('replies_ordering_dir', 'asc'));
			
			$repliesModel = JModelLegacy::getInstance( 'replies', 'CjForumModel' );
			$repliesModel->setState('filter.topic_id', $this->item->id);
			
			$replies = $repliesModel->getItems();
			$pagination = $repliesModel->getPagination();
			$state = $repliesModel->getState();
			
			if(!empty($replies))
			{
				foreach ($replies as $reply)
				{
					$itemids[] = array('id'=>$reply->id, 'type'=>ITEM_TYPE_REPLY);
				}
			}
			
			$this->replies	  	= &$replies;
			$this->rstate		= &$state;
			$this->pagination 	= &$pagination;
		}
		
		$this->likes = $this->getModel()->getLikes($itemids);
		$this->thankyou = $this->getModel()->getThankyou($itemids);
		
		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));
			return false;
		}
		
		// Create a shortcut for $item.
		$item = $this->item;
		$item->tagLayout = new JLayoutFile('joomla.content.tags');
		
		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
		$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
		$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;
		
		// No link for ROOT category
		if ($item->parent_alias == 'root')
		{
			$item->parent_slug = null;
		}
		
		// TODO: Change based on shownoauth
		$item->readmore_link = JRoute::_(CjForumHelperRoute::getTopicRoute($item->slug, $item->catslug));
		
		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;
			
			// If the current view is the active item and an topic view for
			// this topic, then the menu item params take priority
			if (strpos($currentLink, 'view=topic') && (strpos($currentLink, '&id=' . (string) $item->id)))
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
				if ($layout = $item->params->get('topic_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that topic params take priority
			$temp->merge($item->params);
			$item->params = $temp;
			
			// Check for alternative layouts (since we are not in a
			// single-topic menu item)
			// Single-topic menu item layout takes priority over alt layout
			// for an topic
			if ($layout = $item->params->get('topic_layout'))
			{
				$this->setLayout($layout);
			}
		}
		
		$offset = $this->state->get('list.offset');
		
		// Check the view access to the topic (the model has already computed
		// the values).
		if ($item->params->get('access-view') != true && (($item->params->get('show_noauth') != true && $user->get('guest'))))
		{
			JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
			return;
		}
		
		if ($item->params->get('show_intro', '1') == '1')
		{
			$item->text = $item->introtext . ' ' . $item->fulltext;
		}
		elseif ($item->fulltext)
		{
			$item->text = $item->fulltext;
		}
		else
		{
			$item->text = $item->introtext;
		}
		
		$item->tags = new JHelperTags();
		$item->tags->getItemTags('com_cjforum.topic', $item->id);
		
		// Process the content plugins for topic description
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array('com_cjforum.topic',	&$item,	&$this->params,	$offset));

		$item->event = new stdClass();
		$results = $dispatcher->trigger('onContentAfterTitle', array('com_cjforum.topic', &$item, &$this->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_cjforum.topic', &$item, &$this->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));
		
		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_cjforum.topic', &$item, &$this->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// Process the content plugins for topic replies
		if(!empty($this->replies))
		{
			foreach ($this->replies as &$reply)
			{
				$reply->text = $reply->description;
				$dispatcher->trigger('onContentPrepare', array('com_cjforum.reply',	&$reply, &$this->params, 0));
			}
		}
		
		// increment the hit count if this is the first page
		if($this->state->get('list.offset') == 0)
		{
			$model = $this->getModel();
			$model->hit();
		}
		
		// remove this topic from recent new posts data.
		if(!$user->guest)
		{
			// update user read status
			$newPostsData = $app->getUserState('new_posts_data', null);
			if(isset($newPostsData[$item->id]))
			{
				unset($newPostsData[$item->id]);
				$app->setUserState('new_posts_data', $newPostsData);
			}
		}
		
		$userIds = array();
		$userIds[] = $item->created_by;
		
		if(!empty($this->replies))
		{
			foreach ($this->replies as &$reply)
			{
				$userIds[] = $reply->created_by;
			}
		}
		
		if(!empty($this->thankyou))
		{
			foreach ($this->thankyou as $thankyou)
			{
				$userIds[] = $thankyou->created_by;
				$userIds[] = $thankyou->assigned_to;
			}
		}
		
		if(!empty($userIds))
		{
			$api = new CjLibApi();
			$avatar = $this->params->get('user_avatar', 'cjforum');
			$profile = $this->params->get('avatar_component', 'cjforum');
				
			$api->prefetchUserProfiles($avatar, $userIds);
				
			if($profile != 'none' && $avatar != $profile)
			{
				$api->prefetchUserProfiles($profile, $userIds);
			}
		}
		
		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($item->params->get('pageclass_sfx'));
		
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
			$this->params->def('page_heading', JText::_('COM_CJFORUM_TOPICS'));
		}
		
		$title = $this->params->get('page_title', '');
		
		$id = (int) @$menu->query['id'];
		
		// if the menu item does not concern this topic
		if ($menu && ($menu->query['option'] != 'com_cjforum' || $menu->query['view'] != 'topic' || $id != $this->item->id))
		{
			// If this is not a single topic menu item, set the page title to
			// the topic title
			if ($this->item->title)
			{
				$title = $this->item->title;
			}
			$path = array(array('title' => $this->item->title, 'link' => ''));
			$category = JCategories::getInstance('CjForum')->get($this->item->catid);
			
			while ($category && ($menu->query['option'] != 'com_cjforum' || $menu->query['view'] == 'topic' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => CjForumHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}
			$path = array_reverse($path);
			
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
			$title = $this->item->title;
		}
		$this->document->setTitle($title);
		
		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (! $this->item->metadesc && $this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}
		
		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif (! $this->item->metakey && $this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}
		
		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}
		
		if ($app->getCfg('MetaAuthor') == '1')
		{
			$this->document->setMetaData('author', $this->item->author);
		}
		
		$mdata = $this->item->metadata->toArray();
		
		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetadata($k, $v);
			}
		}
		
		// If there is a pagebreak heading or title, add it to the page title
		if (! empty($this->item->page_title))
		{
			$this->item->title = $this->item->title . ' - ' . $this->item->page_title;
			$this->document->setTitle($this->item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $this->state->get('list.offset') + 1));
		}
		
		if ($this->print)
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}
