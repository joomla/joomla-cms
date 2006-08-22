<?php
/**
* @version $Id: weblinks.html.php 4452 2006-08-10 01:03:39Z Jinx $
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

jimport( 'joomla.application.view');

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
	function __construct()
	{
		$this->setViewName('category');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}
	
	function display() 
	{	
		// Define image tag attributes
		if (isset ($this->data->category->image)) {
			$attribs['align'] = '"'.$this->data->category->image_position.'"';
			$attribs['hspace'] = '"6"';

			// Use the static HTML library to build the image tag
			$this->data->image = mosHTML::Image('/images/stories/'.$this->data->category->image, JText::_('Web Links'), $attribs);
		}
		
		$this->_loadTemplate('table');
	}

	function items( ) 
	{
		global $Itemid;
		
		if (!count( $this->data->items ) ) {
			return;
		}
		
		$catid = $this->request->catid;
		
		//create pagination
		jimport('joomla.presentation.pagination');
		$this->pagination = new JPagination($this->data->total, $this->request->limitstart, $this->request->limit);
		
		$this->data->link = "index.php?option=com_weblinks&amp;task=category&amp;catid=$catid&amp;Itemid=$Itemid";
		
		// icon in table display
		if ( $this->params->get( 'weblink_icons' ) <> -1 ) {
			$this->data->image = mosAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $this->params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		} 
		
		$k = 0;		
		for($i = 0; $i < count($this->data->items); $i++) 
		{
			$item =& $this->data->items[$i];
			$params = new JParameter( $item->params );

			$link = sefRelToAbs( 'index.php?option=com_weblinks&task=view&catid='. $catid .'&id='. $item->id );
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
			
			$item->odd   = $k;
			$item->count = $i;
			$k = 1 - $k;
		}

		$this->_loadTemplate('_table_items');
	}
}
?>