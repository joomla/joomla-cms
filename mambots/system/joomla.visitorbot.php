<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

$_MAMBOTS->registerFunction( 'onAfterStart', 'botDetectVisitor' );

/**
 * Detects a 'visit'
 *
 * This function updates the agent and domain table hits for a particular
 * visitor.  The user agent is recorded/incremented if this is the first visit.
 * A cookie is set to mark the first visit.
 */
function botDetectVisitor() {
	global $database, $mainframe;
	
	if ( mosGetParam( $_COOKIE, 'mosvisitor', 0 ) || !$mainframe->isSite() ) {
		return;
	}
	
	//get JBrowser object
	$objBrowser = JApplication::getBrowser();
	
	if( $objBrowser->isRobot()) {
		return;
	}
	
	setcookie( 'mosvisitor', '1' );
	
	$browser  = $objBrowser->getFeature('parent');
	$platform = $objBrowser->getPlatform();

	if (phpversion() <= '4.2.1') {
		$domain = gethostbyaddr( getenv( 'REMOTE_ADDR' ) );
	} else {
		$domain = gethostbyaddr( mosGetParam( $_SERVER, 'REMOTE_ADDR', '' ) );
	}

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

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$platform'"
	. "\n AND type = '1'"
	;
	$database->setQuery( $query );
	if ($database->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$platform'"
		. "\n AND type = '1'"
		;
		$database->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$platform', '1' )"
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
?>