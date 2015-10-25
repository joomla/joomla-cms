<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class mod_cbloginInstallerScript {

	public function install( $adapter ) {
		$version			=	new JVersion();
		$db					=	JFactory::getDbo();
		$module				=	JTable::getInstance( 'module' );

		if ( $module->load( array( 'module' => 'mod_cblogin', 'position' => '' ) ) || ( ! $module->load( array( 'module' => 'mod_cblogin' ) ) ) ) {
			$module->set( 'title', 'CB Login' );
			$module->set( 'ordering', '1' );
			$module->set( 'position', 'position-7' );
			$module->set( 'published', '1' );
			$module->set( 'module', 'mod_cblogin' );
			$module->set( 'access', '1' );
			$module->set( 'showtitle', '1' );

			if ( $version->isCompatible( '3.0' ) ) {
				$module->set( 'params', '{"show_buttons_icons":"0","https_post":"0","cb_plugins":"1","pretext":"","posttext":"","login":"","name_label":"5","name_length":"14","pass_label":"5","pass_length":"14","key_label":"5","key_length":"14","remember_enabled":"1","show_lostpass":"1","show_newaccount":"1","login_message":"0","logoutpretext":"","logoutposttext":"","logout":"index.php","greeting":"1","show_avatar":"1","text_show_profile":"","icon_show_profile":"0","text_edit_profile":"","icon_edit_profile":"0","show_pms":"0","show_pms_icon":"0","show_connection_notifications":"0","show_connection_notifications_icon":"0","logout_message":"0","layout":"_:bootstrap","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}' );
			} else {
				$module->set( 'params', '{"show_buttons_icons":"0","https_post":"0","cb_plugins":"1","pretext":"","posttext":"","login":"","name_label":"1","name_length":"14","pass_label":"1","pass_length":"14","key_label":"1","key_length":"14","remember_enabled":"1","show_lostpass":"1","show_newaccount":"1","login_message":"0","logoutpretext":"","logoutposttext":"","logout":"index.php","greeting":"1","show_avatar":"1","text_show_profile":"","icon_show_profile":"0","text_edit_profile":"","icon_edit_profile":"0","show_pms":"0","show_pms_icon":"0","show_connection_notifications":"0","show_connection_notifications_icon":"0","logout_message":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}' );
			}

			$module->set( 'client_id', '0' );
			$module->set( 'language', '*' );

			if ( $module->store() ) {
				$moduleId	=	$module->get( 'id' );

				if ( $moduleId ) {
					$db->setQuery( 'INSERT IGNORE INTO `#__modules_menu` ( `moduleid`, `menuid` ) VALUES ( ' . (int) $moduleId . ', 0 )' );

					try {
						$db->execute();
					} catch ( RuntimeException $e ) {}
				}
			}
		}
	}

	public function discover_install( $adapter ) {
		$this->install( $adapter );
	}
}
?>