<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $_CB_database;

if ( ( ! file_exists( JPATH_SITE . '/libraries/CBLib/CBLib/Core/CBLib.php' ) ) || ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) ) {
	echo 'CB not installed'; return;
}

include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );

cbimport( 'cb.html' );
cbimport( 'language.front' );

outputCbTemplate();

require_once( dirname( __FILE__ ) . '/helper.php' );

if ( (int) $params->get( 'cb_plugins', 1 ) ) {
	global $_PLUGINS;

	$_PLUGINS->loadPluginGroup( 'user' );
}

$cbUser						=	CBuser::getMyInstance();
$user						=	$cbUser->getUserData();
$templateClass				=	'cb_template cb_template_' . selectTemplate( 'dir' );

$mode						=	(int) $params->get( 'mode', 1 );

if ( $params->get( 'pretext' ) ) {
	$preText				=	$cbUser->replaceUserVars( $params->get( 'pretext' ) );
} else {
	$preText				=	null;
}

if ( $params->get( 'posttext' ) ) {
	$postText				=	$cbUser->replaceUserVars( $params->get( 'posttext' ) );
} else {
	$postText				=	null;
}

if ( $mode < 6 ) {
	$limit					=	(int) $params->get( 'limit', 30 );

	switch( $mode ) {
		case 5:
			$field			=	$params->get( 'custom_field' );
			$direction		=	$params->get( 'custom_direction', 'ASC' );

			if ( $field ) {
				if ( in_array( $field, array( 'id', 'name', 'username', 'email', 'registerDate', 'lastvisitDate', 'params' ) ) ) {
					$table	=	'u.';
				} else {
					$table	=	'c.';
				}

				$query		=	'SELECT DISTINCT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n ORDER BY " . $table . $_CB_database->NameQuote( $field ) . " " . $direction;
				$_CB_database->setQuery( $query, 0, $limit );
				$userIds	=	$_CB_database->loadResultArray();
			} else {
				$userIds	=	array();
			}
			break;
		case 4:
			$query			=	'SELECT DISTINCT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n ORDER BY c." . $_CB_database->NameQuote( 'lastupdatedate' ) . " DESC";
			$_CB_database->setQuery( $query, 0, $limit );
			$userIds		=	$_CB_database->loadResultArray();
			break;
		case 3:
			$query			=	'SELECT DISTINCT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query, 0, $limit );
			$userIds		=	$_CB_database->loadResultArray();
			break;
		case 2:
			$query			=	'SELECT DISTINCT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'lastvisitDate' ) . " DESC";
			$_CB_database->setQuery( $query, 0, $limit );
			$userIds		=	$_CB_database->loadResultArray();
			break;
		default:
			$query			=	'SELECT DISTINCT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__session' ) . " AS s"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = s.' . $_CB_database->NameQuote( 'userid' )
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE s." . $_CB_database->NameQuote( 'client_id' ) . " = 0"
							.	"\n AND s." . $_CB_database->NameQuote( 'guest' ) . " = 0"
							.	"\n ORDER BY s." . $_CB_database->NameQuote( 'time' ) . " DESC";
			$_CB_database->setQuery( $query, 0, $limit );
			$userIds		=	$_CB_database->loadResultArray();
			break;
	}

	$cbUsers				=	array();

	if ( $userIds ) foreach ( $userIds as $userId ) {
		$cbUser				=&	CBuser::getInstance( (int) $userId );

		if ( $cbUser ) {
			$cbUsers[]		=	$cbUser;
		}
	}

	require JModuleHelper::getLayoutPath( 'mod_comprofileronline', $params->get( 'layout', 'default' ) );
} else {
	$label					=	(int) $params->get( 'label', 1 );
	$separator				=	$params->get( 'separator', ',' );

	if ( $label > 1 ) {
		outputCbTemplate();
	}

	switch( $mode ) {
		case 7:
			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0";
			$_CB_database->setQuery( $query );
			$totalUsers		=	$_CB_database->loadResult();

			$query			=	'SELECT u.' . $_CB_database->NameQuote( 'id' )
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query, 0, 1 );
			$userId			=	$_CB_database->loadResult();

			$latestUser		=&	CBuser::getInstance( (int) $userId );

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__session' ) . " AS s"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = s.' . $_CB_database->NameQuote( 'userid' )
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE s." . $_CB_database->NameQuote( 'client_id' ) . " = 0"
							.	"\n AND s." . $_CB_database->NameQuote( 'guest' ) . " = 0";
			$_CB_database->setQuery( $query );
			$onlineUsers	=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " < DATE_ADD( CURDATE(), INTERVAL 1 DAY )"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " >= CURDATE()"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query );
			$usersToday		=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " <= DATE_ADD( DATE_SUB( CURDATE(), INTERVAL DAYOFWEEK( CURDATE() ) DAY ), INTERVAL 1 WEEK )"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " > DATE_SUB( CURDATE(), INTERVAL DAYOFWEEK( CURDATE() ) DAY )"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query );
			$usersWeek		=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " <= DATE_ADD( DATE_SUB( CURDATE(), INTERVAL DAYOFMONTH( CURDATE() ) DAY ), INTERVAL 1 MONTH )"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " > DATE_SUB( CURDATE(), INTERVAL DAYOFMONTH( CURDATE() ) DAY )"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query );
			$usersMonth		=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " <= DATE_ADD( DATE_SUB( CURDATE(), INTERVAL DAYOFYEAR( CURDATE() ) DAY ), INTERVAL 1 YEAR )"
							.	"\n AND u." . $_CB_database->NameQuote( 'registerDate' ) . " > DATE_SUB( CURDATE(), INTERVAL DAYOFYEAR( CURDATE() ) DAY )"
							.	"\n ORDER BY u." . $_CB_database->NameQuote( 'registerDate' ) . " DESC";
			$_CB_database->setQuery( $query );
			$usersYear		=	$_CB_database->loadResult();

			require JModuleHelper::getLayoutPath( 'mod_comprofileronline', '_census' );
			break;
		case 6:
			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__session' ) . " AS s"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = s.' . $_CB_database->NameQuote( 'userid' )
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n WHERE s." . $_CB_database->NameQuote( 'client_id' ) . " = 0"
							.	"\n AND s." . $_CB_database->NameQuote( 'guest' ) . " = 0";
			$_CB_database->setQuery( $query );
			$onlineUsers	=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( DISTINCT u.' . $_CB_database->NameQuote( 'id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
							.	"\n INNER JOIN " . $_CB_database->NameQuote( '#__users' ) . " AS u"
							.	' ON u.' . $_CB_database->NameQuote( 'id' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__session' ) . " AS s"
							.	' ON s.' . $_CB_database->NameQuote( 'userid' ) . ' = c.' . $_CB_database->NameQuote( 'id' )
							.	' AND s.' . $_CB_database->NameQuote( 'client_id' ) . ' = 0'
							.	"\n WHERE c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND c." . $_CB_database->NameQuote( 'approved' ) . " = 1"
							.	"\n AND u." . $_CB_database->NameQuote( 'block' ) . " = 0"
							.	"\n AND s." . $_CB_database->NameQuote( 'session_id' ) . " IS NULL";
			$_CB_database->setQuery( $query );
			$offlineUsers	=	$_CB_database->loadResult();

			$query			=	'SELECT COUNT( ' . $_CB_database->NameQuote( 'session_id' ) . ' )'
							.	"\n FROM " . $_CB_database->NameQuote( '#__session' )
							.	"\n WHERE " . $_CB_database->NameQuote( 'client_id' ) . " = 0"
							.	"\n AND " . $_CB_database->NameQuote( 'guest' ) . " = 1";
			$_CB_database->setQuery( $query );
			$guestUsers		=	$_CB_database->loadResult();

			require JModuleHelper::getLayoutPath( 'mod_comprofileronline', '_statistics' );
			break;
	}
}