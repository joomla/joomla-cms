<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Content
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

jimport( 'joomla.application.view');

/**
 * HTML View class for the Content component
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewSection extends JView
{
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Section';

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function display($layout)
	{
		$document	= & JFactory::getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->_displayFeed();
				break;
			default:
				$this->_displayHTML($layout);
				break;
		}
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function _displayHTML($layout)
	{
		global $mainframe, $Itemid, $option;

		// Initialize some variables
		$user	  =& JFactory::getUser();
		$document =& JFactory::getDocument();
		$pathway  = & $mainframe->getPathWay();

		// Get some data from the model
		$categories	= & $this->get( 'Categories' );
		$items      = & $this->get( 'Content');
		$section    = & $this->get( 'Section' );
		$section->total = count($items);
		
		// Get the menu object of the active menu item
		$menus	=& JMenu::getInstance();
		$menu	=& $menus->getItem($Itemid);
		$params =& $menus->getParams($Itemid);
		
		// Request variables
		$task 	    = JRequest::getVar('task');
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		
		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_content&task='.$task.'&id='.$section->id.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// Set the page title and breadcrumbs
		$pathway->addItem($section->title, '');

		if (!empty ($menu->name)) {
			$mainframe->setPageTitle($menu->name);
		}
		
		$intro		= $params->def('intro', 	4);
		$leading	= $params->def('leading', 	1);
		$links		= $params->def('link', 		4);
		
		$params->def('empty_cat_section', 	0);
		$params->def('other_cat', 			1);
		$params->def('empty_cat', 			0);
		$params->def('cat_items', 			1);
		$params->def('pageclass_sfx', 		'');
		$params->set('intro_only', 			1);
		
		if ($section->total == 0) {
			$params->set('other_cat_section', false);
		}
		
		if ($params->def('page_title', 1)) {
			$params->def('header', $menu->name);
		}
		
		for($i = 0; $i < count($categories); $i++)
		{
			$category =& $categories[$i];
			$category->link = sefRelToAbs('index.php?option=com_content&amp;task=category&amp;sectionid='.$section->id.'&amp;id='.$category->id.'&amp;Itemid='.$Itemid);
		}
		
		$limit	= $intro + $leading + $links;
		$i		= $limitstart;
		
		jimport('joomla.presentation.pagination');
		$pagination = new JPagination(count($items), $limitstart, $limit);
		$link = 'index.php?option=com_content&amp;task=section&amp;id='.$section->id.'&amp;Itemid='.$Itemid;
		
		$request = new stdClass();
		$request->limit	 		= $limit;
		$request->limitstart	= $limitstart;
		
		$data = new stdClass();
		$data->link = $link;
		
		$this->set('data'      , $data);
		$this->set('items'     , $items);
		$this->set('request'   , $request);
		$this->set('section'   , $section);
		$this->set('categories', $categories);
		$this->set('params'    , $params);
		$this->set('user'      , $user);
		$this->set('access'    , $access);
		$this->set('pagination', $pagination);
		
		$this->_loadTemplate($layout);
	}

	function item( $i )
	{
		require_once( JPATH_COMPONENT . '/helpers/article.php' );
		JContentArticleHelper::showItem( $this, $this->items[$i], $this->access, true );
	}

	function links( $i )
	{
		require_once( JPATH_COMPONENT . '/helpers/article.php' );
		JContentArticleHelper::showLinks( $this->items[$i], $this->params->get('link'), $this->section->total, $i );
	}

	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	function _displayFeed()
	{
		$doc =& JFactory::getDocument();

		// Lets get our data from the model
		$rows = & $this->get( 'Section' );

		foreach ( $rows as $row )
		{
			// strip html from feed item title
			$title = htmlspecialchars( $row->title );
			$title = html_entity_decode( $title );

			// url link to article
			// & used instead of &amp; as this is converted by feed creator
			$itemid = JContentHelper::getItemid( $row->id );
			if ($itemid) {
				$_Itemid = '&Itemid='. $itemid;
			}

			$link = 'index.php?option=com_content&task=view&id='. $row->id . $_Itemid;
			$link = sefRelToAbs( $link );

			// strip html from feed item description text
			$description = $row->introtext;
			@$date = ( $row->created ? date( 'r', $row->created ) : '' );

			// load individual item creator class
			$item = new JFeedItem();
			$item->title 		= $title;
			$item->link 		= $link;
			$item->description 	= $description;
			$item->date			= $date;
			$item->category   	= $row->category;

			// loads item info into rss array
			$doc->addItem( $item );
		}
	}
}
?>
