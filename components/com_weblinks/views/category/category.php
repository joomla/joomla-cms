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

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package Joomla
 * @subpackage Weblinks
 * @since 1.0
 */
class WeblinksViewCategory 
{

	/**
	 * Displays a web link category
	 *
	 * @param array $rows An array of weblinks to display
	 * @param int $catid Category id of the current category
	 * @param object $category Category model of the current category
	 * @param object $params Parameters object for the current category
	 * @param array $tabclass Two element array of the two CSS classes used for alternating rows in a table
	 */
	function show( &$rows, $catid, &$category, &$params, &$lists, &$page ) {
		require(dirname(__FILE__).DS.'tmpl'.DS.'category.php');
	}

	/**
	 * Helper function to display a table of web link items
	 *
	 * @param object $params Parameters object
	 * @param array $rows Array of web link objects to show
	 * @param int $catid Category id of the web link category to show
	 * @param array $tabclass Two element array with the CSS classnames of the alternating table rows
	 * @since 1.0
	 */
	function showItems( &$params, &$rows, $catid, &$lists, &$page  ) 
	{
		global $Itemid;

		// icon in table display
		if ( $params->get( 'weblink_icons' ) <> -1 ) {
			$image = mosAdminMenus::ImageCheck( 'weblink.png', '/images/M_images/', $params->get( 'weblink_icons' ), '/images/M_images/', 'Link', 'Link' );
		} else {
			$image = NULL;
		}
		
		$count = count($rows);
		
		$k = 0;		
		for($i=0; $i<$count; $i++) 
		{
			$iparams = new JParameter( $rows[$i]->params );

			$link = sefRelToAbs( 'index.php?option=com_weblinks&task=view&catid='. $catid .'&id='. $rows[$i]->id );
			$link = ampReplace( $link );

			$menuclass = 'category'.$params->get( 'pageclass_sfx' );

			switch ($iparams->get( 'target' )) 
			{
				// cases are slightly different
				case 1:
					// open in a new window
					$rows[$i]->link = '<a href="'. $link .'" target="_blank" class="'. $menuclass .'">'. $rows[$i]->title .'</a>';
					break;

				case 2:
					// open in a popup window
					$rows[$i]->link  = "<a href=\"#\" onclick=\"javascript: window.open('". $link ."', '', 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,width=780,height=550'); return false\" class=\"$menuclass\">". $rows[$i]->title ."</a>\n";
					break;

				default:	
					// formerly case 2
					// open in parent window
					$rows[$i]->link  = '<a href="'. $link .'" class="'. $menuclass .'">'. $rows[$i]->title .'</a>';
					break;
			}
			
			$rows[$i]->odd   = $k;
			$rows[$i]->count = $i;
			$k = 1 - $k;
		}

		require(dirname(__FILE__).DS.'tmpl'.DS.'table.php');	
	}
}
?>