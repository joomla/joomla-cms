<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CB\Database\Table\PluginTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $_CB_Backend_Menu, $_CB_Backend_task, $_PLUGINS;

if ( isset( $_CB_Backend_Menu->mode ) ) {
	if ( isset( $_CB_Backend_Menu->menuItems ) && $_CB_Backend_Menu->menuItems ) {
		TOOLBAR_usersextras::_PLUGIN_MENU( $_CB_Backend_Menu->menuItems );
		// return;		//TODO: This is temporary until we clean-up.
	}
}

switch ( $_CB_Backend_task ) {

	case "edit":
		TOOLBAR_usersextras::_EDIT();
		break;

	case "new":
		TOOLBAR_usersextras::_NEW();
		break;

	case "emailusers":
		TOOLBAR_usersextras::_EMAIL_USERS();
		break;

	case 'loadSampleData':
	case 'loadCanvasLayout':
	case 'syncUsers':
	case 'checkcbdb':
	case 'fixcbdb':
	case 'fixacldb':
	case 'fixcbmiscdb':
	case 'fixcbdeprecdb':
		TOOLBAR_usersextras::_TOOLS();
		break;

	case 'editPlugin':
		if ( isset( $_CB_Backend_Menu->mode ) ) {
			if ( isset( $_CB_Backend_Menu->menuItems ) && $_CB_Backend_Menu->menuItems ) {
				// Done above: TOOLBAR_usersextras::_PLUGIN_MENU( $_CB_Backend_Menu->menuItems );
			}
			elseif ( $_CB_Backend_Menu->mode == 'show' ) {
				TOOLBAR_usersextras::_PLUGIN_ACTION_SHOW();
			}
			elseif ( $_CB_Backend_Menu->mode == 'edit' ) {
				TOOLBAR_usersextras::_PLUGIN_ACTION_EDIT();
			}
		}
		break;

	case 'pluginmenu':
		$plugin	=	new PluginTable();
		$result	=	$plugin->load( (int) cbGetParam( $_REQUEST, 'pluginid', -1 ) );

		if ( $result ) {
			$pluginMenuToolbarFile	=	$_CB_framework->getCfg( 'absolute_path' ) . '/' . $_PLUGINS->getPluginRelPath( $plugin ) . '/toolbar.' . $plugin->element . '.php';

			if ( file_exists( $pluginMenuToolbarFile ) ) {
				/** @noinspection PhpIncludeInspection */
				include_once( $pluginMenuToolbarFile );
				break;
			}
		}
		TOOLBAR_usersextras::_DEFAULT_PLUGIN_MENU();
		break;
}
