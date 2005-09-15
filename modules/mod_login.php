<?php
/**
 * @version $Id: mod_login.php 137 2005-09-12 10:21:17Z eddieajau $
 * @package Joomla
 * @subpackage mod_login
 * @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 * Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

class modLoginData {

	function getType( &$params ) {
		global $my;
	    $type = ($my->id) ? 'login' : 'logout';
		return $type;
	}

	function getVars( &$params, $return ) {
		 global $my, $mainframe, $database;

		$vars = array(
			'allowUserRegistration' => $mainframe->getCfg( 'allowUserRegistration' ),
			// converts & to &amp; for xtml compliance
			'return' => ampReplace( $return )
		);

		if ( $params->get( 'name' ) ) {
			$query = "SELECT name"
			. "\n FROM #__users"
			. "\n WHERE id = $my->id"
			;
			$database->setQuery( $query );
			$vars['name'] = $database->loadResult();
		} else {
			$vars['name'] = $my->username;
		}

		return $vars;
	}
}

class modLogin {

	/**
	 * Show the login/logout form
	 */
	function show (&$params) {
		global $my;
		$cache = mosFactory::getCache( "mod_login");

		$cache->setCaching($params->get('cache', 1));
		$cache->setCacheValidation(false);

		$cache->callId( "modLogin::_display", array( $params ), "mod_login".$my->gid );
	}

	function _display( &$params ) {

		$return = mosGetParam( $_SERVER, 'REQUEST_URI', null );
		$params->def( 'logout', $return );

		$type = modLoginData::getType($params);
		$vars = modLoginData::getVars($params, $return);

		$tmpl =& moduleScreens::createTemplate( 'mod_login.html' );

		$tmpl->addVar( 'mod_login', 'type', $type );
		$tmpl->addVars( 'mod_login', $vars );
		$tmpl->addObject( 'mod_login', $params->toObject() );

		$tmpl->displayParsedTemplate( 'mod_login' );
	}
}

modLogin::show( $params );
?>