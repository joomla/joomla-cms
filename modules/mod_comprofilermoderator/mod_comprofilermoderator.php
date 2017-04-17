<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $_CB_database, $ueConfig, $_CB_PMS;

if ( ( ! file_exists( JPATH_SITE . '/libraries/CBLib/CBLib/Core/CBLib.php' ) ) || ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) ) {
	echo 'CB not installed'; return;
}

include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );

if ( ! ( $_CB_framework->myId() > 0 ) ) {
	return;
}

cbimport( 'cb.html' );
cbimport( 'language.front' );

require_once( dirname( __FILE__ ) . '/helper.php' );

if ( (int) $params->get( 'cb_plugins', 1 ) ) {
	global $_PLUGINS;

	$_PLUGINS->loadPluginGroup( 'user' );
}

$cbUser								=	CBuser::getMyInstance();

if ( ! $cbUser ) {
	$cbUser							=	CBuser::getInstance( null );
}

$user								=	$cbUser->getUserData();

$showBanned							=	(int) $params->get( 'show_banned', 1 );
$showImageApproval					=	(int) $params->get( 'show_image_approval', 1 );
$showUserReports					=	(int) $params->get( 'show_user_reports', 1 );
$showUnbanRequests					=	(int) $params->get( 'show_uban_requests', 1 );
$showUserApproval					=	(int) $params->get( 'show_user_approval', 1 );
$showPrivateMessages				=	(int) $params->get( 'show_pms', 1 );
$showConnectionRequests				=	(int) $params->get( 'show_connections', 1 );

if ( $params->get( 'pretext' ) ) {
	$preText						=	$cbUser->replaceUserVars( $params->get( 'pretext' ) );
} else {
	$preText						=	null;
}

if ( $params->get( 'posttext' ) ) {
	$postText						=	$cbUser->replaceUserVars( $params->get( 'posttext' ) );
} else {
	$postText						=	null;
}

$bannedStatus						=	(int) $user->get( 'banned' );

if ( $showBanned && ( ! $bannedStatus ) ) {
	$showBanned						=	0;
}

if ( isModerator( (int) $user->get( 'id' ) ) ) {
	if ( $showImageApproval ) {
		$query						=	'SELECT ' . $_CB_database->NameQuote( 'name' )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'image' );
		$_CB_database->setQuery( $query );
		$imageFields				=	$_CB_database->loadResultArray();

		$imgApprovSelect			=	array();
		$imgApprovWhere				=	array();

		if ( $imageFields ) foreach ( $imageFields as $imageField ) {
			$imgApprovSelect[]		=	$_CB_database->NameQuote( $imageField . 'approved' );
			$imgApprovWhere[]		=	"( " . $_CB_database->NameQuote( $imageField ) . " != '' AND " . $_CB_database->NameQuote( $imageField ) . " IS NOT NULL AND " . $_CB_database->NameQuote( $imageField . 'approved' ) . " = 0 )";
		}

		$query						=	'SELECT ' . implode( ', ', $imgApprovSelect )
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
									.	"\n WHERE ( " . implode( ' OR ', $imgApprovWhere ) . " )"
									.	"\n AND " . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'banned' ) . " = 0";
		$_CB_database->setQuery( $query );
		$imageApprovalResults		=	$_CB_database->loadAssocList();

		$imageApprovalCount			=	0;

		if ( $imageApprovalResults ) foreach ( $imageApprovalResults as $imageApprovalResult ) {
			foreach ( $imageApprovalResult as $imageCol ) {
				if ( $imageCol == 0 ) {
					$imageApprovalCount++;
				}
			}
		}

		if ( ! $imageApprovalCount ) {
			$showImageApproval		=	0;
		}
	} else {
		$imageApprovalCount			=	0;
	}

	if ( $showUserReports ) {
		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0";
		$_CB_database->setQuery( $query );
		$userReportsCount			=	$_CB_database->loadResult();

		if ( ! $userReportsCount ) {
			$showUserReports		=	0;
		}
	} else {
		$userReportsCount			=	0;
	}

	if ( $showUnbanRequests ) {
		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'banned' ) . " = 2"
									.	"\n AND " . $_CB_database->NameQuote( 'approved' ) . " = 1"
									.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
		$_CB_database->setQuery( $query );
		$unbanRequestCount			=	$_CB_database->loadResult();

		if ( ! $unbanRequestCount ) {
			$showUnbanRequests		=	0;
		}
	} else {
		$unbanRequestCount			=	0;
	}

	if ( $showUserApproval && ( isset( $ueConfig['allowModUserApproval'] ) && $ueConfig['allowModUserApproval'] ) ) {
		$query						=	'SELECT COUNT(*)'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
									.	"\n WHERE " . $_CB_database->NameQuote( 'approved' ) . " = 0"
									.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
		$_CB_database->setQuery( $query );
		$userApprovalCount			=	$_CB_database->loadResult();

		if ( ! $userApprovalCount ) {
			$showUserApproval		=	0;
		}
	} else {
		$showUserApproval			=	0;
		$userApprovalCount			=	0;
	}
} else {
	$showImageApproval				=	0;
	$imageApprovalCount				=	0;
	$showUserReports				=	0;
	$userReportsCount				=	0;
	$showUnbanRequests				=	0;
	$unbanRequestCount				=	0;
	$showUserApproval				=	0;
	$userApprovalCount				=	0;
}

if ( $showPrivateMessages && $_CB_PMS ) {
	$newMessageCount				=	$_CB_PMS->getPMSunreadCount( (int) $user->get( 'id' ) );
	$privateMessageURL				=	$_CB_PMS->getPMSlinks( null, (int) $user->get( 'id' ), null, null, 2 );

	if ( isset( $newMessageCount[0] ) ) {
		$newMessageCount			=	(int) $newMessageCount[0];
	} else {
		$newMessageCount			=	0;
	}

	if ( isset( $privateMessageURL[0]['url'] ) ) {
		$privateMessageURL			=	cbSef( $privateMessageURL[0]['url'] );
	} else {
		$privateMessageURL			=	$_CB_framework->userProfileUrl();
	}

	if ( ( $showPrivateMessages == 1 ) && ( ! $newMessageCount ) ) {
		$showPrivateMessages		=	0;
	}
} else {
	$showPrivateMessages			=	0;
	$newMessageCount				=	0;
	$privateMessageURL				=	$_CB_framework->userProfileUrl();
}

if ( $showConnectionRequests && ( isset( $ueConfig['allowConnections'] ) && $ueConfig['allowConnections'] ) ) {
	$cbConnections					=	new cbConnection( (int) $user->get( 'id' ) );
	$newConnectionRequests			=	(int) $cbConnections->getPendingConnectionsCount( (int) $user->get( 'id' ) );

	if ( ( $showConnectionRequests == 1 ) && ( ! $newConnectionRequests ) ) {
		$showConnectionRequests		=	0;
	}
} else {
	$showConnectionRequests			=	0;
	$newConnectionRequests			=	0;
}

require JModuleHelper::getLayoutPath( 'mod_comprofilermoderator', $params->get( 'layout', 'default' ) );