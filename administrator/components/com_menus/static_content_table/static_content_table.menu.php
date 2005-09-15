<?php
/**
* @version $Id: static_content_table.menu.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

global $task;
global $cid, $menutype, $option;

switch ($task) {
	case 'static_content_table':
		// this is the new item, ie, the same name as the menu `type`
		static_content_table_menu::editCategory( 0, $menutype, $option );
		break;

	case 'edit':
		static_content_table_menu::editCategory( $cid[0], $menutype, $option );
		break;

	case 'save':
	case 'apply':
		saveMenu( $option, $task );
		break;
}
?>