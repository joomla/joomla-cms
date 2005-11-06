<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
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

require_once( $mainframe->getPath( 'front_html' ) );
require_once( $mainframe->getPath( 'class' ) );

$tabclass 			= 'sectiontableentry2,sectiontableentry1';
$polls_graphwidth 	= 200;
$polls_barheight 	= 2;
$polls_maxcolors 	= 5;
$polls_barcolor 	= 0;

$poll = new mosPoll( $database );

$id 	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
$task 	= mosGetParam( $_REQUEST, 'task', '' );

switch ($task) {
	case 'vote':
		pollAddVote( $id );
		break;

	default:
		pollresult( $id );
		break;
}

function pollAddVote( $uid ) {
	global $database;
	global $_LANG;

	$redirect = 1;

	$poll = new mosPoll( $database );
	if (!$poll->load( $uid )) {
		echo '<h3>'. $_LANG->_('ALERTNOTAUTH') .'</h3>';
		echo '<input class="button" type="button" value="'. $_LANG->_( 'Continue' ) .'" onClick="window.history.go(-1);">';
		return;
	}

	$cookiename = "voted$poll->id";
	$voted = mosGetParam( $_COOKIE, $cookiename, '0' );

	if ($voted) {
		echo "<h3>". $_LANG->_( 'You already voted for this poll today!' ) ."</h3>";
		echo "<input class=\"button\" type=\"button\" value=\"". $_LANG->_( 'Continue' )."\" onClick=\"window.history.go(-1);\">";
		return;
	}

	$voteid = mosGetParam( $_POST, 'voteid', 0 );
	if (!$voteid) {
		echo "<h3>". $_LANG->_( 'WARNSELECT' ) ."</h3>";
		echo '<input class="button" type="button" value="'. $_LANG->_( 'Continue' ) .'" onClick="window.history.go(-1);">';
		return;
	}

	setcookie( $cookiename, '1', time()+$poll->lag );

	$query = "UPDATE #__poll_data"
	. "\n SET hits = hits + 1"
	. "\n WHERE pollid = $poll->id"
	. "\n AND id = $voteid"
	;
	$database->setQuery( $query );
	$database->query();

	$query = "UPDATE #__polls"
	. "\n SET voters = voters + 1"
	. "\n WHERE id = $poll->id"
	;
	$database->setQuery( $query );

	$database->query();

	$now = date( 'Y-m-d G:i:s' );
	$query = "INSERT INTO #__poll_date"
	. "\n SET date = '$now', vote_id = $voteid, poll_id = $poll->id"
	;
	$database->setQuery( $query );
	$database->query();

	if ( $redirect ) {
		mosRedirect( sefRelToAbs( 'index.php?option=com_poll&task=results&id='. $uid ), $_LANG->_( 'Thanks for your vote!' ) );
	} else {
		echo '<h3>'. $_LANG->_( 'Thanks for your vote!' ) .'</h3>';
		echo '<form action="" method="GET">';
		echo '<input class="button" type="button" value="'. $_LANG->_( 'Results' ) .'" onClick="window.location=\''. sefRelToAbs( 'index.php?option=com_poll&task=results&id='. $uid ) .'\'">';
		echo '</form>';
	}
}

function pollresult( $uid ) {
	global $database, $Itemid;
	global $mainframe;
	global $_LANG;

	$poll = new mosPoll( $database );
	$poll->load( $uid );

	if (empty($poll->title)) {
		$poll->id = '';
		$poll->title = $_LANG->_( 'Select Poll from the list' );
	}

	$first_vote = '';
	$last_vote 	= '';
	$votes		= '';

	if (isset($poll->id) && $poll->id != '') {
		$query = "SELECT MIN( date ) AS mindate, MAX( date ) AS maxdate"
		. "\n FROM #__poll_date"
		. "\n WHERE poll_id = $poll->id"
		;
		$database->setQuery( $query );
		$dates = $database->loadObjectList();

		if (isset($dates[0]->mindate)) {
			$first_vote = mosFormatDate( $dates[0]->mindate, $_LANG->_( 'DATE_FORMAT_LC2' ) );
			$last_vote = mosFormatDate( $dates[0]->maxdate, $_LANG->_( 'DATE_FORMAT_LC2' ) );
		}
		
		$query = "SELECT a.id, a.text, count( DISTINCT b.id ) AS hits, count( DISTINCT b.id )/COUNT( DISTINCT a.id )*100.0 AS percent"
		. "\n FROM #__poll_data AS a"
		. "\n LEFT JOIN #__poll_date AS b ON b.vote_id = a.id"
		. "\n WHERE a.pollid = $poll->id"
		. "\n AND a.text <> ''"
		. "\n GROUP BY a.id"
		. "\n ORDER BY a.id"
		;
		$database->setQuery( $query );
		$votes = $database->loadObjectList();
		
	}

	$query = "SELECT id, title"
	. "\n FROM #__polls"
	. "\n WHERE published = 1"
	. "\n ORDER BY id"
	;
	$database->setQuery( $query );
	$polls = $database->loadObjectList();

	reset( $polls );
	$link = sefRelToAbs( 'index.php?option=com_poll&amp;task=results&amp;id=\' + this.options[selectedIndex].value + \'&amp;Itemid='. $Itemid .'\' + \'' );
	$pollist = '<select name="id" class="inputbox" size="1" style="width:200px" onchange="if (this.options[selectedIndex].value != \'\') {document.location.href=\''. $link .'\'}">';
	$pollist .= '<option value="">'. $_LANG->_( 'Select Poll from the list' ) .'</option>';
	for ($i=0, $n=count( $polls ); $i < $n; $i++ ) {
		$k = $polls[$i]->id;
		$t = $polls[$i]->title;

		$sel = ($k == intval( $poll->id ) ? " selected=\"selected\"" : '');
		$pollist .= "\n\t<option value=\"".$k."\"$sel>" . $t . "</option>";
	}
	$pollist .= '</select>';

	// Adds parameter handling
	$menu = new mosMenu( $database );
	$menu->load( $Itemid );

	$params = new mosParameters( $menu->params );
	$params->def( 'page_title', 1 );
	$params->def( 'pageclass_sfx', '' );
	$params->def( 'back_button', $mainframe->getCfg( 'back_button' ) );
	$params->def( 'header', $menu->name );

	$mainframe->SetPageTitle($poll->title);

	poll_html::showResults( $poll, $votes, $first_vote, $last_vote, $pollist, $params );
}
?>
