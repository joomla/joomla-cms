<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Content component
 *
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 */
class ContentViewCategory extends JViewCategory
{
	/*
	 * @var array  Array of leading items for blog display
	 */
	protected $lead_items = array();

	/*
	 * @var array  Array of intro (multicolumn display) items for blog display
	 */
	protected $intro_items = array();

	/*
	 * @var array  Array of links in blog displayy
	 */
	protected $link_items = array();

	/*
	 * @var integer Number of columns in a multi column display
	 */
	protected $columns = 1;

	/*
	 * @var string  The name of the extension for the category
	 */
	protected $extension = 'com_content';

	/*
	 * @var  string  Default title to use for page title
	 */
	protected $defaultPageTitle = 'JGLOBAL_ARTICLES';

	/*
	 * @var string  The name of the view to link individual items to
	 */
	protected $viewName = 'article';

	public function display($tpl = null)
	{

		parent::commonCategoryDisplay();

		// PREPARE THE DATA
		// Get the metrics for the structural page layout.
		$params		= $this->params;
		$numLeading	= $params->def('num_leading_articles', 1);
		$numIntro	= $params->def('num_intro_articles', 4);
		$numLinks	= $params->def('num_links', 4);

		// Compute the article slugs and prepare introtext (runs content plugins).
		foreach ($this->items as $item)
		{
			$item->slug = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;

			$item->parent_slug = ($item->parent_alias) ? ($item->parent_id . ':' . $item->parent_alias) : $item->parent_id;

			// No link for ROOT category
			if ($item->parent_alias == 'root')
			{
				$item->parent_slug = null;
			}

			$item->catslug		= $item->category_alias ? ($item->catid.':'.$item->category_alias) : $item->catid;
			$item->event = new stdClass;

			$dispatcher = JEventDispatcher::getInstance();

			// Old plugins: Ensure that text property is available
			if (!isset($item->text))
			{
				$item->text = $item->introtext;
			}

			JPluginHelper::importPlugin('content');
			$dispatcher->trigger('onContentPrepare', array ('com_content.category', &$item, &$this->params, 0));

			// Old plugins: Use processed text as introtext
			$item->introtext = $item->text;

			$results = $dispatcher->trigger('onContentAfterTitle', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_content.category', &$item, &$item->params, 0));
			$item->event->afterDisplayContent = trim(implode("\n", $results));
		}

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and category id will match
		$app = JFactory::getApplication();
		$active	= $app->getMenu()->getActive();
		if ((!$active) || ((strpos($active->link, 'view=category') === false) || (strpos($active->link, '&id=' . (string) $this->category->id) === false)))
		{
			// Get the layout from the merged category params
			if ($layout = $category->params->get('category_layout'))
			{
				$this->setLayout($layout);
			}
		}
		// At this point, we are in a menu item, so we don't override the layout
		elseif (isset($active->query['layout']))
		{
			// We need to set the layout from the query in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

		// For blog layouts, preprocess the breakdown of leading, intro and linked articles.
		// This makes it much easier for the designer to just interrogate the arrays.
		if (($params->get('layout_type') == 'blog') || ($this->getLayout() == 'blog'))
		{
			//$max = count($this->items);

			foreach ($this->items as $i => $item)
			{
				if ($i < $numLeading)
				{
					$this->lead_items[] = $item;
				}

				elseif ($i >= $numLeading && $i < $numLeading + $numIntro)
				{
					$this->intro_items[] = $item;
				}

				elseif ($i < $numLeading + $numIntro + $numLinks)
				{
					$this->link_items[] = $item;
				}
				else
				{
					continue;
				}
				$i++;
			}

			$this->columns = max(1, $params->def('num_columns', 1));

			$order = $params->def('multi_column_order', 1);
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		parent::_prepareDocument();
		$menu = $this->menu;
		$id = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != 'com_content' || $menu->query['view'] == 'article' || $id != $this->category->id))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_content' || $menu->query['view'] == 'article' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => ContentHelperRoute::getCategoryRoute($category->id));
				$category = $category->getParent();
			}

			$path = array_reverse($path);

			foreach ($path as $item)
			{
				$this->pathway->addItem($item['title'], $item['link']);
			}
		}

		parent::addFeed();
	}
}
