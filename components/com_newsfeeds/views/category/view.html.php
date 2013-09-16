<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Newsfeeds component
 *
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 * @since       1.0
 */
class NewsfeedsViewCategory extends JViewCategory
{
	/*
	 * @var  string  Default title to use for page title
	*/
	protected $defaultPageTitle = 'COM_NEWSFEEDS_DEFAULT_PAGE_TITLE';

	/*
	 * @var string  The name of the extension for the category
	*/
	protected $extension = 'com_newsfeeds';

	/*
	 * @var string  The name of the view to link individual items to
	*/
	protected $viewName = 'newsfeed';


	public function display($tpl = null)
	{
		parent::commonCategoryDisplay();

		// Prepare the data.
		// Compute the newsfeed slug.
		foreach ($this->items as $item)
		{
			$item->slug	= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
			$temp		= new JRegistry;
			$temp->loadString($item->params);
			$item->params = clone($this->params);
			$item->params->merge($temp);
		}

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		parent::_prepareDocument();
		$id = (int) @$menu->query['id'];

		$menu = $this->menu;

		if ($menu && ($menu->query['option'] != 'com_newsfeeds' || $menu->query['view'] == 'newsfeed' || $id != $this->category->id))
		{
			$path = array(array('title' => $this->category->title, 'link' => ''));
			$category = $this->category->getParent();

			while (($menu->query['option'] != 'com_newsfeeds' || $menu->query['view'] == 'newsfeed' || $id != $category->id) && $category->id > 1)
			{
				$path[] = array('title' => $category->title, 'link' => NewsfeedsHelperRoute::getCategoryRoute($category->id));
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
