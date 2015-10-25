<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class mod_comprofileronlineInstallerScript {

	public function install( $adapter ) {
		$version			=	new JVersion();
		$db					=	JFactory::getDbo();
		$module				=	JTable::getInstance( 'module' );

		if ( $module->load( array( 'module' => 'mod_comprofileronline', 'position' => '' ) ) || ( ! $module->load( array( 'module' => 'mod_comprofileronline' ) ) ) ) {
			$module->set( 'title', 'CB Online' );
			$module->set( 'ordering', '3' );
			$module->set( 'position', 'position-7' );
			$module->set( 'published', '1' );
			$module->set( 'module', 'mod_comprofileronline' );
			$module->set( 'access', '1' );
			$module->set( 'showtitle', '1' );

			if ( $version->isCompatible( '3.0' ) ) {
				$module->set( 'params', '{"pretext":"","posttext":"","cb_plugins":"0","layout":"_:default","moduleclass_sfx":"","cache":"0","module_tag":"div","bootstrap_size":"0","header_tag":"h3","header_class":"","style":"0"}' );
			} else {
				$module->set( 'params', '{"pretext":"","posttext":"","cb_plugins":"0","layout":"_:default","moduleclass_sfx":"","cache":"0"}' );
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