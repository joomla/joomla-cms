<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewCategory extends JView
{
	function display( $tpl = null )
	{
		global $mainframe, $Itemid, $option;

		// Initialize some variables
		$db			= & JFactory::getDBO();
		$user		= & JFactory::getUser();
		$document	= & JFactory::getDocument();
		$gid		= $user->get('gid');
		$page		= '';

		// Get the paramaters of the active menu item
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getItem($Itemid);
		$params = new JParameter($menu->params);
		
		// Get some request variables
		$limit				= JRequest::getVar('limit', $params->get('display_num'), '', 'int');
		$limitstart			= JRequest::getVar('limitstart', 0, '', 'int');
		$filter_order		= JRequest::getVar('filter_order', 'ordering');
		$filter_order_dir	= JRequest::getVar('filter_order_Dir', 'DESC');
		$catid				= JRequest::getVar( 'catid', 0, '', 'int' );

		// Ordering control
		$orderby = "\n ORDER BY $filter_order $filter_order_dir, ordering";

		$query = "SELECT COUNT(id) as numitems" .
				"\n FROM #__weblinks" .
				"\n WHERE catid = ". (int)$catid .
				"\n AND published = 1";
		$db->setQuery($query);
		$counter = $db->loadObjectList();

		$total = $counter[0]->numitems;
		// Always set at least a default of viewing 5 at a time
		$limit = $limit ? $limit : 5;

		if ($total <= $limit) {
			$limitstart = 0;
		}

		// We need to get a list of all weblinks in the given category
		$query = "SELECT id, url, title, description, date, hits, params, catid" .
				"\n FROM #__weblinks" .
				"\n WHERE catid = $catid" .
				"\n AND published = 1" .
				"\n AND archived = 0".$orderby;
		$db->setQuery($query, $limitstart, $limit);
		$weblinks = $db->loadObjectList();

		// current category info
		$query = "SELECT id, name, description, image, image_position" .
				"\n FROM #__categories" .
				"\n WHERE id = $catid" .
				"\n AND section = 'com_weblinks'" .
				"\n AND published = 1" .
				"\n AND access <= $gid";
		$db->setQuery($query);
		$category = $db->loadObject();

		// Check to see if the category is published or if access level allows access
		if (!$category->name) {
			JError::raiseError( 404, JText::_( 'You need to login.' ));
			return;
		}

		// table ordering
		if ($filter_order_dir == 'DESC') {
			$lists['order_Dir'] = 'ASC';
		} else {
			$lists['order_Dir'] = 'DESC';
		}
		$lists['order'] = $filter_order;
		$selected = '';
		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$params->def('header', $menu->name);
		$params->def('pageclass_sfx', '');
		$params->def('hits', $contentConfig->get('hits'));
		$params->def('item_description', 1);
		$params->def('other_cat_section', 1);
		$params->def('other_cat', 1);
		$params->def('description', 1);
		$params->def('description_text', JText::_('WEBLINKS_DESC'));
		$params->def('image', -1);
		$params->def('weblink_icons', '');
		$params->def('image_align', 'right');

		// pagination parameters
		$params->def('display', 1);
		$params->def('display_num', $mainframe->getCfg('list_limit'));

		$params->set( 'type', 'category' );

		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_weblinks&amp;task=category&amp;catid='.$catid.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		//$document->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		//$document->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);
		
		$pathway = & $mainframe->getPathWay();
		
		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('Links'));
		
		// Add pathway item based on category name
		$pathway->addItem($category->name, '');

		// Define image tag attributes
		if (isset ($category->image))
		{
			$attribs['align'] = '"'.$category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$category->image = JHTML::Image('/images/stories/'.$category->image, JText::_('Web Links'), $attribs);
		}
		
		//create pagination
		jimport('joomla.html.pagination');
		$pagination = new JPagination($total, $limitstart, $limit);

		// icon in table display
		if ( $params->get( 'weblink_icons' ) <> -1 ) {
			$image = JAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		}

		$k = 0;
		for($i = 0; $i < count($weblinks); $i++)
		{
			$item =& $weblinks[$i];

			$link = sefRelToAbs( 'index.php?option=com_weblinks&view=weblink&id='. $item->id.'&Itemid='.$Itemid );
			$link = ampReplace( $link );

			$menuclass = 'category'.$params->get( 'pageclass_sfx' );

			switch ($params->get( 'target' ))
			{
				// cases are slightly different
				case 1:
					// open in a new window
					$item->link = '<a href="'. $link .'" target="_blank" class="'. $menuclass .'">'. $item->title .'</a>';
					break;

				case 2:
					// open in a popup window
					$item->link  = "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">". $item->title ."</a>\n";
					break;

				default:
					// formerly case 2
					// open in parent window
					$item->link  = '<a href="'. $link .'" class="'. $menuclass .'">'. $item->title .'</a>';
					break;
			}
			
			$item->image = $image;

			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}
		
		$this->assign('total', count($weblinks));
		$this->assign('catid', $catid);
		$this->assign('limit', $limit);
		$this->assign('limitstart', $limitstart);

		$this->assignRef('lists'     , $lists);
		$this->assignRef('params'    , $params);
		$this->assignRef('category'  , $category);
		$this->assignRef('items'     , $weblinks);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}
}
?>