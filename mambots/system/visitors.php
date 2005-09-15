<?php
/**
* @version $Id: visitors.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onStart', 'botDetectVisitor' );

/**
 * Detects a 'visit'
 *
 * This function updates the agent and domain table hits for a particular
 * visitor.  The user agent is recorded/incremented if this is the first visit.
 * A cookie is set to mark the first visit.
 */
function botDetectVisitor() {
	global $database;
	if ( mosGetParam( $_COOKIE, 'mosvisitor', 0 ) ) {
		return;
	}
	setcookie( 'mosvisitor', '1' );

	if (phpversion() <= '4.2.1') {
		$agent 	= getenv( 'HTTP_USER_AGENT' );
		$domain = gethostbyaddr( getenv( 'REMOTE_ADDR' ) );
	} else {
		$agent 	= mosGetParam( $_SERVER, 'HTTP_USER_AGENT', '' );
		$domain = gethostbyaddr( mosGetParam( $_SERVER, 'REMOTE_ADDR', '' ) );
	}

	$browser = mosGetBrowser( $agent );

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$browser'"
	. "\n AND type = '0'"
	;
	$database->setQuery( $query );
	if ($database->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$browser'"
		. "\n AND type = '0'"
		;
		$database->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$browser', '0' )"
		;
		$database->setQuery( $query );
	}
	$database->query();

	$os = mosGetOS( $agent );

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$os'"
	. "\n AND type = '1'"
	;
	$database->setQuery( $query );
	if ($database->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$os'"
		. "\n AND type = '1'"
		;
		$database->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$os', '1' )"
		;
		$database->setQuery( $query );
	}
	$database->query();

	// tease out the last element of the domain
	$tldomain = split( "\.", $domain );
	$tldomain = $tldomain[count( $tldomain )-1];

	if (is_numeric( $tldomain )) {
		$tldomain = 'Unknown';
	}

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$tldomain'"
	. "\n AND type = '2'"
	;
	$database->setQuery( $query );
	if ($database->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$tldomain'"
		. "\n AND type = '2'"
		;
		$database->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$tldomain', '2' )"
		;
		$database->setQuery( $query );
	}
	$database->query();
}

/**
 * Checks the user agent string against known browsers
 */
function mosGetBrowser( $agent ) {
	require( dirname( __FILE__ ) . '/visitors.agent_browser.php' );

	if (preg_match( "/msie[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/webtv/i", $agent )
	&& !preg_match( "/omniweb/i", $agent )
	&& !preg_match( "/opera/i", $agent )) {
		// IE
		return "MS Internet Explorer $m[1]";
	} else if (preg_match( "/netscape.?\/([\d\.]*)/i", $agent, $m )) {
		// Netscape 6.x, 7.x ...
		return "Netscape $m[1]";
	} else if ( preg_match( "/mozilla[\/\sa-z]*([\d\.]*)/i", $agent, $m )
	&& !preg_match( "/gecko/i", $agent )
	&& !preg_match( "/compatible/i", $agent )
	&& !preg_match( "/opera/i", $agent )
	&& !preg_match( "/galeon/i", $agent )
	&& !preg_match( "/safari/i", $agent )) {
		// Netscape 3.x, 4.x ...
		return "Netscape $m[2]";
	} else {
		// Other
		$found = false;
		foreach ($browserSearchOrder as $key) {
			if (preg_match( "/$key.?\/([\d\.]*)/i", $agent, $m )) {
				$name = "$browsersAlias[$key] $m[1]";
				return $name;
				break;
			}
		}
	}

	return 'Unknown';
}

/**
 * Checks the user agent string against known operating systems
 */
function mosGetOS( $agent ) {
	require( dirname( __FILE__ ) . '/visitors.agent_os.php' );

	foreach ($osSearchOrder as $key) {
		if (preg_match( "/$key/i", $agent )) {
			return $osAlias[$key];
			break;
		}
	}

	return 'Unknown';
}
?>