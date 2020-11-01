<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Registry\Registry;

/**
 * HTML View class for the Tags component
 *
 * @since  3.1
 */
class TagsViewTags extends JViewLegacy
{
	protected $state;

	protected $items;

	protected $item;

	protected $pagination;

	protected $params;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed   A string if successful, otherwise an Error object.
	 */
	public function display($tpl = null)
	{
		// Get some data from the models
		$this->state      = $this->get('State');
		$this->items      = $this->get('Items');
		$this->pagination = $this->get('Pagination');
		$this->params = $this->state->get('params');
		$this->user   = JFactory::getUser();

		// Flag indicates to not add limitstart=0 to URL
		$this->pagination->hideEmptyLimitstart = true;

		/*
		 * // Change to catch
		 * if (count($errors = $this->get('Errors'))) {
		 * JError::raiseError(500, implode("\n", $errors));
		 * return false;
		 */

		// Check whether access level allows access.
		// @todo: Should already be computed in $item->params->get('access-view')
		$groups = $this->user->getAuthorisedViewLevels();

		if (!empty($this->items))
		{
			foreach ($this->items as $itemElement)
			{
				if (!in_array($itemElement->access, $groups))
				{
					unset($itemElement);
				}

				// Prepare the data.
				$temp = new Registry($itemElement->params);
				$itemElement->params = clone $this->params;
				$itemElement->params->merge($temp);
				$itemElement->params = (array) json_decode($itemElement->params);
			}
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($this->params->get('pageclass_sfx'));

		$active = JFactory::getApplication()->getMenu()->getActive();

		// Load layout from active query (in case it is an alternative menu item)
		if ($active && isset($active->query['option']) && $active->query['option'] === 'com_tags' && $active->query['view'] === 'tags')
		{
			if (isset($active->query['layout']))
			{
				$this->setLayout($active->query['layout']);
			}
		}
		else
		{
			// Load default All Tags layout from component
			if ($layout = $this->params->get('tags_layout'))
			{
				$this->setLayout($layout);
			}
		}

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 *
	 * @return void
	 */
	protected function _prepareDocument()
	{
		$app   = JFactory::getApplication();
		$menus = $app->getMenu();
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
			$this->params->def('page_heading', JText::_('COM_TAGS_DEFAULT_PAGE_TITLE'));
		}

		if ($menu && (!isset($menu->query['option']) || $menu->query['option'] !== 'com_tags'))
		{
			$this->params->set('page_subheading', $menu->title);
		}

		// Set metadata for all tags menu item
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

		// If this is not a single tag menu item, set the page title to the tag titles
		$title = '';

		if (!empty($this->item))
		{
			foreach ($this->item as $i => $itemElement)
			{
				if ($itemElement->title)
				{
					if ($i != 0)
					{
						$title .= ', ';
					}

					$title .= $itemElement->title;
				}
			}

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

			foreach ($this->item as $itemElement)
			{
				if ($itemElement->metadesc)
				{
					$this->document->setDescription($this->item->metadesc);
				}
				elseif ($this->params->get('menu-meta_description'))
				{
					$this->document->setDescription($this->params->get('menu-meta_description'));
				}

				if ($itemElement->metakey)
				{
					$this->document->setMetadata('keywords', $this->tag->metakey);
				}
				elseif ($this->params->get('menu-meta_keywords'))
				{
					$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
				}

				if ($this->params->get('robots'))
				{
					$this->document->setMetadata('robots', $this->params->get('robots'));
				}

				if ($app->get('MetaAuthor') == '1')
				{
					$this->document->setMetaData('author', $itemElement->created_user_id);
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

		// Respect configuration Sitename Before/After for TITLE in views All Tags.
		if (!$title && ($pos = $app->get('sitename_pagetitles', 0)))
		{
			$title = $this->document->getTitle();

			if ($pos == 1)
			{
				$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
			}
			else
			{
				$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
			}

			$this->document->setTitle($title);
		}

		// Add alternative feed link
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
