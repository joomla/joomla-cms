<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Wrapper
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch ( JRequest::getVar( 'task' ) )
{
	default:
		WrapperController::display();
		break;
}

/**
 * Static class to hold controller functions for the Search component
 *
 * @static
 * @author		Johan Janssens <johan.janssens@joomla.org>
 * @package		Joomla
 * @subpackage	Search
 * @since		1.5
 */
class WrapperController
{
	function display()
	{
		global $Itemid, $mainframe, $option;

		$menus = &JMenu::getInstance();
		$menu  = $menus->getItem($Itemid);

		//set page title
		$mainframe->SetPageTitle($menu->name);

		// Set the breadcrumbs
		$pathway =& $mainframe->getPathWay();
		$pathway->setItemName(1, $menu->name);

		$params = new JParameter( $menu->params );
		$params->def( 'scrolling', 'auto' );
		$params->def( 'page_title', '1' );
		$params->def( 'pageclass_sfx', '' );
		$params->def( 'header', $menu->name );
		$params->def( 'height', '500' );
		$params->def( 'height_auto', '0' );
		$params->def( 'width', '100%' );
		$params->def( 'add', '1' );
		$url = $params->def( 'url', '' );

		$row = new stdClass();
		if ( $params->get( 'add' ) )
		{
			// adds 'http://' if none is set
			if ( substr( $url, 0, 1 ) == '/' )
			{
				// relative url in component. use server http_host.
				$row->url = 'http://'. $_SERVER['HTTP_HOST'] . $url;
			}
			elseif ( !strstr( $url, 'http' ) && !strstr( $url, 'https' ) )
			{
				$row->url = 'http://'. $url;
			}
			else
			{
				$row->url = $url;
			}
		}
		else
		{
			$row->url = $url;
		}

		// auto height control
		if ( $params->def( 'height_auto' ) ) {
			$row->load = 'onload="iFrameHeight()"';
		} else {
			$row->load = '';
		}

		require_once (JPATH_COMPONENT.DS.'views'.DS.'wrapper'.DS.'view.php');
		$view = new WrapperViewWrapper();

		$view->set('params'  , $params);
		$view->set('wrapper' , $row);
		$view->display();
	}
}
?>