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
		$document	= & JFactory::getDocument();
		$pathway	= & $mainframe->getPathWay();

		// get menu
		$menus  =& JMenu::getInstance();
		$menu   =& $menus->getItem($Itemid);
		
		$catid = $this->catid;

		$this->params->def('header', $menu->name);
		$this->params->def('pageclass_sfx', '');
		$this->params->def('hits', $mainframe->getCfg('hits'));
		$this->params->def('item_description', 1);
		$this->params->def('other_cat_section', 1);
		$this->params->def('other_cat', 1);
		$this->params->def('description', 1);
		$this->params->def('description_text', JText::_('WEBLINKS_DESC'));
		$this->params->def('image', -1);
		$this->params->def('weblink_icons', '');
		$this->params->def('image_align', 'right');

		// pagination parameters
		$this->params->def('display', 1);
		$this->params->def('display_num', $mainframe->getCfg('list_limit'));

		$this->params->set( 'type', 'category' );

		//add alternate feed link
		$link    = JURI::base() .'feed.php?option=com_weblinks&amp;task=category&amp;catid='.$catid.'&Itemid='.$Itemid;
		$attribs = array('type' => 'application/rss+xml', 'title' => 'RSS 2.0');
		$document->addHeadLink($link.'&format=rss', 'alternate', 'rel', $attribs);
		$attribs = array('type' => 'application/atom+xml', 'title' => 'Atom 1.0');
		$document->addHeadLink($link.'&format=atom', 'alternate', 'rel', $attribs);
		
		// Set the component name in the pathway
		$pathway->setItemName(1, JText::_('Links'));
		
		// Add pathway item based on category name
		$pathway->addItem($this->category->name, '');

		// Define image tag attributes
		if (isset ($this->category->image))
		{
			$attribs['align'] = '"'.$this->category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->category->image = mosHTML::Image('/images/stories/'.$this->category->image, JText::_('Web Links'), $attribs);
		}
		
		//create pagination
		jimport('joomla.html.pagination');
		$this->pagination = new JPagination($this->total, $this->limitstart, $this->limit);

		// icon in table display
		if ( $this->params->get( 'weblink_icons' ) <> -1 ) {
			$image = mosAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $this->params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		}

		$k = 0;
		for($i = 0; $i < count($this->items); $i++)
		{
			$item =& $this->items[$i];
			$params = new JParameter( $item->params );

			$link = sefRelToAbs( 'index.php?option=com_weblinks&task=view&catid='. $catid .'&id='. $item->id.'&Itemid='.$Itemid );
			$link = ampReplace( $link );

			$menuclass = 'category'.$this->params->get( 'pageclass_sfx' );

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

		parent::display($tpl);
	}
}
?>