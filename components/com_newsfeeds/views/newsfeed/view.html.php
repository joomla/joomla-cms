<?php
/**
 * version $Id$
 * @package		Joomla
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
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
		$pathway  = &$app->getPathway();
		$document = &JFactory::getDocument();

		// Get the current menu item
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();
		$params	= &$app->getParams();

		//get the newsfeed
		$newsfeed = &$this->get('data');

		//  get RSS parsed object
		$options = array();
		$options['rssUrl']		= $newsfeed->link;
		$options['cache_time']	= $newsfeed->cache_time;

		$rssDoc = &JFactory::getXMLparser('RSS', $options);

		if ($rssDoc == false) {
			$msg = JText::_('Error: Feed not retrieved');
			$app->redirect('index.php?option=com_newsfeeds&view=category&id='. $newsfeed->catslug, $msg);
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

		// Set page title
		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object($menu)) {
			$menu_params = new JParameter($menu->params);
			if (!$menu_params->get('page_title')) {
				$params->set('page_title',	$newsfeed->name);
			}
		} else {
			$params->set('page_title',	$newsfeed->name);
		}
		$document->setTitle($params->get('page_title'));

		//set breadcrumbs
		$viewname	= JRequest::getString('view');
		if ($viewname == 'categories') {
			$pathway->addItem($newsfeed->category, 'index.php?view=category&id='.$newsfeed->catslug);
		}
		$pathway->addItem($newsfeed->name, '');

		$this->assignRef('params'  , $params  );
		$this->assignRef('newsfeed', $newsfeed);

		parent::display($tpl);
	}

	function limitText($text, $wordcount)
	{
		if (!$wordcount) {
			return $text;
		}

		$texts = explode(' ', $text);
		$count = count($texts);

		if ($count > $wordcount)
		{
			$text = '';
			for ($i=0; $i < $wordcount; $i++) {
				$text .= ' '. $texts[$i];
			}
			$text .= '...';
		}

		return $text;
	}
}
?>
