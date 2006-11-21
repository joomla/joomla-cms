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
		$document = & JFactory::getDocument();
		$pathway  = & $mainframe->getPathWay();

		// Get the paramaters of the active menu item
		$menus  = &JMenu::getInstance();
		$menu   = $menus->getItem($Itemid);
		$params = new JParameter($menu->params);

		// Get some request variables
		$filter_order		= JRequest::getVar('filter_order', 'ordering');
		$filter_order_dir	= JRequest::getVar('filter_order_Dir', 'DESC');

		// Get some data from the model
		$items      =& $this->get('data' );
		$total      =& $this->get('total');
		$pagination =& $this->get('pagination'); 
		$category   =& $this->get( 'category' );
		$category->total = $total;

		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_weblinks&amp;task=category&amp;catid='.$category->id.'&amp;Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&amp;format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&amp;format=atom', 'alternate', 'rel', $attribs);

		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('Links'));

		// Add pathway item based on category name
		$pathway->addItem($category->name, '');

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
		$params->def('display', 1);

		// Define image tag attributes
		if (isset ($category->image))
		{
			$attribs['align'] = '"'.$category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$category->image = JHTML::Image('/images/stories/'.$category->image, JText::_('Web Links'), $attribs);
		}

		// icon in table display
		if ( $params->get( 'weblink_icons' ) <> -1 ) {
			$image = JAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		}

		$k = 0;
		for($i = 0; $i < $total; $i++)
		{
			$item =& $items[$i];

			$link = sefRelToAbs( 'index.php?option=com_weblinks&amp;view=weblink&amp;id='. $item->id.'&amp;Itemid='.$Itemid );
			$link = ampReplace( $link );

			$menuclass = 'category'.$params->get( 'pageclass_sfx' );

			$itemParams = new JParameter($item->params);
			switch ($itemParams->get( 'target' ))
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

		$this->assign('total', $total);

		$this->assignRef('lists'     , $lists);
		$this->assignRef('params'    , $params);
		$this->assignRef('category'  , $category);
		$this->assignRef('items'     , $items);
		$this->assignRef('pagination', $pagination);

		parent::display($tpl);
	}
}
?>