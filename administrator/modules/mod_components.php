<?php
/**
* @version $Id: mod_components.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

// cache some acl checks
$canConfig = $acl->acl_check( 'com_config', 'manage', 'users', $my->usertype );

$manageTemplates 	= $acl->acl_check( 'com_templates', 'manage', 'users', $my->usertype );
$installModules 	= $acl->acl_check( 'com_modules', 'install', 'users', $my->usertype );
$editAllModules 	= $acl->acl_check( 'com_modules', 'edit', 'users', $my->usertype );
$installComponents 	= $acl->acl_check( 'com_installer', 'all', 'users', $my->usertype );
$editAllComponents 	= $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', 'all' );
$canMassMail 		= $acl->acl_check( 'com_massmail', 'manage', 'users', $my->usertype );
$canManageUsers 	= $acl->acl_check( 'com_users', 'manage', 'users', $my->usertype );

// parameters
if ( ( $task == 'listcomponents' ) && ( $option == 'com_components' ) ) {
	$list_num 	= 1000;
	$display 	= 1;
} else {
	$list_num = intval( $params->get( 'list_num', 15 ) );
	$display 	= 0;
}

$query = "SELECT *"
. "\n FROM #__components"
. "\n ORDER BY ordering, name"
;
$database->setQuery( $query );
$comps = $database->loadObjectList();	// component list

$subs = array();	// sub menus

// first pass to collect sub-menu items
foreach ($comps as $row) {
	if ($row->parent) {
		if (!array_key_exists( $row->parent, $subs )) {
			$subs[$row->parent] = array();
		} // if
		$subs[$row->parent][] = $row;
	} // if
} // foreach

$topLevelLimit = $list_num;

$i 		= 0;
$z 		= 0;
$show 	= 0;
foreach ($comps as $row) {
	if ( $editAllComponents | $acl->acl_check( 'administration', 'edit', 'users', $my->usertype, 'components', $row->option ) ) {
		if ($row->parent == 0 && (trim( $row->admin_menu_link ) || array_key_exists( $row->id, $subs ))) {
			if ($i < $topLevelLimit) {
				if ($i < $topLevelLimit ) {
					$i++;
					if ($row->admin_menu_link) {
						$link = htmlspecialchars( $row->admin_menu_link, ENT_QUOTES );
						$rows[$z]->link = $link;
					} else {
						$rows[$z]->link = '#';
					}
					$name = htmlspecialchars( $row->name, ENT_QUOTES );
					$rows[$z]->name = $name;
					$rows[$z]->type	= 'main';
					$z++;

					if (array_key_exists( $row->id, $subs )) {
						foreach ($subs[$row->id] as $sub) {
					    if ($sub->admin_menu_link) {
					    	$link = htmlspecialchars($sub->admin_menu_link, ENT_QUOTES);
								$rows[$z]->link = $link;
					    } else {
								$rows[$z]->link = '#';
					    }
					    $name 				= htmlspecialchars( $sub->name );
							$rows[$z]->name 	= $name;
							$rows[$z]->type		= 'sub';
							$z++;
						}
					}
				}
			} else {
				if ($i == $topLevelLimit) {
					$show = 1;
				}
			}
		}
	}
}
mod_componentsScreens::view( $rows, $show, $display );


class mod_componentsScreens {
	function view( &$rows, $show, $display ) {
		$tmpl =& moduleScreens_admin::createTemplate( 'mod_components.html' );

		$tmpl->addVar( 'body', 'show', $show );
		$tmpl->addVar( 'body', 'display', $display );
		$tmpl->addObject( 'rows', $rows, 'row_' );


		$tmpl->displayParsedTemplate( 'mod_components' );
	}
}
?>