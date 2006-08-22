<?php
/**
 * @version $Id: blog.html.php 4402 2006-08-06 18:22:41Z Jinx $
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
class FrontpageViewFrontpage extends JView
{
	function __construct()
	{
		$this->setViewName('frontpage');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}
	
	function display()
	{
		$document	=& JFactory::getDocument();
		
		$function = '_display'.$document->getType();
		$this->$function();
	}
	
	function _displayHTML()
	{
		global $mainframe, $Itemid, $option;

		// Initialize variables
		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();
		$document	=& JFactory::getDocument();
		$lang 		=& JFactory::getLanguage();
		
		//we also need the content language file
		$lang->load('com_content');

		$task		= JRequest::getVar('task');
		$id			= JRequest::getVar('id');
		$gid		= $user->get('gid');

		//add alternate feed link
		$link    = ampReplace($mainframe->getBaseURL() .'feed.php?option=com_frontpage&Itemid='.$Itemid);
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&amp;format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&amp;format=atom', 'alternate', 'rel', $attribs);
		
		// get menu
		$menus  =& JMenu::getInstance();
		$menu   =& $menus->getItem($Itemid);

		$intro				= $this->params->def('intro', 4);
		$leading			= $this->params->def('leading', 1);
		$links				= $this->params->def('link', 4);
		$descrip			= $this->params->def('description', 1);
		$descrip_image		= $this->params->def('description_image', 1);
		$columns 			= $this->params->def('columns', 2);
		
		$this->params->def('pagination', 2);
		$this->params->def('pagination_results', 1);
		$this->params->def('pageclass_sfx', '');
		$this->params->set('intro_only', 1);
		
		// parameters
		if ($this->params->get('page_title', 1) && $menu) {
			$this->data->header = $this->params->def('header', $menu->name);
		} 
		
		// Set section/category description text and images for 
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
					
					$this->data->description = $description;
					break;

				case 'content_blog_category' :
					$category = & JTable::getInstance('category', $db);
					$category->load($menu->componentid);
					
					$description = new stdClass();
					$description->text = $category->description;
					$description->link = 'images/stories/'.$description->image;
					
					$this->data->description = $description;
					break;
			}
		}
		
		$rows = $this->get('ContentData');
		
		$total 		= count($rows);
		$limit 		= $intro + $leading + $links;
		$limitstart = $this->request->limitstart;
		
		if ($total <= $limit) {
			$limitstart = 0;
		}
		$i = $limitstart;
		
		if (!$total) {
			return;
		}
		
		$this->data->total = $total;
		$this->items = $rows;
		
		$limitstart = $limitstart ? $limitstart : 0;
		jimport('joomla.presentation.pagination');
		$this->pagination = new JPagination($total, $limitstart, $limit);
		
		if ($columns == 0) {
			$columns = 1;
		}
		
		$column_width = 100 / $columns; // width of each column
		$column_width = 'width="'.intval($column_width).'%"';
		
		$this->params->set('column_width', $column_width);
		$this->params->set('columns', $columns);
		
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
		$SiteName	= $mainframe->getCfg('sitename');
		$gid		= $user->get('gid');
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
		$this->params->set('intro_only', 	1);

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
		$results = $mainframe->triggerEvent('onPrepareContent', array (& $this->item, & $this->params, 0));

		// Build the link and text of the readmore button
		if (($this->params->get('readmore') && @ $this->item->readmore) || $this->params->get('link_titles'))
		{
			if ($this->params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($this->item->access <= $gid)
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
		$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $this->item, & $this->params,0));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $this->item, & $this->params, 0));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));
		
		$results = $mainframe->triggerEvent('onAfterDisplayContent', array (& $this->item, & $this->params, 0));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));
		
		$this->_loadTemplate('_blog_item');
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
		
		
		$this->_loadTemplate('_blog_links');
	}
}
?>