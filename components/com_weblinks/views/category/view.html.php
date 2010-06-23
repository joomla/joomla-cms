<?php
/**
 * version $Id$
 * @package		Joomla
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @since		1.5
 */
class WeblinksViewCategory extends JView
{
	protected $state;
	protected $items;
	protected $category;
	protected $children;
	protected $pagination;

	function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$category	= $this->get('Category');
		$children	= $this->get('Children');
		$parent 	= $this->get('Parent');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		if($category == false)
		{
			return JError::raiseWarning(404, JText::_('JGLOBAL_CATEGORY_NOT_FOUND'));
		}

		if($parent == false)
		{
			//TODO Raise error for missing parent category here
		}

		// Check whether category access level allows access.
		$user	= JFactory::getUser();
		$groups	= $user->authorisedLevels();
		if (!in_array($category->access, $groups)) {
			return JError::raiseError(403, JText::_("JERROR_ALERTNOAUTHOR"));
		}

		// Prepare the data.
		// Compute the weblink slug & link url.
		for ($i = 0, $n = count($items); $i < $n; $i++)
		{
			$item		= &$items[$i];
			$item->slug	= $item->alias ? ($item->id.':'.$item->alias) : $item->id;
			if ($item->params->get('count_clicks', $params->get('count_clicks')) == 1) {
				$item->link = JRoute::_('index.php?task=weblink.go&&id='. $item->id);
			} else {
				$item->link = $item->url;
			}
			$temp		= new JRegistry();
			$temp->loadJSON($item->params);
			$item->params = clone($params);
			$item->params->merge($temp);
		}

		$children = array($category->id => $children);

		$this->assignRef('maxLevel',	$params->get('maxLevel', -1));
		$this->assignRef('state',		$state);
		$this->assignRef('items',		$items);
		$this->assignRef('category',	$category);
		$this->assignRef('children',	$children);
		$this->assignRef('params',		$params);
		$this->assignRef('parent',		$parent);
		$this->assignRef('pagination',	$pagination);

		$this->_prepareDocument();

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument()
	{
		$app		= JFactory::getApplication();
		$menus		= $app->getMenu();
		$pathway	= $app->getPathway();
		$title 		= null;

		// Because the application sets a default page title,
		// we need to get it from the menu item itself
		$menu = $menus->getActive();
		if($menu)
		{
			$this->params->def('page_heading', $this->params->get('page_title', $menu->title));
		} else {
			$this->params->def('page_heading', JText::_('COM_WEBLINKS_DEFAULT_PAGE_TITLE'));
		}
		$id = (int) @$menu->query['id'];
		if($menu && $menu->query['view'] != 'weblink' && $id != $this->category->id)
		{
			$this->params->set('page_subheading', $this->category->title);
			$path = array($this->category->title => '');
			$category = $this->category->getParent();
			while($id != $category->id && $category->id > 1)
			{
				$path[$category->title] = WeblinksHelperRoute::getCategoryRoute($category->id);
				$category = $category->getParent();
			}
			$path = array_reverse($path);
			foreach($path as $title => $link)
			{
				$pathway->addItem($title, $link);
			}
		}

		$title = $this->params->get('page_title', '');
		if (empty($title)) {
			$title = htmlspecialchars_decode($app->getCfg('sitename'));
		}
		elseif ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', htmlspecialchars_decode($app->getCfg('sitename')), $title);
		}
		$this->document->setTitle($title);

		// Add alternate feed link
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
	}
}
