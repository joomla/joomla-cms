<?php
/**
 * @version $Id: mambo.files.xml.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Mambo
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@domit' );

/**
* @package Mambo
*/
class mosXMLFS extends mosFS {
	/**
	 * @return array Common meta tags in an xml setup file
	 */
	function getCommonTags() {
		return array( 'name', 'version', 'creationDate', 'author', 'authorEmail', 'authorUrl', 'copyright', 'license', 'description' );
	}

	/**
	 * @param array
	 * @param string The version for the mosinstall tag
	 * @return DOMIT_Lite_Document
	 */
	function &arrayToXML( $vars, $version='' ) {
		global $mosConfig_absolute_path;

		varsStripSlashes( $vars );

		if ($version == '') {
			global $_VERSION;
			$version = $_VERSION->RELEASE . '.' . $_VERSION->DEV_LEVEL;
		}

		require_once( $mosConfig_absolute_path . '/includes/domit/xml_domit_lite_include.php' );

		$xmlDoc = new DOMIT_Lite_Document();

		$elem = new DOMIT_Lite_Element( 'mosinstall' );
		$elem->setAttribute( 'version', $version );

		if ($client = mosGetParam( $vars, 'client' )) {
			$elem->setAttribute( 'client', $client );
		}

		$xmlDoc->setDocumentElement( $elem );
		$root =& $xmlDoc->documentElement;
		$strip = get_magic_quotes_gpc();

		// meta tags for the setup file
		// contained in $vars['meta']
		$meta = mosGetParam( $vars, 'meta', array() );

		foreach (mosXMLFS::getCommonTags() as $elemName) {
			$e =& $xmlDoc->createElement( $elemName );
			if (isset( $meta[$elemName] )) {
				$e->setText( $meta[$elemName] );
				$root->appendChild( $e );
			}
		}

		// site files
		// contained in $vars['siteFiles']
		$eFiles =& $xmlDoc->createElement( 'files' );
		$site_files = mosGetParam( $vars, 'siteFiles', array() );

		if (count( $site_files)) {
			$module = mosGetParam( $vars, 'module', null );
			$mambot = mosGetParam( $vars, 'mambot', null );

			foreach ($site_files as $i => $file) {
				$e =& $xmlDoc->createElement( 'filename' );

				if ($strip) {
					$site_files[$i] = stripslashes( $site_files[$i] );
				}

				if ($site_files[$i] === $module) {
					$e->setAttribute( 'module', mosFS::stripExt( $site_files[$i] ) );
				}

				if ($site_files[$i] === $mambot) {
					$e->setAttribute( 'mambot', mosFS::stripExt( $site_files[$i] ) );
				}

				$site_files[$i] = mosFS::getNativePath( $site_files[$i], false, true );

				$e->setText( $site_files[$i] );
				$eFiles->appendChild( $e );
			}
			$root->appendChild( $eFiles );
		}

		// administrator files
		// contained in $vars['adminFiles']
		$eAdmin =& $xmlDoc->createElement( 'administration' );
		$eFiles =& $xmlDoc->createElement( 'files' );
		$eFiles->setAttribute( 'folder', 'admin' );
		$admin_files = mosGetParam( $vars, 'adminFiles', array() );

		if (count( $admin_files )) {
			foreach ($admin_files as $i => $file) {
				if ($strip) {
					$admin_files[$i] = stripslashes( $admin_files[$i] );
				}
				$admin_files[$i] = mosFS::getNativePath( $admin_files[$i], false, true );

				$e =& $xmlDoc->createElement( 'filename' );
				$e->setText( $admin_files[$i] );
				$eFiles->appendChild( $e );
			}
			$eAdmin->appendChild( $eFiles );
		}

		// administrator menus
		// contained in $vars['adminMenus'] and $vars['adminSubMenus']
		$admin_menus = mosGetParam( $vars, 'adminMenus', array() );

		foreach ($admin_menus as $k=>$v) {
			$menu =& $admin_menus[$k];
			if (isset( $menu['id'] )) {
				$e =& $xmlDoc->createElement( 'menu' );
				$e->setText( $menu['name'] );

				if (isset( $menu['admin_menu_link'] )) {
					$menu['admin_menu_link'] = ampReplace( $menu['admin_menu_link'] );
					$e->setAttribute( 'link', $menu['admin_menu_link'] );
				}
				$eAdmin->appendChild( $e );
			}
		}

		$admin_menus = mosGetParam( $vars, 'adminSubMenus', array() );

		foreach ($admin_menus as $k => $v) {
			$menu =& $admin_menus[$k];
			if (isset( $menu['id'] )) {
				$e =& $xmlDoc->createElement( 'submenu' );
				$e->setText( $menu['name'] );

				if (isset( $menu['admin_menu_link'] )) {
					$menu['admin_menu_link'] = ampReplace( $menu['admin_menu_link'] );
					$e->setAttribute( 'link', $menu['admin_menu_link'] );
				}
				$eAdmin->appendChild( $e );
			}
		}

		// install/uninstall queries
		if (isset( $vars['installQueries'] )) {
			$eQueries =& $xmlDoc->createElement( 'queries' );
			$i = 1;

			foreach ($vars['installQueries'] as $query) {
				if (isset( $query['id'])) {
					$eQuery =& $xmlDoc->createElement( 'query' );
					$eQuery->setAttribute( 'id', $i++ );
					$eQuery->setAttribute( 'table', $query['table'] );
					$eQuery->setText( $query['query'] );
					$eQueries->appendChild( $eQuery );
				}
			}

			$eInstall =& $xmlDoc->createElement( 'install' );
			$eInstall->appendChild( $eQueries );
			$root->appendChild( $eInstall );
		}

		if (isset( $vars['uninstallQueries'] )) {
			$eQueries =& $xmlDoc->createElement( 'queries' );
			$i = 1;

			foreach ($vars['uninstallQueries'] as $query) {
				if (isset( $query['id'])) {
					$eQuery =& $xmlDoc->createElement( 'query' );
					$eQuery->setAttribute( 'id', $i++ );
					$eQuery->setAttribute( 'table', $query['table'] );
					$eQuery->setText( $query['query'] );
					$eQueries->appendChild( $eQuery );
				}
			}

			$eInstall =& $xmlDoc->createElement( 'uninstall' );
			$eInstall->appendChild( $eQueries );

			$root->appendChild( $eInstall );
		}

		// install/uninstall files
		$installFile = mosGetParam( $vars, 'installFile', '' );

		if ($installFile) {
			$e =& $xmlDoc->createElement( 'installfile' );
			$e->setText( $installFile );
			$root->appendChild( $e );
		}

		$uninstallFile = mosGetParam( $vars, 'uninstallFile', '' );

		if ($uninstallFile) {
			$e =& $xmlDoc->createElement( 'uninstallfile' );
			$e->setText( $uninstallFile );
			$root->appendChild( $e );
		}

		if ($eAdmin->hasChildNodes()) {
			$root->appendChild( $eAdmin );
		}

		if (isset( $vars['params'] )) {
			$temp = new DOMIT_Lite_Document();
			$temp->parseXML( $vars['params'] );
			$root->appendChild( $temp->documentElement );
		} else {
			$e =& $xmlDoc->createElement( 'params' );
			$root->appendChild( $e );
		}

		return $xmlDoc;
	}

	/**
	 * @param string The xml file to parse
	 * @param string The setup file type to check for
	 * @param array A complex array of file variables
	 * @return boolean True on success
	 */
	function read( $file, $type, &$vars ) {
		mosFS::check( $file );

		$xmlDoc = new DOMIT_Lite_Document();
		$xmlDoc->resolveErrors( true );

		if (!$xmlDoc->loadXML( $file, false, true )) {
			echo $xmlDoc->getErrorString();
			return false;
		}

		$root = &$xmlDoc->documentElement;

		if ($root->getTagName() != 'mosinstall') {
			echo 'Not mosinstall';
			return false;
		}

		if ($root->getAttribute( 'type' ) != $type) {
			return false;
		}

		$vars = array(
			'client' => $root->getAttribute( 'client' ),
			'meta' => array()
		);

		foreach (mosXMLFS::getCommonTags() as $elemName) {
			$element = &$xmlDoc->getElementsByPath( $elemName, 1 );
			if ($elemName=='authorUrl' && strcasecmp( substr($element->getText(), 0, 4), 'http' ) ) {
				$vars['meta'][$elemName] = $element ? 'http://' . $element->getText() : '';
			} else {
				$vars['meta'][$elemName] = $element ? $element->getText() : '';
			}
		}
		if ($type == 'mambot') {
			$vars['meta']['group'] = $root->getAttribute( 'group' );
		}

		// site files
		$nodeList = &$xmlDoc->getElementsByPath( 'files/filename' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$file = array(
				'file' => str_replace( '\\', '/', $e->getText() )
			);

			if ($e->getAttribute( 'module' )) {
				$file['special'] = 1;
			}

			if ($e->getAttribute( 'mambot' )) {
				$file['special'] = 1;
			}
			$vars['siteFiles'][] = $file;
		}

		$nodeList = &$xmlDoc->getElementsByPath( 'images/filename' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['siteFiles'][] = array(
				'file' => $e->getText()
			);
		}

		$nodeList = &$xmlDoc->getElementsByPath( 'css/filename' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['siteFiles'][] = array(
				'file' => $e->getText()
			);
		}

		// check admin section
		$nodeList = &$xmlDoc->getElementsByPath( 'administration/files/filename' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['adminFiles'][] = array( 'file' => str_replace( '\\', '/', $e->getText() ) );
		}

		// administrator menus
		$nodeList = &$xmlDoc->getElementsByPath( 'administration/menu' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['adminMenus'][] = array(
				'id' => $i,
				'name' => $e->getText(),
				'admin_menu_link' => $e->getAttribute( 'link' )
			);
		}

		$nodeList = &$xmlDoc->getElementsByPath( 'administration/submenu' );
		$n = $nodeList->getLength();

		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['adminSubMenus'][] = array(
				'id' => $i,
				'name' => $e->getText(),
				'admin_menu_link' => $e->getAttribute( 'link' )
			);
		}

		// install queries
		$nodeList = &$xmlDoc->getElementsByPath( 'install/queries/query' );
		$n = $nodeList->getLength();
		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['installQueries'][] = array(
				'id' => count( @$vars['installQueries'] ),
				'query' => $e->getText(),
				'table' => $e->getAttribute( 'table' )
			);
		}

		// uninstall queries
		$nodeList = &$xmlDoc->getElementsByPath( 'uninstall/queries/query' );
		$n = $nodeList->getLength();
		for ($i = 0; $i < $n; $i++) {
			$e =& $nodeList->item( $i );
			$vars['uninstallQueries'][] = array(
				'id' => count( @$vars['uninstallQueries'] ),
				'query' => $e->getText()
			);
		}

		// installer files
		$e = &$xmlDoc->getElementsByPath( 'installfile', 1 );
		if ($e) {
			$vars['installFile'] = $e->getText();
		}

		$e = &$xmlDoc->getElementsByPath( 'uninstallfile', 1 );
		if ($e) {
			$vars['uninstallFile'] = $e->getText();
		}

		// parameters
		$e = &$xmlDoc->getElementsByPath( 'params', 1 );
		if ($e) {
			$vars['params'] = $e->toNormalizedString();
		}

		unset( $xmlDoc );

		return $vars;
	}

	/**
	 * Saves the xml steup file
	 * @param string The name of the element
	 * @param array
	 * @param string The path path to save in
	 * @return boolean True if successful
	 */

	function write( $element, $vars, $filePath ) {
		$xmlDoc =& mosXMLFS::arrayToXML( $vars );

		$root =& $xmlDoc->documentElement;
		$root->setAttribute( 'type', $element );
		if ($element == 'mambot' && isset( $vars['meta']['group'])) {
			$root->setAttribute( 'group', $vars['meta']['group'] );
		}

		$xmlDoc->setXMLDeclaration( '<?xml version="1.0" encoding="iso-8859-1"?>' );
		mosFS::check( $filePath );
		//echo $xmlDoc->toNormalizedString(1);die;

		$ret = $xmlDoc->saveXML( $filePath, true );

		unset( $xmlDoc );

		return $ret;
	}
}

/**
 * A function to strip slashes in critical string elements
 * @param array The vars array
 */
function varsStripSlashes( &$vars ) {
	if (!get_magic_quotes_gpc()) {
		return;
	}

	if (isset( $vars['installQueries'] )) {
		foreach ($vars['installQueries'] as $k => $v) {
			$v['query'] = stripslashes( $v['query'] );
			$vars['installQueries'][$k] = $v;
		}
	}

	if (isset( $vars['uninstallQueries'] )) {
		foreach ($vars['uninstallQueries'] as $k => $v) {
			$v['query'] = stripslashes( $v['query'] );
			$vars['uninstallQueries'][$k] = $v;
		}
	}

	if (isset( $vars['params'] )) {
		$vars['params'] = stripslashes( $vars['params'] );
	}
}
?>