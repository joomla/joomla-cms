<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_tags
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * HTML View class for the Tags component
 *
 * @package     Joomla.Site
 * @subpackage  com_tags
 * @since       3.1
 */
class TagsViewTag extends JViewLegacy
{
	protected $state;

	protected $items;

	protected $item;

	protected $children;

	protected $pagination;

	protected $params;


	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$params		= $app->getParams();

		// Get some data from the models
		$state		= $this->get('State');
		$items		= $this->get('Items');
		$item		= $this->get('Item');
		$children	= $this->get('Children');
		$parent 	= $this->get('Parent');
		$pagination	= $this->get('Pagination');

		// Check for errors.
		if (count($errors = $this->get('Errors'))) {
			JError::raiseError(500, implode("\n", $errors));
			return false;
		}

		// Check whether access level allows access.
		// TODO: SHould already be computed in $item->params->get('access-view')
		$user	= JFactory::getUser();
		$groups	= $user->getAuthorisedViewLevels();
		if (!in_array($item->access, $groups)) {
			return JError::raiseError(403, JText::_('JERROR_ALERTNOAUTHOR'));
		}

		// Prepare the data.

		$temp = new JRegistry;
		$temp->loadString($item->params);
		$item->params = clone($params);
		$item->params->merge($temp);
		$item->params = (array) json_decode($item->params);

		$this->state      = &$state;
		$this->items      = &$items;
		$this->children   = &$children;
		$this->parent     = &$parent;
		$this->pagination = &$pagination;
		$this->user       = &$user;
		$this->item       = &$item;

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		// Check for layout override only if this is not the active menu item
		// If it is the active menu item, then the view and id will match
		$active	= $app->getMenu()->getActive();
		if ((!$active) || ((strpos($active->link, 'view=tag') === false) || (strpos($active->link, '&id=' . (string) $item->id) === false)))
		{
			if ($layout = $item->params['tag_layout']) {
			$this->setLayout($layout);
			}
		}
		elseif (isset($active->query['layout'])) {
			// We need to set the layout in case this is an alternative menu item (with an alternative layout)
			$this->setLayout($active->query['layout']);
		}

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

		if   ($menu)
		{
			//$params->def('page_heading', $item->params->get('page_title', $menu->title));
		}
		else
		{
			//$params->def('page_heading', JText::_('COM_TAGS_DEFAULT_PAGE_TITLE'));
		}

		$id = (int) @$menu->query['id'];

		if ($menu && ($menu->query['option'] != 'com_tags' /* || $id != $this->id */))
		{
			$this->params->set('page_subheading', $item->title);
		}

		$title = $this->params['page_title'];

		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$this->document->setTitle($title);

		if ($this->item->metadesc)
		{
			$this->document->setDescription($this->item->metadesc);
		}
		elseif (!$this->item->metadesc && $this->params['menu-meta_description'])
		{
			$this->document->setDescription($this->params->get('menu-meta_description'));
		}

		if ($this->item->metakey)
		{
			$this->document->setMetadata('keywords', $this->tag->metakey);
		}
		elseif (!$this->item->metakey && $this->params['menu-meta_keywords'])
		{
			$this->document->setMetadata('keywords', $this->params->get('menu-meta_keywords'));
		}

		if ($this->params['robots'])
		{
			$this->document->setMetadata('robots', $this->params->get('robots'));
		}

		if ($app->getCfg('MetaAuthor') == '1') {
			$this->document->setMetaData('author', $this->document->getMetadata('author'));
		}

		if ($this->params['metadata'])
		{
			$mdata = $this->params['metadata']->toArray();

			foreach ($mdata as $k => $v)
			{
				if ($v) {
					$this->document->setMetadata($k, $v);
				}
			}
		}

		// TODO create feed document
		// Add alternative feed link
		/*
		if ($this->params->get('show_feed_link', 1) == 1)
		{
			$link	= '&format=feed&limitstart=';
			$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=rss'), 'alternate', 'rel', $attribs);
			$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
			$this->document->addHeadLink(JRoute::_($link.'&type=atom'), 'alternate', 'rel', $attribs);
		}
		*/
	}

}
