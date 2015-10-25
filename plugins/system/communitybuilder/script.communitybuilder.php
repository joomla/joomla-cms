<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class plgsystemcommunitybuilderInstallerScript {

	public function install( /** @noinspection PhpUnusedParameterInspection */ $adapter ) {
		/** @var $plugin JTableExtension */
		$plugin				=	JTable::getInstance( 'extension' );

		if ( ! $plugin->load( array( 'type' => 'plugin', 'folder' => 'system', 'element' => 'communitybuilder' ) ) ) {
			return false;
		}

		/** @var $legacy JTableExtension */
		$legacy				=	JTable::getInstance( 'extension' );

		if ( $legacy->load( array( 'type' => 'plugin', 'folder' => 'system', 'element' => 'cbcoreredirect' ) ) ) {
			$pluginParams	=	new JRegistry;

			$pluginParams->loadString( $plugin->get( 'params' ) );

			$legacyParams	=	new JRegistry;

			$legacyParams->loadString( $legacy->get( 'params' ) );

			$pluginParams->set( 'rewrite_urls', $legacyParams->get( 'rewrite_urls', 1 ) );
			$pluginParams->set( 'itemids', $legacyParams->get( 'itemids', 1 ) );

			$plugin->set( 'params', $pluginParams->toString() );

			$installer		=	new JInstaller();

			try {
				$installer->uninstall( 'plugin', $legacy->get( 'extension_id' ) );
			} catch ( RuntimeException $e ) {}
		}

		$plugin->set( 'enabled', 1 );

		return $plugin->store();
	}

	public function discover_install( $adapter ) {
		$this->install( $adapter );
	}
}
