<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$params->def('menutype', 			'mainmenu');
$params->def('class_sfx', 			'');
$params->def('menu_images', 		0);
$params->def('menu_images_align', 	0);
$params->def('expand_menu', 		0);
$params->def('activate_parent', 	0);
$params->def('indent_image', 		0);
$params->def('indent_image1', 		'indent1.png');
$params->def('indent_image2', 		'indent2.png');
$params->def('indent_image3', 		'indent3.png');
$params->def('indent_image4', 		'indent4.png');
$params->def('indent_image5', 		'indent5.png');
$params->def('indent_image6', 		'indent.png');
$params->def('spacer', 				'');
$params->def('end_spacer', 			'');
$params->def('full_active_id', 		0);

// Added in 1.5
$params->def('startLevel', 			0);
$params->def('endLevel', 			0);
$params->def('showAllChildren', 	0);

switch ( $params->get( 'menu_style', 'list' ) ) {
	case 'list_flat' :
		// Include the legacy library file
		require_once(dirname(__FILE__).DS.'legacy.php');

		mosShowHFMenu($params, 1);
		break;

	case 'horiz_flat' :
		// Include the legacy library file
		require_once(dirname(__FILE__).DS.'legacy.php');

		mosShowHFMenu($params, 0);
		break;

	case 'vert_indent' :
		// Include the legacy library file
		require_once(dirname(__FILE__).DS.'legacy.php');

		mosShowVIMenu($params);
		break;

	default :
		// Include the new menu class
		require_once(dirname(__FILE__).DS.'menu.php');

		// Initialize some variables
		$Itemid = JRequest::getVar( 'Itemid', 0, '', 'int' );
		$menu = & new JMainMenu($params, $Itemid);
		$items = JMenu::getInstance();
		
		// Get Menu Items
		$rows = $items->getItems('menutype', $params->get('menutype'));
		
		// Handle RTL
		$lang = $mainframe->getLanguage();
		if( $lang->isRTL() ) {
			$rows = array_reverse( $rows );
		}

		// Build Menu Tree
		$user =& $mainframe->getUser();
		foreach ($rows as $row) {
			if($row->access <= $user->get('gid')) {
				$menu->addNode($row);
			}
		}

		// Render Menu		
		$menu->render($params->get('menutype'), $params->get('class_sfx'));
		break;
}
?>