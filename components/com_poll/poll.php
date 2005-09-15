<?php
/**
* @version $Id: poll.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

mosFS::load( '@class' );

/**
 * @package Poll
 * @subpackage Poll
 */
class pollTasks_Front extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function pollTasks_Front() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'result' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );
	}

	function result() {
		global $database, $Itemid;
		global $mainframe, $_LANG;

		$uid = intval( mosGetParam( $_REQUEST, 'id', 0 ) );

		$poll = new mosPoll( $database );
		$poll->load( $uid );

		if ( empty( $poll->title ) ) {
			$poll->id 		= '';
			$poll->title 	= $_LANG->_( 'SELECT_POLL' );
		}

		$first_vote = '';
		$last_vote 	= '';
		$total_vote = '';
		if ( isset( $poll->id ) && $poll->id != '' ) {
			$query = "SELECT MIN( date ) AS mindate, MAX( date ) AS maxdate"
			."\n FROM #__poll_date"
			."\n WHERE poll_id = '$poll->id'"
			;
			$database->setQuery( $query );
			$dates = $database->loadObjectList();

			if ( isset( $dates[0]->mindate ) ) {
				$first_vote = mosFormatDate( $dates[0]->mindate, $_LANG->_( 'DATE_FORMAT_LC2' ) );
				$last_vote 	= mosFormatDate( $dates[0]->maxdate, $_LANG->_( 'DATE_FORMAT_LC2' ) );
			}
		}

	$query = "SELECT a.id, a.text, count( DISTINCT b.id ) AS hits, count( DISTINCT b.id )/COUNT( DISTINCT a.id )*100.0 AS percent"
		. "\n FROM #__poll_data AS a"
		. "\n LEFT JOIN #__poll_date AS b ON b.vote_id = a.id"
		. "\n WHERE a.pollid = '$poll->id'"
		. "\n AND a.text <> ''"
		. "\n GROUP BY a.id"
		. "\n ORDER BY a.id"
		;
		$database->setQuery( $query );
		$votes = $database->loadObjectList();

		$query = "SELECT id, title"
		. "\n FROM #__polls"
		. "\n WHERE published = 1"
		. "\n ORDER BY id"
		;
		$database->setQuery( $query );
		$polls = $database->loadObjectList();

		reset( $polls );
		$link 		= sefRelToAbs( 'index.php?option=com_poll&amp;task=results&amp;id=\' + this.options[selectedIndex].value + \'&amp;Itemid='. $Itemid .'\' + \'' );
		$pollist 	= '<select name="id" id="polllist" class="inputbox" size="1" style="width:200px" onchange="if (this.options[selectedIndex].value != \'\') {document.location.href=\''. $link .'\'}">';
		$pollist 	.= '<option value="">'. $_LANG->_( 'SELECT_POLL' ) .'</option>';
		$n 			= count( $polls );
		for ( $i=0; $i < $n; $i++ ) {
			$k = $polls[$i]->id;
			$t = $polls[$i]->title;
			$sel = ( $k == intval( $poll->id ) ? 'selected="selected"' : '' );
			$pollist .= '<option value="'. $k .'" '. $sel .'>' . $t . '</option>';
		}
		$pollist .= '</select>';

		// Adds parameter handling
		$menu = new mosMenu( $database );
		$menu->load( $Itemid );

		$params = new mosParameters( $menu->params );
		$params->def( 'page_title', 	1 );
		$params->def( 'pageclass_sfx', 	'' );
		$params->def( 'back_button', 	$mainframe->getCfg( 'back_button' ) );
		$params->def( 'header', 		$menu->name );
		$params->def( 'meta_key', 		'' );
		$params->def( 'meta_descrip', 	'' );
		$params->def( 'seo_title', 		$menu->name );

		if ( $votes ) {
			$data_arr['text'] = null;
			$data_arr['hits'] = null;
			$j = 0;
			foreach ( $votes as $vote ) {
				$data_arr['text'][$j] = trim( $vote->text );
				$data_arr['hits'][$j] = $vote->hits;
				$j++;
			}
			$tabclass 			= explode( ',', 'sectiontableentry2, sectiontableentry1' );
			$polls_graphwidth 	= 200;
			$polls_maxcolors 	= 5;
			$polls_barcolor 	= 0;
			$colorx 			= 0;
			$maxval 			= 0;

			array_multisort( $data_arr['hits'], SORT_NUMERIC, SORT_DESC, $data_arr['text'] );

			foreach( $data_arr['hits'] as $hits ) {
				if ( $maxval < $hits ) {
					$maxval = $hits;
				}
			}
			$total_vote = array_sum( $data_arr['hits'] );

			$j = 0;
			$n = count( $data_arr['text'] );
			for ( $i=0; $i < $n; $i++) {
				$rows[$i]->text =& $data_arr['text'][$i];
				$hits 			=& $data_arr['hits'][$i];
				$rows[$i]->hits = $hits;

				if ( $maxval > 0 && $total_vote > 0 ) {
					$rows[$i]->width	= ceil( $hits * $polls_graphwidth / $maxval );
					$rows[$i]->percent 	= round( 100 * $hits / $total_vote, 1 );
				} else {
					$rows[$i]->width	= 0;
					$rows[$i]->percent 	= 0;
				}

				$tdclass = '';
				if ( $polls_barcolor == 0 ) {
					if ( $colorx < $polls_maxcolors ) {
						$colorx = ++$colorx;
					} else {
						$colorx = 1;
					}
					$rows[$i]->image_class = 'polls_color_'. $colorx;
				} else {
					$rows[$i]->image_class = 'polls_color_'. $polls_barcolor;
				}

				$rows[$i]->class	= $tabclass[$j];
				 $j = 1 - $j;
			}
		}

		$params->set( 'show_poll', 	( $uid ? 1 : 0 ) );
		$params->set( 'poll_list', 	$pollist );
		$params->set( 'first_vote',	$first_vote );
		$params->set( 'last_vote', 	$last_vote );
		$params->set( 'total_vote', $total_vote );

		mosFS::load( '@front_html' );

		// SEO Meta Tags
		$mainframe->setPageMeta( $params->get( 'seo_title' ), $params->get( 'meta_key' ), $params->get( 'meta_descrip' ) );

		pollScreens_front::displaylist( $params, $poll, $rows	);
	}

	function vote() {
		global $database, $Itemid, $mainframe;
		global $_LANG;

		mosFS::load( '@front_html' );

		$sessioncookie 	= mosGetParam( $_REQUEST, 'sessioncookie', '' );
		// Error Check - Cookies must be enabled
		if ( !$sessioncookie ) {
			$text = $_LANG->_( 'ALERT_ENABLED' );
			pollScreens_front::vote( $text );
			return;
		}

		$uid	= intval( mosGetParam( $_REQUEST, 'id', 0 ) );
		$poll 	= new mosPoll( $database );

		// Error Check - Not Authorized to Vote
		if ( !$poll->load( $uid ) ) {
			$text = $_LANG->_('NOT_AUTH');
			pollScreens_front::vote( $text );
			return;
		}

		$redirect 		= 1;
		$cookiename 	= 'voted'. $poll->id;
		$voteid 		= mosGetParam( $_POST, 'voteid', 0 );
		$voted 			= mosGetParam( $_COOKIE, $cookiename, '0' );

		// Error Check - Already Voted
		if ( $voted ) {
			$text = $_LANG->_( 'ALREADY_VOTE' );
			pollScreens_front::vote( $text );
			return;
		}

		// Error Check - No Poll selection
		if ( !$voteid ) {
			$text = $_LANG->_( 'NO_SELECTION' );
			pollScreens_front::vote( $text );
			return;
		}

		setcookie( $cookiename, '1', time() + $poll->lag );

		$query = "UPDATE #__poll_data"
		. "\n SET hits = hits + 1"
		. "\n WHERE pollid = '$poll->id'"
		. "\n AND id = '$voteid'"
		;
		$database->setQuery( $query );
		$database->query();

		$query = "UPDATE #__polls"
		. "\n SET voters = voters + 1"
		. "\n WHERE id = '$poll->id'"
		;
		$database->setQuery( $query );
		$database->query();

		$now = $mainframe->getDateTime();

		$query = "INSERT INTO #__poll_date"
		. "\n SET date = '$now', vote_id='$voteid',	poll_id='$poll->id'"
		;
		$database->setQuery( $query );
		$database->query();

		// Itemid
		$query = "SELECT a.id"
		. "\n FROM #__menu AS a"
		. "\n LEFT JOIN #__components AS b ON a.componentid = b.id"
		. "\n WHERE b.link = 'option=com_poll'"
		;
		$database->setQuery( $query );
		$Itemid = $database->loadResult();
		$Itemid = ( $Itemid ? $Itemid : 0 );

		$link = sefRelToAbs( 'index.php?option=com_poll&task=results&id='. $uid .'&Itemid='. $Itemid );
		if ( $redirect ) {
			mosRedirect( $link, $_LANG->_( 'THANKS' ) );
		} else {
			$text = $_LANG->_( 'THANKS' );
			pollScreens_front::vote( $text, $link, 1 );
		}
	}
}

$tasker = new pollTasks_Front();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>