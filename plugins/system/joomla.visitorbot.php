<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$mainframe->registerEvent( 'onAfterStart', 'botDetectVisitor' );

/**
 * Detects a 'visit'
 *
 * This function updates the agent and domain table hits for a particular
 * visitor.  The user agent is recorded/incremented if this is the first visit.
 * A cookie is set to mark the first visit.
 */
function botDetectVisitor()
{
	global $mainframe;

	$db =& JFactory::getDBO();
	if ( JRequest::getVar( 'mosvisitor', 0, 'COOKIE' ) || !$mainframe->isSite() ) {
		return;
	}

	jimport('joomla.environment.browser');
	$browser =& JBrowser::getInstance();

	if( is_null( $browser ) || $browser->isRobot()) {
		return;
	}

	setcookie( 'mosvisitor', '1' );

	$agent    = $browser->getBrowser();
	$platform = $browser->getPlatform();

	if (phpversion() <= '4.2.1') {
		$domain = @gethostbyaddr( getenv( 'REMOTE_ADDR' ) );
	} else {
		$domain = @gethostbyaddr( JArrayHelper::getValue( $_SERVER, 'REMOTE_ADDR', '' ) );
	}

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$agent'"
	. "\n AND type = '0'"
	;
	$db->setQuery( $query );
	if ($db->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$agent'"
		. "\n AND type = '0'"
		;
		$db->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$agent', '0' )"
		;
		$db->setQuery( $query );
	}
	$db->query();

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$platform'"
	. "\n AND type = '1'"
	;
	$db->setQuery( $query );
	if ($db->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$platform'"
		. "\n AND type = '1'"
		;
		$db->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$platform', '1' )"
		;
		$db->setQuery( $query );
	}
	$db->query();

	// tease out the last element of the domain
	$tldomain = explode( "\.", $domain );
	$tldomain = $tldomain[count( $tldomain )-1];

	if (is_numeric( $tldomain )) {
		$tldomain = 'Unknown';
	}

	$query = "SELECT COUNT(*)"
	. "\n FROM #__stats_agents"
	. "\n WHERE agent = '$tldomain'"
	. "\n AND type = '2'"
	;
	$db->setQuery( $query );
	if ($db->loadResult()) {
		$query = "UPDATE #__stats_agents"
		. "\n SET hits = ( hits + 1 )"
		. "\n WHERE agent = '$tldomain'"
		. "\n AND type = '2'"
		;
		$db->setQuery( $query );
	} else {
		$query = "INSERT INTO #__stats_agents"
		. "\n ( agent, type ) VALUES ( '$tldomain', '2' )"
		;
		$db->setQuery( $query );
	}
	$db->query();
}
?>