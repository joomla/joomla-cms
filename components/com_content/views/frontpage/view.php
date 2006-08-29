<?php
/**
 * @version $Id: view.php 4814 2006-08-28 19:35:16Z Jinx $
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
 * Frontpage View class
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class ContentViewFrontpage extends JView
{ 
	/**
	 * Name of the view.
	 *
	 * @access	private
	 * @var		string
	 */
	var $_viewName = 'Frontpage';
	
	function display()
	{
		$document	= & JFactory::getDocument();
		switch ($document->getType())
		{
			case 'feed':
				$this->_displayFeed();
				break;
			default:
				$this->_displayHTML();
				break;
		}
	}

	function _displayHTML()
	{
		global $mainframe, $Itemid, $option;

		// Initialize variables
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$lang 		=& JFactory::getLanguage();

		// get menu
		$menus  =& JMenu::getInstance();
		$menu   =& $menus->getItem($Itemid);
		$params =& $menus->getParams($Itemid);

		// Request variables
		$id			= JRequest::getVar('id');
		$limit		= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart	= JRequest::getVar('limitstart', 0, '', 'int');
		
		//set data model
		$items 		= $this->get('ContentData');
		$frontpage  = new stdClass();
		$frontpage->total = count($items);

		// Create a user access object for the user
		$access					= new stdClass();
		$access->canEdit		= $user->authorize('action', 'edit', 'content', 'all');
		$access->canEditOwn		= $user->authorize('action', 'edit', 'content', 'own');
		$access->canPublish		= $user->authorize('action', 'publish', 'content', 'all');

		// parameters
		$intro				= $params->def('intro', 			4);
		$leading			= $params->def('leading', 			1);
		$links				= $params->def('link', 				4);
		$descrip			= $params->def('description', 		1);
		$descrip_image		= $params->def('description_image', 1);

		$params->def('pageclass_sfx', '');
		$params->set('intro_only', 	1);
		$params->def('page_title', 	1);
		
		if ($params->get('page_title')) {
			$params->def('header', $menu->name);
		}

		//add alternate feed link
		$link    = ampReplace(JURI::base() .'feed.php?option=com_frontpage&Itemid='.$Itemid);
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&amp;format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&amp;format=atom', 'alternate', 'rel', $attribs);

		// Set section/category description text and images for
		//TODO :: Fix this !
		if ($menu && $menu->componentid && ($descrip || $descrip_image))
		{
			switch ($menu->type)
			{
				case 'content_blog_section' :
					$section = & JTable::getInstance('section', $db);
					$section->load($menu->componentid);

					$description = new stdClass();
					$description->text = $section->description;
					$description->link = 'images/stories/'.$section->image;

					$frontpage->description = $description;
					break;

				case 'content_blog_category' :
					$category = & JTable::getInstance('category', $db);
					$category->load($menu->componentid);

					$description = new stdClass();
					$description->text = $category->description;
					$description->link = 'images/stories/'.$description->image;

					$frontpage->description = $description;
					break;
			}
		}

		$limit 		= $intro + $leading + $links;

		if ($frontpage->total <= $limit) {
			$limitstart = 0;
		}
		$i = $limitstart;

		jimport('joomla.presentation.pagination');
		$this->pagination = new JPagination($frontpage->total, $limitstart, $limit);

		$request = new stdClass();
		$request->limit      = $limit;
		$request->limitstart = $limitstart;

		$this->set('user'      , $user);
		$this->set('access'    , $access);
		$this->set('params'    , $params);
		$this->set('request'   , $request);
		$this->set('items'     , $items);
		$this->set('frontpage' , $frontpage);
		
		$this->_loadTemplate('blog');
	}

	function _displayFeed()
	{
		global $mainframe, $Itemid;

		// parameters
		$db       =& JFactory::getDBO();
		$document =& JFactory::getDocument('rss');
		$limit	  = '10';

		JRequest::setVar('limit', $limit);
		$rows = $this->get('ContentData');

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
			$item->category   	= 'frontpage';

			// loads item info into rss array
			$document->addItem( $item );
		}
	}

	function item($index = 0)
	{
		global $mainframe, $Itemid;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$dispatcher	=& JEventDispatcher::getInstance();
		
		$SiteName	= $mainframe->getCfg('sitename');
		
		$task		= JRequest::getVar( 'task' );
		
		$linkOn		= null;
		$linkText	= null;

		// Get some parameters from global configuration
		$this->params->def('link_titles',	$mainframe->getCfg('link_titles'));
		$this->params->def('author',		!$mainframe->getCfg('hideAuthor'));
		$this->params->def('createdate',	!$mainframe->getCfg('hideCreateDate'));
		$this->params->def('modifydate',	!$mainframe->getCfg('hideModifyDate'));
		$this->params->def('print',			!$mainframe->getCfg('hidePrint'));
		$this->params->def('pdf',			!$mainframe->getCfg('hidePdf'));
		$this->params->def('email',			!$mainframe->getCfg('hideEmail'));
		$this->params->def('rating',		$mainframe->getCfg('vote'));
		$this->params->def('icons',			$mainframe->getCfg('icons'));
		$this->params->def('readmore',		$mainframe->getCfg('readmore'));
		$this->params->def('back_button', 	$mainframe->getCfg('back_button'));

		// Get some item specific parameters
		$this->params->def('image',				1);
		$this->params->def('section',			0);
		$this->params->def('section_link',		0);
		$this->params->def('category',			0);
		$this->params->def('category_link',		0);
		$this->params->def('introtext',			1);
		$this->params->def('pageclass_sfx',		'');
		$this->params->def('item_title',		1);
		$this->params->def('url',				1);
		$this->params->set('image',				1);

		$this->item =& $this->items[$index];

		// Process the content preparation plugins
		$this->item->text	= ampReplace($this->item->introtext);
		JPluginHelper::importPlugin('content');
		$results = $dispatcher->trigger('onPrepareContent', array (& $this->item, & $this->params, 0));

		// Build the link and text of the readmore button
		if (($this->params->get('readmore') && @ $this->item->readmore) || $this->params->get('link_titles'))
		{
			if ($this->params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($this->item->access <= $user->get('gid'))
				{
					if ($task != 'view') {
						$Itemid = JContentHelper::getItemid($this->item->id);
					}
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$this->item->id."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}

		$this->item->readmore_link = $linkOn;
		$this->item->readmore_text = $linkText;

		$this->item->print_link = $mainframe->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$this->item->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';

		$this->item->event = new stdClass();
		$results = $dispatcher->trigger('onAfterDisplayTitle', array (& $this->item, & $this->params,0));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onBeforeDisplayContent', array (& $this->item, & $this->params, 0));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onAfterDisplayContent', array (& $this->item, & $this->params, 0));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		$this->_loadTemplate('blog_item');
	}

	function links($index = 0)
	{
		global $Itemid;

		$this->links = array_splice($this->items, $index);

		for($i = 0; $i < count($this->links); $i++)
		{
			$link =& $this->links[$i];

			$Itemid	    = JContentHelper::getItemid($link->id);
			$link->link	= sefRelToAbs('index.php?option=com_content&amp;task=view&amp;id='.$link->id.'&amp;Itemid='.$Itemid);
		}


		$this->_loadTemplate('blog_links');
	}
}
?>