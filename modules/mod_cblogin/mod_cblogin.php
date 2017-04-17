<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_framework, $ueConfig, $_CB_PMS, $cbSpecialReturnAfterLogin, $cbSpecialReturnAfterLogout;

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

$type								=	modCBLoginHelper::getType();
$cbUser								=	CBuser::getMyInstance();

if ( ! $cbUser ) {
	$cbUser							=	CBuser::getInstance( null );
}

$user								=	$cbUser->getUserData();
$livePath							=	$_CB_framework->getCfg( 'live_site' ) . '/modules/mod_cblogin';
$templateClass						=	'cb_template cb_template_' . selectTemplate( 'dir' );

$moduleLayout						=	$params->get( 'layout', '_:bootstrap' );
$showButton							=	(int) $params->get( 'show_buttons_icons', 0 );
$secureForm							=	(int) $params->get( 'https_post', 0 );
$showUsernameLabel					=	(int) $params->get( 'name_label', 5 );
$usernameInputLength				=	(int) $params->get( 'name_length', 14 );
$showPasswordLabel					=	(int) $params->get( 'pass_label', 5 );
$passwordInputLength				=	(int) $params->get( 'pass_length', 14 );
$showSecretKeyLabel					=	(int) $params->get( 'key_label', 5 );
$secretKeyInputLength				=	(int) $params->get( 'key_length', 14 );
$showRememberMe						=	(int) $params->get( 'remember_enabled', 1 );
$showForgotLogin					=	(int) $params->get( 'show_lostpass', 1 );
$showRegister						=	( ( $_CB_framework->getCfg( 'allowUserRegistration' ) || ( isset( $ueConfig['reg_admin_allowcbregistration'] ) && ( $ueConfig['reg_admin_allowcbregistration'] == 1 ) ) ) && $params->get( 'show_newaccount', 1 ) );
$showPrivateMessages				=	(int) $params->get( 'show_pms', 0 );
$showConnectionRequests				=	(int) $params->get( 'show_connection_notifications', 0 );

if ( $params->get( 'logoutpretext' ) ) {
	$preLogoutText					=	$cbUser->replaceUserVars( $params->get( 'logoutpretext' ) );
} else {
	$preLogoutText					=	null;
}

if ( $params->get( 'logoutposttext' ) ) {
	$postLogoutText					=	$cbUser->replaceUserVars( $params->get( 'logoutposttext' ) );
} else {
	$postLogoutText					=	null;
}

if ( $params->get( 'text_show_profile' ) ) {
	$profileViewText				=	$cbUser->replaceUserVars( $params->get( 'text_show_profile' ) );
} else {
	$profileViewText				=	null;
}

if ( $params->get( 'text_edit_profile' ) ) {
	$profileEditText				=	$cbUser->replaceUserVars( $params->get( 'text_edit_profile' ) );
} else {
	$profileEditText				=	null;
}

$greetingText						=	$cbUser->replaceUserVars( CBTxt::T( 'Hi, [formatname]' ) );

if ( $params->get( 'pretext' ) ) {
	$preLogintText					=	$cbUser->replaceUserVars( $params->get( 'pretext' ) );
} else {
	$preLogintText					=	null;
}

if ( $params->get( 'posttext' ) ) {
	$postLoginText					=	$cbUser->replaceUserVars( $params->get( 'posttext' ) );
} else {
	$postLoginText					=	null;
}

$loginMethod						=	( isset( $ueConfig['login_type'] ) ? (int) $ueConfig['login_type'] : 0 );

if ( $loginMethod == 4 ) {
	$showForgotLogin				=	0;
}

switch ( $loginMethod ) {
	case 2:
		$userNameText				=	CBTxt::T( 'Email' );
		break;
	case 1:
		$userNameText				=	CBTxt::T( 'Username or email' );
		break;
	case 0:
	default:
		$userNameText				=	CBTxt::T( 'Username' );
		break;
}

$loginReturnUrl						=	modCBLoginHelper::getReturnURL( $params, $type );
$logoutReturnUrl					=	modCBLoginHelper::getReturnURL( $params, $type );

if ( in_array( $showButton, array( 2, 4 ) ) ) {
	$buttonStyle					=	' style="color: black; font-weight: normal; box-shadow: none; text-shadow: none; background: none; border: 0; padding: 0;"';
} else {
	$buttonStyle					=	null;
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

$twoFactorMethods					=	modCBLoginHelper::getTwoFactorMethods();

if ( $type == 'logout' ) {
	$moduleLayout					.=	'_logout';
}

require JModuleHelper::getLayoutPath( 'mod_cblogin', $moduleLayout );