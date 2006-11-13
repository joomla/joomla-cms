<?php
/**
* version $Id: view.php 5173 2006-09-25 18:12:39Z Jinx $
* @package Joomla
* @subpackage Newsfeeds
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
*
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Newsfeeds component
 *
 * @static
 * @package Joomla
 * @subpackage Newsfeeds
 * @since 1.0
 */
class NewsfeedsViewNewsfeed extends JView
{
	function display( $tpl = null)
	{
		global $mainframe, $Itemid;

		// check if cache directory is writeable
		$cacheDir = JPATH_BASE.DS.'cache'.DS;
		if ( !is_writable( $cacheDir ) ) {
			echo JText::_( 'Cache Directory Unwriteable' );
			return;
		}

		// Get some objects from the JApplication
		$pathway =& $mainframe->getPathWay();

		// Get the current menu item
		$menu    =& JSiteHelper::getCurrentMenuItem();
		$params  =& JSiteHelper::getMenuParams();
		
		//get the newsfeed
		$newsfeed =& $this->get('data');

		//  get RSS parsed object
		$options = array();
		$options['rssUrl']     = $newsfeed->link;
		$options['cache_time'] = $newsfeed->cache_time;

		$rssDoc = JFactory::getXMLparser('RSS', $options);

		if ( $rssDoc == false ) {
			$msg = JText::_('Error: Feed not retrieved');
			$mainframe->redirect('index.php?option=com_newsfeeds&catid='. $newsfeed->catid .'&Itemid=' . $Itemid, $msg);
			return;
		}
		$lists = array();

		// channel header and link
		$newsfeed->channel = $rssDoc->channel;

		// channel image if exists
		$newsfeed->image = $rssDoc->image;

		// items
		$newsfeed->items = $rssDoc->items;

		// feed elements
		$newsfeed->items = array_slice($newsfeed->items, 0, $newsfeed->numarticles);

		// Adds parameter handling
		$params->def( 'page_title', 1 );
		$params->def( 'header', $menu->name );
		$params->def( 'pageclass_sfx', '' );
		// Feed Display control
		$params->def( 'feed_image', 1 );
		$params->def( 'feed_descr', 1 );
		$params->def( 'item_descr', 1 );
		$params->def( 'word_count', 0 );

		if ( !$params->get( 'page_title' ) ) {
			$params->set( 'header', '' );
		}

		// Set page title per category
		$mainframe->setPageTitle( $menu->name. ' - ' .$newsfeed->name );

		// Add breadcrumb item per category
		$pathway->addItem($newsfeed->name, '');

		$this->assignRef('params'  , $params   );
		$this->assignRef('newsfeed', $newsfeed );
		$this->assignRef('category', $category );

		parent::display($tpl);
	}

	function limitText($text, $wordcount)
	{
		if(!$wordcount) {
			return $text;
		}

		$texts = explode( ' ', $text );
		$count = count( $texts );

		if ( $count > $wordcount )
		{
			$text = '';
			for( $i=0; $i < $wordcount; $i++ ) {
				$text .= ' '. $texts[$i];
			}
			$text .= '...';
		}

		return $text;
	}
}
?>