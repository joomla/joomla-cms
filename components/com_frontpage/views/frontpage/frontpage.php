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

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Frontpage View class
 *
 * @static
 * @package Joomla
 * @subpackage Content
 * @since 1.5
 */
class FrontpageView
{

	function showHTML(&$model, &$access, &$menu)
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

		// parameters
		$params = & $model->getMenuParams();
		if ($params->get('page_title', 1) && $menu) {
			$header = $params->def('header', $menu->name);
		} else {
			$header = '';
		}

		$intro					= $params->def('intro', 4);
		$leading				= $params->def('leading', 1);
		$links					= $params->def('link', 4);
		$usePagination			= $params->def('pagination', 2);
		$showPaginationResults	= $params->def('pagination_results', 1);
		$descrip				= $params->def('description', 1);
		$descrip_image			= $params->def('description_image', 1);

		$params->def('pageclass_sfx', '');
		$params->set('intro_only', 1);

		// Set section/category description text and images for 
		if ($menu && $menu->componentid && ($descrip || $descrip_image))
		{
			switch ($menu->type)
			{
				case 'content_blog_section' :
					$description = & JTable::getInstance('section', $db);
					$description->load($menu->componentid);
					$description->link = 'images/stories/'.$description->image;
					break;

				case 'content_blog_category' :
					$description = & JTable::getInstance('category', $db);
					$description->load($menu->componentid);
					$description->link = 'images/stories/'.$description->image;
					break;

				default :
					$menu->componentid = 0;
					break;
			}
		}
		
		$rows = $model->getContentData();
		
		$total 		= count($rows);
		$limitstart = JRequest::getVar('limitstart', 0, '', 'int');
		$limit 		= $intro + $leading + $links;
		
		if (!$limitstart) {
			$limitstart = 0;
		}

		if ($total <= $limit) {
			$limitstart = 0;
		}
		$i = $limitstart;
		
		if (!$total) {
			return;
		}
		
		$limitstart = $limitstart ? $limitstart : 0;
		jimport('joomla.presentation.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);
		
		$columns = $params->def('columns', 2);

		if ($columns == 0) {
			$columns = 1;
		}

		$column_width = 100 / $columns; // width of each column
		$column_width = 'width="'.intval($column_width).'%"';
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'blog.php');
	}
	
	function showFeed(&$model, &$access, &$menu)
	{
		global $mainframe, $Itemid;

		// parameters
		$db       =& JFactory::getDBO();
		$document =& JFactory::getDocument('rss');
		$limit	  = '10';

		JRequest::setVar('limit', $limit);
		$rows = $model->getContentData();

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

	function showItem(&$row, &$params, &$access, $showImages = false)
	{
		global $mainframe, $hide_js;

		// Initialize some variables
		$user		=& JFactory::getUser();
		$SiteName	= $mainframe->getCfg('sitename');
		$gid		= $user->get('gid');
		$task		= JRequest::getVar( 'task' );
		$Itemid		= JRequest::getVar( 'Itemid', 9999 );
		$linkOn		= null;
		$linkText	= null;

		// Get some parameters from global configuration
		$params->def('link_titles',	$mainframe->getCfg('link_titles'));
		$params->def('author',		!$mainframe->getCfg('hideAuthor'));
		$params->def('createdate',	!$mainframe->getCfg('hideCreateDate'));
		$params->def('modifydate',	!$mainframe->getCfg('hideModifyDate'));
		$params->def('print',		!$mainframe->getCfg('hidePrint'));
		$params->def('pdf',			!$mainframe->getCfg('hidePdf'));
		$params->def('email',		!$mainframe->getCfg('hideEmail'));
		$params->def('rating',		$mainframe->getCfg('vote'));
		$params->def('icons',		$mainframe->getCfg('icons'));
		$params->def('readmore',	$mainframe->getCfg('readmore'));
		$params->def('back_button', $mainframe->getCfg('back_button'));
		$params->set('intro_only', 1);

		// Get some item specific parameters
		$params->def('image',					1);
		$params->def('section',				0);
		$params->def('section_link',		0);
		$params->def('category',			0);
		$params->def('category_link',	0);
		$params->def('introtext',			1);
		$params->def('pageclass_sfx',	'');
		$params->def('item_title',			1);
		$params->def('url',						1);

		if (!$showImages) {
			$params->set('image',	0);
		}

		// Process the content preparation plugins
		$row->text	= ampReplace($row->introtext);
		JPluginHelper::importPlugin('content');
		$results = $mainframe->triggerEvent('onPrepareContent', array (& $row, & $params, 0));

		// Build the link and text of the readmore button
		if (($params->get('readmore') && @ $row->readmore) || $params->get('link_titles'))
		{
			if ($params->get('intro_only'))
			{
				// checks if the item is a public or registered/special item
				if ($row->access <= $gid)
				{
					if ($task != 'view')
					{
						$Itemid = JContentHelper::getItemid($row->id);
					}
					$linkOn = sefRelToAbs("index.php?option=com_content&amp;task=view&amp;id=".$row->id."&amp;Itemid=".$Itemid);
					$linkText = JText::_('Read more...');
				}
				else
				{
					$linkOn = sefRelToAbs("index.php?option=com_registration&amp;task=register");
					$linkText = JText::_('Register to read more...');
				}
			}
		}
		
		$print_link = $mainframe->getCfg('live_site').'/index2.php?option=com_content&amp;task=view&amp;id='.$row->id.'&amp;Itemid='.$Itemid.'&amp;pop=1';

		$results = $mainframe->triggerEvent('onAfterDisplayTitle', array (& $row, & $params,0));
		$row->afterDisplayTitle = trim(implode("\n", $results));
		
		$results = $mainframe->triggerEvent('onBeforeDisplayContent', array (& $row, & $params, 0));
		$row->beforeDisplayContent = trim(implode("\n", $results));
		
		$results = $mainframe->triggerEvent('onAfterDisplayContent', array (& $row, & $params, 0));
		$row->afterDisplayContent = trim(implode("\n", $results));
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'blog_item.php');
	}

	function showLinks(& $rows, $links, $total, $i = 0)
	{
		require(dirname(__FILE__).DS.'tmpl'.DS.'blog_links.php');
	}
}
?>