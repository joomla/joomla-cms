<?php
/**
 * version $Id$
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 *
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeed extends JView
{
	function display($tpl = null)
	{
		$app = JFactory::getApplication();

		// check if cache directory is writeable
		$cacheDir = JPATH_BASE.DS.'cache'.DS;
		if (!is_writable($cacheDir)) {
			echo JText::_('CACHE_DIRECTORY_UNWRITABLE');
			return;
		}

		// Get some objects from the JApplication
		$pathway  = $app->getPathway();
		$document = JFactory::getDocument();

		// Get the current menu item
		$menus	= $app->getMenu();
		$menu	= $menus->getActive();
		$params	= $app->getParams();

		//get the newsfeed
		$newsfeed = $this->get('data');

		$temp = new JRegistry();
		$temp->loadJSON($newsfeed->params);
		$params->merge($temp);

		//  get RSS parsed object
		$options = array();
		$options['rssUrl']		= $newsfeed->link;
		$options['cache_time']	= $newsfeed->cache_time;

		$rssDoc = JFactory::getXMLparser('RSS', $options);

		if ($rssDoc == false) {
			$msg = JText::_('COM_NEWSFEEDS_ERRORS_FEED_NOT_RETRIEVED');
			$app->redirect(NewsFeedsHelperRoute::getCategoryRoute($newsfeed->catslug), $msg);
			return;
		}
		$lists = array();

		// channel header and link
		$newsfeed->channel['title']			= $rssDoc->get_title();
		$newsfeed->channel['link']			= $rssDoc->get_link();
		$newsfeed->channel['description']	= $rssDoc->get_description();
		$newsfeed->channel['language']		= $rssDoc->get_language();

		// channel image if exists
		$newsfeed->image['url']		= $rssDoc->get_image_url();
		$newsfeed->image['title']	= $rssDoc->get_image_title();
		$newsfeed->image['link']	= $rssDoc->get_image_link();
		$newsfeed->image['height']	= $rssDoc->get_image_height();
		$newsfeed->image['width']	= $rssDoc->get_image_width();

		// items
		$newsfeed->items = $rssDoc->get_items();

		// feed elements
		$newsfeed->items = array_slice($newsfeed->items, 0, $newsfeed->numarticles);

		$this->assignRef('params'  , $params  );
		$this->assignRef('newsfeed', $newsfeed);

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
			$this->params->def('page_heading', JText::_('COM_NEWSFEEDS_DEFAULT_PAGE_TITLE'));
		}
		if($menu && $menu->query['view'] != 'newsfeed')
		{
			$id = (int) @$menu->query['id'];
			$path = array($this->newsfeed->name => '');
			$category = JCategories::getInstance('Newsfeeds')->get($this->newsfeed->catid);
			while($id != $category->id && $category->id > 1)
			{
				$path[$category->title] = NewsfeedsHelperRoute::getCategoryRoute($category->id);
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
	}
}
