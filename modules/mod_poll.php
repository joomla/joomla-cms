<?php
/**
* @version $Id: mod_poll.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );



class modPollData {

	function &getVars( &$params ){
		if ( !defined( '_MOS_POLL_MODULE' ) ) {
			/** ensure that functions are declared only once */
			define( '_MOS_POLL_MODULE', 1 );

			global $database;

			$Itemid = mosGetParam( $_REQUEST, 'Itemid', 0 );

			$query1 = "SELECT p.id, p.title"
			. "\n FROM #__poll_menu AS pm, #__polls AS p"
			. "\n WHERE ( pm.menuid = '$Itemid' OR pm.menuid = '0')"
			. "\n AND p.id = pm.pollid"
			. "\n AND p.published = 1";
			$database->setQuery( $query1 );
			$polls = $database->loadObjectList();

			if ( $database->getErrorNum() ) {
				mosErrorAlert( 'MB '. $database->stderr() );
			}

			foreach ( $polls as $poll ) {
				if ( $poll->id && $poll->title ) {
					$query = "SELECT id, text FROM #__poll_data"
					. "\n WHERE pollid = '$poll->id'"
					. "\n AND text <> ''"
					. "\n ORDER BY id";
					$database->setQuery( $query );
					if ( !( $rows = $database->loadObjectList() ) ) {
						mosErrorAlert( 'MD '. $database->stderr() );
					}

					// Itemid
					$query = "SELECT a.id"
					. "\n FROM #__menu AS a"
					. "\n LEFT JOIN #__components AS b ON a.componentid = b.id"
					. "\n WHERE b.link = 'option=com_poll'"
					;
					$database->setQuery( $query );
					$Itemid = $database->loadResult();
					$Itemid = ( $Itemid ? $Itemid : 0 );

					$lists['url_results'] 	= sefRelToAbs( 'index.php?option=com_poll&amp;task=results&amp;id='. $poll->id .'&amp;Itemid='. $Itemid );
					$lists['url_form'] 		= sefRelToAbs( 'index.php?option=com_poll&amp;Itemid='. $Itemid );
					$lists['title'] 		= $poll->title;
					$lists['id'] 			= $poll->id;

					return array( $rows, $lists );
				}
			}
		}
	}
}

class modPoll {

	function show( &$params ){
		$Itemid = mosGetParam( $_REQUEST, 'Itemid', 0 );

		$cache  = mosFactory::getCache("mod_poll");

		$cache->setCaching($params->get('cache', 1));
		$cache->setCacheValidation(false);

		$cache->callId("modPoll::_display", array( $params ), "mod_poll$Itemid");
	}

	function _display( &$params ) {

		$vars = modPollData::getVars( $params );
		$rows = $vars[0];
		$lists = $vars[1];

		$tmpl =& moduleScreens::createTemplate( 'mod_poll.html' );

		$tmpl->addVar( 'mod_poll', 'class', 		$params->get( 'moduleclass_sfx' ) );
		$tmpl->addVar( 'mod_poll', 'url_results',	$lists['url_results'] );
		$tmpl->addVar( 'mod_poll', 'url_form', 		$lists['url_form'] );
		$tmpl->addVar( 'mod_poll', 'title', 		$lists['title'] );
		$tmpl->addVar( 'mod_poll', 'id', 			$lists['id'] );

		$tmpl->addObject( 'poll-options', 	$rows, 'row_' );

		$tmpl->displayParsedTemplate( 'mod_poll' );
	}
}

modPoll::show( $params );
?>