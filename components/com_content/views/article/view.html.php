<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML Article View class for the Content component
 *
 * @since  1.5
 */
class ContentViewArticle extends JViewLegacy
{
	protected $jLayout;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		$this->jLayout = new JLayoutFile('article', JPATH_COMPONENT . '/layouts/article');

		$app   = JFactory::getApplication();
		$user  = JFactory::getUser();
		$input = $app->input;
		$state = $this->get('State');
		$item  = $this->get('Item');

		$this->jLayout->set('item', $item);
		$this->jLayout->set('print', $input->getBool('print'));
		$this->jLayout->set('state', $state);
		$this->jLayout->set('user', $user);
		$this->jLayout->set('print', $user);

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new JViewGenericdataexception(implode("\n", $errors), 500);
		}

		$item->tagLayout = new JLayoutFile('joomla.content.tags');

		// Add router helpers.
		$item->slug        = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
		$item->catslug     = $item->category_alias ? ($item->catid . ':' . $item->category_alias) : $item->catid;
		$item->parent_slug = $item->parent_alias ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

		// No link for ROOT category
		if ($item->parent_alias === 'root')
		{
			$item->parent_slug = null;
		}

		// TODO: Change based on shownoauth
		$item->readmore_link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));

		// Merge article params. If this is single-article view, menu params override article params
		// Otherwise, article params override menu item params
		$params       = $state->get('params');
		$active       = $app->getMenu()->getActive();
		$temp         = clone $params;

		// Check to see which parameters should take priority
		if ($active)
		{
			$currentLink = $active->link;

			// If the current view is the active item and an article view for this article, then the menu item params take priority
			if (strpos($currentLink, 'view=article') && strpos($currentLink, '&id=' . (string) $item->id))
			{
				// Load jLayout from active query (in case it is an alternative menu item)
				if (isset($active->query['jLayout']))
				{
					$this->setLayout($active->query['jLayout']);
				}
				// Check for alternative jLayout of article
				elseif ($layout = $item->params->get('article_layout'))
				{
					$this->setLayout($layout);
				}

				// $item->params are the article params, $temp are the menu item params
				// Merge so that the menu item params take priority
				$item->params->merge($temp);
			}
			else
			{
				// Current view is not a single article, so the article params take priority here
				// Merge the menu item params with the article params so that the article params take priority
				$temp->merge($item->params);
				$item->params = $temp;

				// Check for alternative layouts (since we are not in a single-article menu item)
				// Single-article menu item jLayout takes priority over alt jLayout for an article
				if ($layout = $item->params->get('article_layout'))
				{
					$this->setLayout($layout);
				}
			}
		}
		else
		{
			// Merge so that article params take priority
			$temp->merge($item->params);
			$item->params = $temp;

			// Check for alternative layouts (since we are not in a single-article menu item)
			// Single-article menu item jLayout takes priority over alt jLayout for an article
			if ($layout = $item->params->get('article_layout'))
			{
				$this->setLayout($layout);
			}
		}

		$offset = $state->get('list.offset');

		// Check the view access to the article (the model has already computed the values).
		if ($item->params->get('access-view') == false && ($item->params->get('show_noauth', '0') == '0'))
		{
			$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

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

		$item->tags = new JHelperTags;
		$item->tags->getItemTags('com_content.article', $item->id);

		if ($item->params->get('show_associations'))
		{
			$item->associations = ContentHelperAssociation::displayAssociations($item->id);
		}

		// Process the content plugins.
		JPluginHelper::importPlugin('content');
		JFactory::getApplication()->triggerEvent('onContentPrepare', array ('com_content.article', &$item, &$item->params, $offset));

		$item->event = new stdClass;
		$results = JFactory::getApplication()->triggerEvent('onContentAfterTitle', array('com_content.article', &$item, &$item->params, $offset));
		$item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = JFactory::getApplication()->triggerEvent('onContentBeforeDisplay', array('com_content.article', &$item, &$item->params, $offset));
		$item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = JFactory::getApplication()->triggerEvent('onContentAfterDisplay', array('com_content.article', &$item, &$item->params, $offset));
		$item->event->afterDisplayContent = trim(implode("\n", $results));

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->_prepareDocument($params, $item, $state);

		$this->jLayout->set('params', $params);

		$file = $this->getTemplateFile();

		if ($file)
		{
			// Fallback to old system
			return parent::display($tpl);
		}

		echo $this->jLayout->render();
	}

	/**
	 * Prepares the document.
	 *
	 * @param   object    $params  The params
	 * @param   Object    $item    The article Item
	 * @param   $state    $sate    State
	 *
	 * @return   void.
	 */
	protected function _prepareDocument($params, $item, $state)
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
			$params->def('page_heading', $params->get('page_title', $menu->title));
		}
		else
		{
			$params->def('page_heading', JText::_('JGLOBAL_ARTICLES'));
		}

		$title = $params->get('page_title', '');

		$id = (int) @$menu->query['id'];

		// If the menu item does not concern this article
		if ($menu && ($menu->query['option'] !== 'com_content' || $menu->query['view'] !== 'article' || $id != $item->id))
		{
			// If a browser page title is defined, use that, then fall back to the article title if set, then fall back to the page_title option
			$title = $item->params->get('article_page_title', $item->title ?: $title);

			$path     = array(array('title' => $item->title, 'link' => ''));
			$category = JCategories::getInstance('Content')->get($item->catid);

			while ($category && ($menu->query['option'] !== 'com_content' || $menu->query['view'] === 'article' || $id != $category->id) && $category->id > 1)
			{
				$path[]   = array('title' => $category->title, 'link' => ContentHelperRoute::getCategoryRoute($category->id));
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

		if (empty($title))
		{
			$title = $item->title;
		}

		$this->document->setTitle($title);

		if ($item->metadesc)
		{
			$this->document->setDescription($item->metadesc);
		}
		elseif ($params->get('menu-meta_description'))
		{
			$this->document->setDescription($params->get('menu-meta_description'));
		}

		if ($item->metakey)
		{
			$this->document->setMetadata('keywords', $item->metakey);
		}
		elseif ($params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetadata('robots', $params->get('robots'));
		}

		if ($app->get('MetaAuthor') == '1')
		{
			$author = $item->created_by_alias ?: $item->author;
			$this->document->setMetaData('author', $author);
		}

		$mdata = $item->metadata->toArray();

		foreach ($mdata as $k => $v)
		{
			if ($v)
			{
				$this->document->setMetadata($k, $v);
			}
		}

		// If there is a pagebreak heading or title, add it to the page title
		if (!empty($item->page_title))
		{
			$item->title = $item->title . ' - ' . $item->page_title;
			$this->document->setTitle(
				$item->page_title . ' - ' . JText::sprintf('PLG_CONTENT_PAGEBREAK_PAGE_NUM', $state->get('list.offset') + 1)
			);
		}

		if ($this->jLayout->get('print'))
		{
			$this->document->setMetaData('robots', 'noindex, nofollow');
		}
	}
}
