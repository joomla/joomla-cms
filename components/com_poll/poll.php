<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

require_once( JApplicationHelper::getPath( 'front_html' ) );
require_once( JApplicationHelper::getPath( 'class' ) );

$task 	= JRequest::getVar( 'task' );

switch ($task) {
	case 'vote':
		pollAddVote();
		break;

	default:
		pollresult();
		break;
}

/**
 * Add a vote to an option
 */
function pollAddVote()
{
	global $mainframe;

	$database	= $mainframe->getDBO();

	$poll_id	= JRequest::getVar( 'id', 0, '', 'int' );
	$option_id	= JRequest::getVar( 'voteid', 0, 'post', 'int' );

	$redirect = 1;

	$poll = new mosPoll( $database );
	if (!$poll->load( $poll_id ) || $poll->published != 1)
	{
		echo '<h3>'. JText::_('ALERTNOTAUTH') .'</h3>';
		echo '<input class="button" type="button" value="'. JText::_( 'Continue' ) .'" onClick="window.history.go(-1);">';
		return;
	}

	$siteName	= $mainframe->getCfg( 'live_site' );
	$cookieName	= mosHash( $siteName . 'poll' . $poll_id );
	$voted = 0; //mosGetParam( $_COOKIE, $cookiename, '0' );

	if ($voted)
	{
		echo '<h3>'. JText::_( 'You already voted for this poll today!' ) .'</h3>';
		echo "<input class=\"button\" type=\"button\" value=\"". JText::_( 'Continue' )."\" onClick=\"window.history.go(-1);\">";
		return;
	}

	if (!$option_id)
	{
		echo '<h3>'. JText::_( 'WARNSELECT' ) .'</h3>';
		echo '<input class="button" type="button" value="'. JText::_( 'Continue' ) .'" onClick="window.history.go(-1);">';
		return;
	}

	setcookie( $cookieName, '1', time() + $poll->lag );

	$model = new JModelPolls( $database );
	$model->addVote( $poll_id, $option_id );

	if ( $redirect ) {
		josRedirect( sefRelToAbs( 'index.php?option=com_poll&task=results&id='. $poll_id ), JText::_( 'Thanks for your vote!' ) );
	} else {
		echo '<h3>'. JText::_( 'Thanks for your vote!' ) .'</h3>';
		echo '<form action="" method="GET">';
		echo '<input class="button" type="button" value="'. JText::_( 'Results' ) .'" onclick="window.location=\''. sefRelToAbs( 'index.php?option=com_poll&task=results&id='. $poll_id ) .'\'">';
		echo '</form>';
	}
}

/**
 * Display the poll result
 */
function pollresult() {
	global $Itemid;
	global $mainframe;

	$poll_id 	= JRequest::getVar( 'id', 0, '', 'int' );
	$database	= $mainframe->getDBO();

	$poll = new mosPoll( $database );
	$poll->load( $poll_id );

	// if id value is passed and poll not published then exit
	if ($poll->id > 0 && $poll->published != 1) {
		JError::raiseError( 403, JText::_('Access Forbidden') );
		return;
	}

	$first_vote = '';
	$last_vote 	= '';
	$votes		= '';

	/*
	Check if there is a poll corresponding to id
	and if poll is published
	*/
	if ($poll->id > 0) {
		if (empty( $poll->title )) {
			$poll->id = 0;
			$poll->title = JText::_( 'Select Poll from the list' );
		}

		$query = "SELECT MIN( date ) AS mindate, MAX( date ) AS maxdate"
		. "\n FROM #__poll_date"
		. "\n WHERE poll_id = $poll->id"
		;
		$database->setQuery( $query );
		$dates = null;
		$database->loadObject( $dates );

		if (isset( $dates->mindate )) {
			$first_vote = mosFormatDate( $dates->mindate, JText::_( 'DATEFORMATLC2' ) );
			$last_vote 	= mosFormatDate( $dates->maxdate, JText::_( 'DATEFORMATLC2' ) );
		}

		$query = "SELECT a.id, a.text, a.hits, b.voters"
		. "\n FROM #__poll_data AS a"
		. "\n INNER JOIN #__polls AS b ON b.id = a.pollid"
		. "\n WHERE a.pollid = $poll->id"
		. "\n AND a.text <> ''"
		. "\n ORDER BY a.hits DESC"
		;
		$database->setQuery( $query );
		$votes = $database->loadObjectList();
	} else {
		$votes = array();
	}

	// list of polls for dropdown selection
	$query = "SELECT id, title"
	. "\n FROM #__polls"
	. "\n WHERE published = 1"
	. "\n ORDER BY id"
	;
	$database->setQuery( $query );
	$polls = $database->loadObjectList();

	// Itemid for dropdown
	$_Itemid = '';
	if ( $Itemid || $Itemid != 99999999 ) {
		$_Itemid = '&amp;Itemid='. $Itemid;
	}

	$lists = array();

	// dropdown output
	$link = sefRelToAbs( 'index.php?option=com_poll&amp;task=results&amp;id=\' + this.options[selectedIndex].value + \'&amp;Itemid='. $Itemid .'\' + \'' );

	array_unshift( $polls, mosHTML::makeOption( '', JText::_( 'Select Poll from the list' ), 'id', 'title' ));

	$lists['polls'] = mosHTML::selectList( $polls, 'id',
		'class="inputbox" size="1" style="width:200px" onchange="if (this.options[selectedIndex].value != \'\') {document.location.href=\''. $link .'\'}"',
		'id', 'title',
		$poll->id
		);

	// Adds parameter handling
	$menu =& JTable::getInstance('menu', $database );
	$menu->load( $Itemid );

	$params = new JParameter( $menu->params );

	poll_html::showResults( $poll, $votes, $first_vote, $last_vote, $lists, $params, $menu );
}
?>