<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the Newsfeeds component
 *
 * @since  1.0
 */
class NewsfeedsViewNewsfeed extends JViewLegacy
{
	/**
	 * @var     object
	 * @since   1.6
	 */
	protected $state;

	/**
	 * @var     object
	 * @since   1.6
	 */
	protected $item;

	/**
	 * @var     boolean
	 * @since   1.6
	 */
	protected $print;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 *
	 * @since   1.6
	 */
	public function display($tpl = null)
	{
		$app  = JFactory::getApplication();
		$user = JFactory::getUser();

		// Get view related request variables.
		$print = $app->input->getBool('print');

		// Get model data.
		$state = $this->get('State');
		$item  = $this->get('Item');

		if ($item)
		{
			// Get Category Model data
			$categoryModel = JModelLegacy::getInstance('Category', 'NewsfeedsModel', array('ignore_request' => true));
			$categoryModel->setState('category.id', $item->catid);
			$categoryModel->setState('list.ordering', 'a.name');
			$categoryModel->setState('list.direction', 'asc');

			// @TODO: $items is not used. Remove this line?
			$items = $categoryModel->getItems();
		}

		// Check for errors.
		// @TODO: Maybe this could go into JComponentHelper::raiseErrors($this->get('Errors'))
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseWarning(500, implode("\n", $errors));

			return false;
		}

		// Add router helpers.
		$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
		$item->catslug = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
		$item->parent_slug = $item->category_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

		// Merge newsfeed params. If this is single-newsfeed view, menu params override newsfeed params
		// Otherwise, newsfeed params override menu item params
		$params = $state->get('params');
		$newsfeed_params = clone $item->params;
		$active = $app->getMenu()->getActive();
		$temp = clone $params;

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and an newsfeed view for this feed, then the menu item params take priority
			if (strpos($currentLink, 'view=newsfeed') && strpos($currentLink, '&id=' . (string) $item->id))
			{
				// $item->params are the newsfeed params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$newsfeed_params->merge($temp);
				$item->params = $newsfeed_params;

				// Load layout from active query (in case it is an alternative menu item)
				if (isset($active->query['layout']))
				{
					$this->setLayout($active->query['layout']);
				}
			}
			else
			{
				// Current view is not a single newsfeed, so the newsfeed params take priority here
				// Merge the menu item params with the newsfeed params so that the newsfeed params take priority
				$temp->merge($newsfeed_params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-newsfeed menu item)
				if ($layout = $item->params->get('newsfeed_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that newsfeed params take priority
			$temp->merge($newsfeed_params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-newsfeed menu item)
			if ($layout = $item->params->get('newsfeed_layout'))
			{
				$this->setLayout($layout);
			}
		}

		// Check the access to the newsfeed
		$levels = $user->getAuthorisedViewLevels();

		if (!in_array($item->access, $levels) or (in_array($item->access, $levels) and (!in_array($item->category_access, $levels))))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		// Get the current menu item
		$params = $app->getParams();

		// Get the newsfeed
		$newsfeed = $item;

		$params->merge($item->params);

		try
		{
			$feed = new JFeedFactory;
			$this->rssDoc = $feed->getFeed($newsfeed->link);
		}
		catch (InvalidArgumentException $e)
		{
			$msg = JText::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED');
		}
		catch (RunTimeException $e)
		{
			$msg = JText::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED');
		}
		if (empty($this->rssDoc))
		{
			$msg = JText::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED');
		}

		$feed_display_order = $params->get('feed_display_order', 'des');

		if ($feed_display_order == 'asc')
		{
			$this->rssDoc->reverseItems();
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->params = $params;
		$this->newsfeed = $newsfeed;
		$this->state = $state;
		$this->item = $item;
		$this->user = $user;

		if (!empty($msg))
		{
			$this->msg = $msg;
		}

		$this->print = $print;

		$item->tags = new JHelperTags;
		$item->tags->getItemTags('com_newsfeeds.newsfeed', $item->id);

		// Increment the hit counter of the newsfeed.
		$model = $this->getModel();
		$model->hit();

		$this->_prepareDocument();

		return parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function _prepareDocument()
	{
		$app     = JFactory::getApplication();
		$menus   = $app->getMenu();
		$pathway = $app->getPathway();
		$title   = null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();

		if ($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		}
		else
		{
			$this->params->def('page_heading', JText::_('COM_NEWSFEEDS_DEFAULT_PAGE_TITLE'));
		}

		$title = $this->params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this newsfeed
		if ($menu && ($menu->query['option'] != 'com_newsfeeds' || $menu->query['view'] != 'newsfeed' || $id != $this->item->id))
		{
			// If this is not a single newsfeed menu item, set the page title to the newsfeed title
			if ($this->item->name)
			{
				$title = $this->item->name;
			}

			$path = array(array('title' => $this->item->name, 'link' => ''));
			$category = JCategories::getInstance('Newsfeeds')->get($this->item->catid);

			while (($menu->query['option'] != 'com_newsfeeds' || $menu->query['view'] == 'newsfeed' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => NewsfeedsHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$pathway->addItem($item['title'], $item['link']);
			}
		}

		if (empty($title))
		{
			$title = $this->item->name;
		}

		$this->setDocumentTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif ($this->params->get('menu-meta_description'))
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->item->metakey);
		}
		elseif ($this->params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params->get('robots'))
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->get('MetaTitle') == '1')
		{
			$this->document->setMetaData('title', $this->item->name);
		}

		if ($app->get('MetaAuthor') == '1')
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
	}
}
