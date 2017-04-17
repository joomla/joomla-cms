<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Registry\Registry;
use CB\Database\Table\UserTable;
use CB\Database\Table\UserReportTable;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

$memMax				=	trim( @ini_get( 'memory_limit' ) );
if ( $memMax ) {
	$last			=	strtolower( $memMax{strlen( $memMax ) - 1} );
	switch( $last ) {
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'g':
			$memMax	*=	1024;
		/** @noinspection PhpMissingBreakStatementInspection */
		case 'm':
			$memMax	*=	1024;
		case 'k':
			$memMax	*=	1024;
	}
	if ( $memMax < 16000000 ) {
		@ini_set( 'memory_limit', '16M' );
	}
	if ( $memMax < 24000000 ) {
		@ini_set( 'memory_limit', '24M' );
	}
	if ( $memMax < 32000000 ) {
		@ini_set( 'memory_limit', '32M' );
	}
	if ( $memMax < 64000000 ) {
		@ini_set( 'memory_limit', '64M' );
	}
	if ( $memMax < 80000000 ) {
		@ini_set( 'memory_limit', '80M' );
	}
}

/**
 * CB framework
 * @global CBframework $_CB_framework
 */
global $_CB_framework;
/** @global array $ueConfig
 */
global $ueConfig;
/** @noinspection PhpIncludeInspection */
include_once JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php';
/** @noinspection PhpIncludeInspection */
require_once $_CB_framework->getCfg( 'absolute_path' ) . '/components/com_comprofiler/comprofiler.html.php';

if($_CB_framework->getCfg( 'debug' )) {
	ini_set('display_errors',true);
	error_reporting(E_ALL );	// | E_STRICT );
}

cbimport( 'language.front' );

cbimport( 'cb.tabs' );

if ( class_exists( 'JFactory' ) ) {	// Joomla 1.5 : for string WARNREG_EMAIL_INUSE used in error js popup.
	$lang			=	JFactory::getLanguage();
	$lang->load( "com_user" );
}

// We don't use view so lets map it to task before we grab task:
cbMapViewToTask();

$option				=	$_CB_framework->getRequestVar( 'option' );
$task				=	$_CB_framework->getRequestVar( 'view' );
$form				=	cbGetParam( $_REQUEST, 'reportform', 1 );
$uid				=	cbGetParam( $_REQUEST, 'uid', 0 );
$act				=	cbGetParam( $_REQUEST, 'act', 1 );

$oldignoreuserabort	=	null;

$_CB_framework->document->outputToHeadCollectionStart();
ob_start();

switch( $task ) {

	case "userDetails":
	case "userdetails":
	userEdit( $option, $uid, CBTxt::T( 'UE_UPDATE', 'Update' ) );
	break;

	case "saveUserEdit":
	case "saveuseredit":
	$oldignoreuserabort = ignore_user_abort(true);
	userSave( $option, (int) cbGetParam( $_POST, 'id', 0 ) );
	break;

	case "userProfile":
	case "userprofile":
	userProfile($option, $_CB_framework->myId(), CBTxt::T( 'UE_UPDATE', 'Update' ));
	break;

	case "usersList":
	case "userslist":
	usersList( $_CB_framework->myId() );
	break;

	case "lostPassword":
	case "lostpassword":
	lostPassForm( $option );
	break;

	case "sendNewPass":
	case "sendnewpass":
	$oldignoreuserabort = ignore_user_abort(true);
	sendNewPass( $option );
	break;

	case "registers":
	registerForm( $option, isset( $ueConfig['emailpass'] ) ? $ueConfig['emailpass'] : '0' );
	break;

	case "saveregisters":
	$oldignoreuserabort = ignore_user_abort(true);
	saveRegistration( $option );
	break;

	case "login":
	$oldignoreuserabort = ignore_user_abort(true);
	login();
	break;

	case "logout":
	$oldignoreuserabort = ignore_user_abort(true);
	logout();
	break;

	case "confirm":
	$oldignoreuserabort = ignore_user_abort(true);
	confirm( cbGetParam( $_GET, 'confirmcode', '1' ) );		// mambo 4.5.3h braindead: does intval of octal from hex in cbGetParam...
	break;

	case "moderateImages":
	case "moderateimages":
	$oldignoreuserabort = ignore_user_abort(true);
	moderateImages($option);
	break;

	case "moderateReports":
	case "moderatereports":
	$oldignoreuserabort = ignore_user_abort(true);
	moderateReports($option);
	break;

	case "moderateBans":
	case "moderatebans":
	$oldignoreuserabort = ignore_user_abort(true);
	moderateBans($option,$act,$uid);
	break;

	case "approveImage":
	case "approveimage":
	$oldignoreuserabort = ignore_user_abort(true);
	approveImage();
	break;

	case "reportUser":
	case "reportuser":
	$oldignoreuserabort = ignore_user_abort(true);
	reportUser($option,$form,$uid);
	break;

	case "processReports":
	case "processreports":
	$oldignoreuserabort = ignore_user_abort(true);
	processReports();
	break;

	case "banProfile":
	case "banprofile":
	$oldignoreuserabort = ignore_user_abort(true);
	banUser($option,$uid,$form,$act);
	break;

	case "viewReports":
	case "viewreports":
	viewReports($option,$uid,$act);
	break;

	case "emailUser":
	case "emailuser":
	emailUser($option,$uid);
	break;

	case "pendingApprovalUser":
	case "pendingapprovaluser":
	pendingApprovalUsers($option);
	break;

	case "approveUser":
	case "approveuser":
	$oldignoreuserabort = ignore_user_abort(true);
	approveUser(cbGetParam($_POST,'uids'));
	break;

	case "rejectUser":
	case "rejectuser":
	$oldignoreuserabort = ignore_user_abort(true);
	rejectUser($option,cbGetParam($_POST,'uids'));
	break;

	case "sendUserEmail":
	case "senduseremail":
	$oldignoreuserabort = ignore_user_abort(true);
	sendUserEmail( $option, (int) cbGetParam( $_POST, 'toID', 0 ), (int) cbGetParam( $_POST, 'fromID', 0 ), cbGetParam( $_POST, 'emailName', '' ), cbGetParam( $_POST, 'emailAddress', '' ), cbGetParam( $_POST, 'emailSubject', '' ), cbGetParam( $_POST, 'emailBody', '' ) );
	break;

	case "addConnection":
	case "addconnection":
	$oldignoreuserabort = ignore_user_abort(true);
	addConnection( $_CB_framework->myId(), (int) cbGetParam($_REQUEST,'connectionid'), ((isset($_POST['message'])) ? cbGetParam($_POST,'message') : ""), $act);
	break;

	case "removeConnection":
	case "removeconnection":
	$oldignoreuserabort = ignore_user_abort(true);
	removeConnection( $_CB_framework->myId(), (int) cbGetParam($_REQUEST, 'connectionid'), $act );
	break;

	case "denyConnection":
	case "denyconnection":
	$oldignoreuserabort = ignore_user_abort(true);
	denyConnection( $_CB_framework->myId(), (int) cbGetParam($_REQUEST,'connectionid'), $act);
	break;

	case "acceptConnection":
	case "acceptconnection":
	$oldignoreuserabort = ignore_user_abort(true);
	acceptConnection( $_CB_framework->myId(), (int) cbGetParam($_REQUEST,'connectionid'), $act);
	break;

	case "manageConnections":
	case "manageconnections":
	manageConnections( $_CB_framework->myId() );
	break;

	case "saveConnections":
	case "saveconnections":
	$oldignoreuserabort = ignore_user_abort(true);
	saveConnections(cbGetParam($_POST,'uid'));
	break;

	case "processConnectionActions":
	case "processconnectionactions":
	$oldignoreuserabort = ignore_user_abort(true);
	processConnectionActions(cbGetParam($_POST,'uid'));
	break;

	case "teamCredits":
	case "teamcredits":
	teamCredits(1);
	break;

	case "fieldclass":
	case "tabclass":
	case "pluginclass":
	tabClass( $option, $task, $_CB_framework->myId() );
	break;

	case "done":
	break;

	case "performcheckusername":
	performCheckUsername( cbGetParam( $_POST, 'value' ), cbGetParam( $_GET, 'function' ) );
	break;

	case "performcheckemail":
	performCheckEmail( cbGetParam( $_POST, 'value' ), cbGetParam( $_GET, 'function' ) );
	break;

	default:
	userProfile($option, $_CB_framework->myId(), CBTxt::T( 'UE_UPDATE', 'Update' ));
	break;
}

if (!is_null($oldignoreuserabort)) ignore_user_abort($oldignoreuserabort);

$_CB_framework->getAllJsPageCodes();

$html		=	ob_get_contents();
ob_end_clean();

if ( ( cbGetParam( $_GET, 'no_html', 0 ) != 1 ) && ( cbGetParam( $_GET, 'format' ) != 'raw' ) ) {
	echo $_CB_framework->document->outputToHead();
}
echo $html;

// END OF MAIN.

function sendUserEmail( $option, $toId, $fromId, $emailName, $emailAddress, $subject, $message ) {
	global $ueConfig, $_CB_framework, $_POST, $_PLUGINS;

	$allowPublic							=	( isset( $ueConfig['allow_email_public'] ) ? (int) $ueConfig['allow_email_public'] : 0 );

	// simple spoof check:
	cbSpoofCheck( 'emailuser' );

	$errorMsg								=	cbAntiSpamCheck( false, $allowPublic );

	if ( ( ( $_CB_framework->myId() == 0 ) && ( ( ! $allowPublic ) || ( $allowPublic && ( ! $emailAddress ) ) ) ) || ( $_CB_framework->myId() != $fromId ) || ( ! $toId ) || ( ( $ueConfig['allow_email_display'] != 1 ) && ( $ueConfig['allow_email_display'] != 3 ) ) || ( ! CBuser::getMyInstance()->authoriseView( 'profile', $toId ) ) ) {
		cbNotAuth( true );
		return;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$rowFrom								=	new UserTable();

	$rowFrom->load( (int) $fromId );

	$rowTo									=	new UserTable();

	$rowTo->load( (int) $toId );

	$emailName								=	stripslashes( $emailName ); // remove slashes added by cbGetParam
	$emailAddress							=	stripslashes( $emailAddress ); // remove slashes added by cbGetParam
	$subject								=	stripslashes( $subject ); // remove slashes added by cbGetParam
	$message								=	stripslashes( $message ); // remove slashes added by cbGetParam

	if ( ! $errorMsg ) {
		$errorMsg							=	CBTxt::Th( 'UE_SESSIONTIMEOUT', 'Session timed out.' ) . ' ' . CBTxt::Th( 'UE_SENTEMAILFAILED', 'Your email failed to send! Please try again.' );

		if ( isset( $_POST['protect'] ) ) {
			$parts							=	explode( '_', cbGetParam( $_POST, 'protect', '' ) );

			if ( ( count( $parts ) == 3 ) && ( $parts[0] == 'cbmv1' ) && ( strlen( $parts[2] ) == 16 ) && ( $parts[1] == md5( $parts[2] . $rowTo->id . $rowTo->password . $rowTo->lastvisitDate . $rowFrom->password . $rowFrom->lastvisitDate ) ) ) {
				$errorMsg					=	null;

				$_PLUGINS->trigger( 'onBeforeEmailUser', array( &$rowFrom, &$rowTo, 1, &$emailName, &$emailAddress, &$subject, &$message ) );	//$ui=1

				if ( $_PLUGINS->is_errors() ) {
					$errorMsg				=	$_PLUGINS->getErrorMSG( '<br />' );
				} else {
					$spamCheck				=	cbSpamProtect( $_CB_framework->myId(), true, $allowPublic );

					if ( $spamCheck ) {
						$errorMsg			=	$spamCheck;
					} else {
						$cbNotification		=	new cbNotification();

						if ( $_CB_framework->myId() ) {
							$res			=	$cbNotification->sendUserEmail( $toId, $fromId, $subject, $message, true );
						} else {
							$res			=	$cbNotification->sendUserEmailFromEmail( $toId, $emailName, $emailAddress, $subject, $message, true );
						}

						if ( $res ) {
							cbRedirectToProfile( $rowTo->id, CBTxt::Th( 'UE_SENTEMAILSUCCESS', 'Your email was sent successfully!' ) );
							return;
						}
						else {
							$errorMsg		=	CBTxt::Th( 'UE_SENTEMAILFAILED', 'Your email failed to send! Please try again.' );
						}
					}
				}
			}
		}
	}

	if ( $errorMsg ) {
		$_CB_framework->enqueueMessage( $errorMsg, 'error' );
	}

	HTML_comprofiler::emailUser( $option, $rowFrom, $rowTo, $allowPublic, $emailName, $emailAddress, $subject, $message );
}

function emailUser( $option, $uid ) {
	global $_CB_framework, $_PLUGINS, $ueConfig;

	$allowPublic	=	( isset( $ueConfig['allow_email_public'] ) ? (int) $ueConfig['allow_email_public'] : 0 );

	if ( ( ( $_CB_framework->myId() == 0 ) && ( ! $allowPublic ) ) || ( ( $ueConfig['allow_email_display'] != 1 ) && ( $ueConfig['allow_email_display'] != 3 ) ) || ( ! CBuser::getMyInstance()->authoriseView( 'profile', $uid ) ) ) {
		cbNotAuth( true );
		return;
	}

	$spamCheck		=	cbSpamProtect( (int) $_CB_framework->myId(), false, $allowPublic );

	if ( $spamCheck ) {
		$_CB_framework->enqueueMessage( $spamCheck, 'error' );
		return;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$rowFrom		=	new UserTable();

	$rowFrom->load( (int) $_CB_framework->myId() );

	$rowTo			=	new UserTable();

	$rowTo->load( (int) $uid );

	HTML_comprofiler::emailUser( $option, $rowFrom, $rowTo, $allowPublic );
}

function userEdit( $option, $uid, $submitvalue, $regErrorMSG = null ) {
	global $_CB_framework, $_POST, $_PLUGINS;

	if ( $uid == 0 ) {
		$uid		=	$_CB_framework->myId();
	}

	$msg			=	cbCheckIfUserCanPerformUserTask( $uid, 'allowModeratorsUserEdit' );

	if ( ( $uid != $_CB_framework->myId() ) && ( $msg === null ) ) {
		// safeguard against missconfiguration of the above: also avoids lower-level users editing higher level ones:
		$msg		=	checkCBpermissions( array( (int) $uid ), 'edit', true );
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( $uid, &$msg, 1 ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbUser			=&	CBuser::getInstance( $uid );

	if ( $cbUser !== null ) {
		$user		=&	$cbUser->getUserData();

		HTML_comprofiler::userEdit( $user, $option, $submitvalue, $regErrorMSG );
	} else {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_ERROR_USER_NOT_SYNCHRONIZED', 'User not existing or not synchronized with CB' ), 'error' );
	}
}

function userSave( $option, $uid ) {
	global $_CB_framework, $_POST, $_PLUGINS;

	// simple spoof check security
	cbSpoofCheck( 'userEdit' );

	// check rights to access:
	if ( $uid == null ) {
		$msg							=	CBTxt::Th( 'UE_USER_PROFILE_NOT', 'Your profile could not be updated.' );
	} else {
		$msg							=	cbCheckIfUserCanPerformUserTask( $uid, 'allowModeratorsUserEdit' );
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeUserProfileSaveRequest', array( $uid, &$msg, 1 ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	// Get current user state:
	$userComplete						=	new UserTable();

	if ( ! $userComplete->load( (int) $uid ) ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USER_PROFILE_NOT', 'Your profile could not be updated.' ), 'error' );
		return;
	}

	// Update lastupdatedate of profile by user:
	if ( $_CB_framework->myId() == $uid ) {
		$userComplete->lastupdatedate	=	$_CB_framework->dateDbOfNow();
	}

	// Store new user state:
	$saveResult							=	$userComplete->saveSafely( $_POST, $_CB_framework->getUi(), 'edit' );

	if ( ! $saveResult ) {
		$regErrorMSG					=	$userComplete->getError();

		$_PLUGINS->trigger( 'onAfterUserProfileSaveFailed', array( &$userComplete, &$regErrorMSG, 1 ) );

		HTML_comprofiler::userEdit( $userComplete, $option, CBTxt::T( 'UE_UPDATE', 'Update' ), $regErrorMSG );
		return;
	}

	$_PLUGINS->trigger( 'onAfterUserProfileSaved', array( &$userComplete, 1 ) );

	cbRedirectToProfile( $uid, CBTxt::Th( 'USER_DETAILS_SAVE', 'Your settings have been saved.' ) );
}

function &loadComprofilerUser( $uid ) {
	global $_CB_framework, $_REQUEST;

	if ( ! isset( $_REQUEST['user'] ) ) {
		if ( ! $uid ) {
			$null		=	null;

			return $null;
		}
	} else {
		$userReq		=	urldecode( stripslashes( cbGetParam( $_REQUEST, 'user' ) ) );
		$len			=	strlen( $userReq );

		if ( ( $len > 2 ) && ( $userReq[0] == "'" ) && ( $userReq[$len-1] == "'" ) ) {
			$userReq	=	substr( $userReq, 1, ( $len - 2 ) );
			$uid		=	$_CB_framework->getUserIdFrom( 'username', $userReq );
		} else {
			$uid		=	(int) $userReq;
		}
	}

	if ( $uid ) {
		$cbUser			=&	CBuser::getInstance( $uid );

		if ( $cbUser ) {
			$user		=&	$cbUser->getUserData();

			return $user;
		}
	}

	$null				=	null;

	return $null;
}

function userProfile( $option, $uid, $submitvalue) {
	global $_REQUEST, $ueConfig, $_CB_framework, $_PLUGINS;

	$msg					=	null;

	if ( isset( $_REQUEST['user'] ) ) {
		if ( ! CBuser::getMyInstance()->authoriseView( 'profile', $uid ) ) {
			$canRegister	=	( ( ! isset( $ueConfig['reg_admin_allowcbregistration'] ) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' );

			// Can the guest access once registered with default User Group ? (and doing the check only if he even can register to avoid unneeded checks)
			$canAccess		=	$canRegister && Application::CmsPermissions()->checkGroupsForViewAccessLevel(
				$_CB_framework->getCfg( 'new_usertype' ),
				Application::Config()->get( 'profile_viewaccesslevel', 3 )
			);

			if ( ( $_CB_framework->myId() < 1 ) && ( ! ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' ) && $canRegister ) ) ) && $canAccess ) {
				$msg		=	CBTxt::Th( 'UE_REGISTERFORPROFILEVIEW', 'Please log in or sign up to view user profiles.' );
			} else {
				$msg		=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
			}
		}
	} else {
		if ( $uid == 0 ) {
			$msg			=	CBTxt::Th( 'UE_REGISTERFORPROFILE', 'Please log in or sign up to view or modify your profile.' );
		}
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeUserProfileAccess', array( $uid, &$msg, 1 ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$user					=&	loadComprofilerUser( $uid );

	if ( $user === null ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOSUCHPROFILE', 'This profile does not exist or is no longer available' ), 'error' );
		return;
	}

	if ( cbGetParam( $_GET, 'reason' ) == 'canceledit' ) {
		if ( $uid == 0 ) {
			$Euid			=	$_CB_framework->myId();
		} else {
			$Euid			=	$uid;
		}

		$msg				=	cbCheckIfUserCanPerformUserTask( $Euid, 'allowModeratorsUserEdit');

		if ( ( $Euid != $_CB_framework->myId() ) && ( $msg === null ) ) {
			// safeguard against missconfiguration of the above: also avoids lower-level users editing higher level ones:
			$msg			=	checkCBpermissions( array( (int) $Euid ), 'edit', true );
		}

		$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( $Euid, &$msg, 1 ) );

		if ( $msg ) {
			$_CB_framework->enqueueMessage( $msg, 'error' );
			return;
		}

		$_PLUGINS->trigger( 'onAfterUserProfileEditCancel', array( &$user ) );

		if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"".$_PLUGINS->getErrorMSG()."\"); window.history.go(-1); </script>\n";
			exit();
		}
	}

	HTML_comprofiler::userProfile( $user, $option, $submitvalue );
}

// NB for now duplicated in frontend and admin backend:
function tabClass( /** @noinspection PhpUnusedParameterInspection */ $option, $task, $uid ) {
	global $_CB_framework, $_PLUGINS, $_REQUEST, $_POST;

	$user					=&	loadComprofilerUser( $uid );
	$cbUser					=&	CBuser::getInstance( ( $user === null ? null : $user->id ) );

	$unsecureChars			=	array( '/', '\\', ':', ';', '{', '}', '(', ')', "\"", "'", '.', ',', "\0", ' ', "\t", "\n", "\r", "\x0B" );
	$appendClass			=	false;

	if ( $task == 'fieldclass' ) {
		$reason				=	cbGetParam( $_REQUEST, 'reason' );

		if ( $user && $user->id ) {
			$_PLUGINS->loadPluginGroup( 'user' );

			if ( $reason === 'edit' ) {
				$msg		=	cbCheckIfUserCanPerformUserTask( $user->id, 'allowModeratorsUserEdit' );

				if ( ( $uid != $_CB_framework->myId() ) && ( $msg === null ) ) {
					// safeguard against missconfiguration of the above: also avoids lower-level users editing higher level ones:
					$msg	=	checkCBpermissions( array( (int) $user->id ), 'edit', true );
				}

				$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( $user->id, &$msg, 1 ) );
			} elseif ( ( $reason === 'profile' ) || ( $reason === 'list' ) ) {
				if ( CBuser::getMyInstance()->authoriseView( 'profile', $user->id ) ) {
					$msg	=	null;
				} else {
					$msg	=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
				}

				$_PLUGINS->trigger( 'onBeforeUserProfileAccess', array( $user->id, &$msg, 1 ) );
			} else {
				$msg		=	CBTxt::Th( 'UE_NO_INDICATION', 'No indication' );
			}

			if ( $msg ) {
				echo $msg; return;
			}
		} elseif ( $reason == 'register' ) {
			if ( $_CB_framework->myId() != 0 ) {
				echo CBTxt::Th( 'UE_ALREADY_LOGGED_IN', 'You are already logged in' );
				return;
			}
		} else {
			$msg			=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );

			echo $msg; return;
		}

		$fieldName			=	trim( substr( str_replace( $unsecureChars, '', urldecode( stripslashes( cbGetParam( $_REQUEST, "field" ) ) ) ), 0, 50 ) );

		if ( ! $fieldName ) {
			echo 'no field'; return;
		}

		$pluginName			=	null;
		$tabClassName		=	null;
		$method				=	null;
	}
	elseif ( $task == 'tabclass' ) {
		$tabClassName		=	urldecode( stripslashes( cbGetParam( $_REQUEST, "tab" ) ) );

		if ( ! $tabClassName ) {
			return;
		}

		$pluginName			=	null;
		$tabClassName		=	substr( str_replace( $unsecureChars, '', $tabClassName ), 0, 32 );
		$method				=	'getTabComponent';

		$fieldName			=	null;
		$reason				=	null;
	}
	elseif ( $task == 'pluginclass' ) {
		$pluginName			=	urldecode( stripslashes( cbGetParam( $_REQUEST, "plugin" ) ) );

		if ( ! $pluginName ) {
			return;
		}

		$tabClassName		=	'CBplug_' . strtolower( substr( str_replace( $unsecureChars, '', $pluginName ), 0, 32 ) );
		$method				=	'getCBpluginComponent';
		$appendClass		=	( ( cbGetParam( $_REQUEST, 'format' ) != 'raw' ) && ( cbGetParam( $_REQUEST, 'format' ) != 'rawraw' ) ? true : false );

		$fieldName			=	null;
		$reason				=	null;
	}
	else {
		throw new LogicException( 'Unexpected task for CB tabClass' );
	}

	$tabs					=	$cbUser->_getCbTabs( false );

	if ( $task == 'fieldclass' ) {
		ob_start();
		$results			=	$tabs->fieldCall( $fieldName, $user, $_POST, $reason );
		$result				=	ob_get_contents() . $results;
		ob_end_clean();
	} else {
		ob_start();
		$results			=	$tabs->tabClassPluginTabs( $user, $_POST, $pluginName, $tabClassName, $method );
		$result				=	ob_get_contents() . $results;
		ob_end_clean();
	}

	if ( $result === false ) {
	 	if ( $_PLUGINS->is_errors() ) {
			echo "<script type=\"text/javascript\">alert(\"" . $_PLUGINS->getErrorMSG() . "\"); </script>\n";
	 	}
	} elseif ( $result !== null ) {
		if ( $appendClass ) {
			$pageClass		=	$_CB_framework->getMenuPageClass();

			echo '<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . ( $pageClass ? ' ' . htmlspecialchars( $pageClass ) : null ) . '">' . $result . '</div>';

			$_CB_framework->setMenuMeta();
		} else {
			echo $result;
		}
	}
}

function usersList( $uid ) {
	global $_POST, $_REQUEST;

	cbimport( 'cb.lists' );

	if ( isset( $_POST['listid'] ) ) {
		$listid				=	(int) cbGetParam( $_POST, 'listid', 0 );
	} else {
		$listid				=	(int) cbGetParam( $_GET, 'listid', 0 );
	}

	$searchFormValuesRAW	=	$_GET;

	$cbList					=	new cbUsersList();

	$cbList->drawUsersList( $uid, $listid, $searchFormValuesRAW );
}

function lostPassForm( $option ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	$loginType	=	( isset( $ueConfig['login_type'] ) ? (int) $ueConfig['login_type'] : 0 );

	if ( $loginType == 4 ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ), 'error' );
		return;
	}

	$_CB_framework->setPageTitle( CBTxt::Th( 'PROMPT_PASSWORD', 'Lost your Password?' ) );

	$_PLUGINS->loadPluginGroup( 'user' );

	HTML_comprofiler::lostPassForm( $option );
}

function sendNewPass( /** @noinspection PhpUnusedParameterInspection */ $option ) {
	global $_CB_framework, $ueConfig, $_PLUGINS, $_POST;

	$loginType					=	( isset( $ueConfig['login_type'] ) ? (int) $ueConfig['login_type'] : 0 );

	if ( $loginType == 4 ) {
		cbRedirect( $_CB_framework->viewUrl( 'done', false ), CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ), 'error' );
		return;
	}

	// simple spoof check security
	checkCBPostIsHTTPS();
	cbSpoofCheck( 'lostPassForm' );
	cbRegAntiSpamCheck();

	$liveSite					=	$_CB_framework->getCfg( 'live_site' );
	$usernameExists				=	( $loginType != 2 );

	// ensure no malicous sql gets past
	$checkusername				=	trim( cbGetParam( $_POST, 'checkusername', '' ) );
	$confirmEmail				=	trim( cbGetParam( $_POST, 'checkemail', '' ) );

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onStartNewPassword', array( &$checkusername, &$confirmEmail ) );

	if ( $_PLUGINS->is_errors() ) {
		cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), $_PLUGINS->getErrorMSG(), 'error' );
		return;
	}

	$checkusername				=	stripslashes( $checkusername );
	$confirmEmail				=	stripslashes( $confirmEmail );
	$res						=	false;
	$error						=	null;

	if ( $usernameExists && ( $confirmEmail != '' ) && ( ! $checkusername ) ) {
		$user					=	new UserTable();

		if ( ! $user->loadByEmail( $confirmEmail ) ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), CBTxt::Th( 'UE_EMAIL_DOES_NOT_EXISTS_ON_SITE', "The email '[email]' does not exist on this site.", array( '[email]' =>  htmlspecialchars( $confirmEmail ) ) ), 'error' );
		}

		$message				=	str_replace( '\n', "\n", sprintf( CBTxt::T( 'UE_USERNAMEREMINDER_MSG', 'Hello,\nA username reminder has been requested for your %s account.\n\nYour username is: %s\n\nTo log in to your account, click on the link below:\n%s\n\nThank you.\n' ), $_CB_framework->getCfg( 'sitename' ), $user->username, $liveSite ) );
			/*
			'Hello,\n'
			.'A username reminder has been requested for your %s account.\n\n'
			.'Your username is: %s\n\n'
			.'To log in to your account, click on the link below:\n'
			.'%s\n\n'
			.'Thank you.\n'
			*/
		$subject				=	sprintf( CBTxt::T( 'UE_USERNAMEREMINDER_SUB', 'Username reminder for %s' ), $user->username );

		$_PLUGINS->trigger( 'onBeforeUsernameReminder', array( $user, &$subject, &$message ) );

		if ( $_PLUGINS->is_errors() ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), $_PLUGINS->getErrorMSG(), 'error' );
			return;
		}

		$cbNotification			=	new cbNotification();
		$res					=	$cbNotification->sendFromSystem( $user->id, $subject, $message, true, ( isset( $ueConfig['reg_email_html'] ) ? (int) $ueConfig['reg_email_html']  : 0 ) );
		$error					=	$cbNotification->errorMSG;

		$_PLUGINS->trigger( 'onAfterUsernameReminder', array( $user, &$res ) );

		if ( $res ) {
			cbRedirect( $_CB_framework->viewUrl( 'done', false ), sprintf( CBTxt::Th( 'UE_USERNAME_REMINDER_SENT', 'Username reminder sent to your email address %s. Please check your email (and if needed your spambox too)!' ), htmlspecialchars( $confirmEmail ) ) );
		} else {
			cbRedirect( $_CB_framework->viewUrl( 'done', false ), ( $error ? CBTxt::Th( 'SENDING_EMAIL_FAILED_ERROR_ERROR', 'Sending Email Failed! Error: [error]', array( '[error]' => $error ) ) : CBTxt::Th( 'UE_EMAIL_SENDING_ERROR', 'Error sending email' ) ), 'error' );
		}
	} elseif ( $confirmEmail != '' ) {
		$user					=	new UserTable();

		if ( $usernameExists ) {
			$foundUser			=	$user->loadByUsername( $checkusername );

			if ( $foundUser && ( cbutf8_strtolower( $user->email ) != cbutf8_strtolower( $confirmEmail ) ) ) {
				$foundUser		=	false;
			}
		} else {
			$foundUser			=	$user->loadByEmail( $confirmEmail );
		}

		if ( ! $foundUser ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), CBTxt::Th( 'ERROR_PASS', 'Sorry, no corresponding user was found' ), 'error' );
		}

		$resetTime					=	(int) $_CB_framework->getCfg( 'reset_time' );
		$resetCount					=	(int) $_CB_framework->getCfg( 'reset_count' );
		$hoursSinceLastReset		=	( ( $_CB_framework->getUTCNow() - (int) $_CB_framework->getUTCTimestamp( $user->lastResetTime ) ) / 3600 );

		if ( $hoursSinceLastReset > $resetTime ) {
			$user->lastResetTime	=	$_CB_framework->getUTCDate();
			$user->resetCount		=	1;
		} else {
			$user->resetCount		=	( $user->resetCount + 1 );
		}

		if ( $resetCount && ( $user->resetCount > $resetCount ) ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), CBTxt::Th( 'EXCEEDED_MAXIMUM_PASSWORD_RESETS', 'You have exceeded the maximum number of password resets allowed. Please try again in %%COUNT%% hours.|You have exceeded the maximum number of password resets allowed. Please try again in 1 hour.', array( '%%COUNT%%' => $resetTime ) ), 'error' );
		}

		$newpass				=	$user->getRandomPassword();
		$message				=	str_replace( '\n', "\n", sprintf( CBTxt::T( 'UE_NEWPASS_MSG', 'The user account %s has this email associated with it.\nA web user from %s has just requested that a new password be sent.\n\nYour New Password is: %s\n\nIf you didn\'t ask for this, don\'t worry. You are seeing this message, not them. If this was an error just log in with your new password and then change your password to what you would like it to be.' ), $user->username, $liveSite, $newpass ) );
			/*
			'The user account %s has this email associated with it.\n'
			.'A web user from %s has just requested that a new password be sent.\n\n'
			.'Your New Password is: %s\n\n'
			.'If you didn\'t ask for this, don\'t worry. You are seeing this message, not them. If this was an error just log in with your new password and then change your password to what you would like it to be.'
			*/
		$subject				=	sprintf( CBTxt::T( 'UE_NEWPASS_SUB', 'New password for: %s' ), $user->username );

		$_PLUGINS->trigger( 'onBeforeNewPassword', array( $user, &$newpass, &$subject, &$message ) );

		if ( $_PLUGINS->is_errors() ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), $_PLUGINS->getErrorMSG(), 'error' );
		}

		$_PLUGINS->trigger( 'onNewPassword', array( $user, $newpass ) );

		$storeValues			=	array(	'password' => $newpass,
											'lastResetTime' => $user->lastResetTime,
											'resetCount' => $user->resetCount
										);

		if ( ! $user->storeDatabaseValues( $storeValues ) ) {
			cbRedirect( $_CB_framework->viewUrl( 'lostpassword', false ), $user->getError(), 'error' );
		} else {
			$cbNotification 	=	new cbNotification();
			$res				=	$cbNotification->sendFromSystem( $user->id, $subject, $message, true, ( isset( $ueConfig['reg_email_html'] ) ? (int) $ueConfig['reg_email_html']  : 0 ) );
			$error				=	$cbNotification->errorMSG;
		}

		$_PLUGINS->trigger( 'onAfterPasswordReminder', array( $user, $newpass, &$res ) );

		if ( $res ) {
			cbRedirect( $_CB_framework->viewUrl( 'done', false ), sprintf( CBTxt::Th( 'UE_NEWPASS_SENT', 'New User Password created and sent to your email address %s. Please check your email (and if needed your spambox too)!' ), htmlspecialchars( $confirmEmail ) ) );
		} else {
			cbRedirect( $_CB_framework->viewUrl( 'done', false ), ( $error ? CBTxt::Th( 'PASSWORD_RESET_FAILED_ERROR_ERROR', 'Password Reset Failed! Error: [error]', array( '[error]' => $error ) ) : CBTxt::Th( 'UE_NEWPASS_FAILED', 'Password Reset Failed!' ) ), 'error' );
		}
	} else {
		cbRedirect( $_CB_framework->viewUrl( 'done', false ), CBTxt::Th( 'UE_NEWPASS_FAILED', 'Password Reset Failed!' ), 'error' );
	}
}

function registerForm( $option, $emailpass, $regErrorMSG = null ) {
	global $_CB_framework, $ueConfig, $_PLUGINS, $_POST;

	$msg				=	null;

	if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' ) && ( ( ! isset( $ueConfig['reg_admin_allowcbregistration'] ) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) ) ) {
		$msg			=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} elseif ( $_CB_framework->myId() ) {
		$msg			=	CBTxt::Th( 'UE_ALREADY_LOGGED_IN', 'You are already logged in' );
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeRegisterFormRequest', array( &$msg, $emailpass, &$regErrorMSG ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$fieldsQuery		=	null;

	$results			=	$_PLUGINS->trigger( 'onBeforeRegisterForm', array( $option, $emailpass, &$regErrorMSG, $fieldsQuery ) );

	if ( $_PLUGINS->is_errors() ) {
		$_CB_framework->enqueueMessage( $_PLUGINS->getErrorMSG( '<br />' ), 'error' );
		return;
	}

	if ( implode( '', $results ) != "" ) {
		$return			=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
						.			'<div>' . implode( '</div><div>', $results ) . '</div>'
						.		'</div>';

		echo $return;
		return;
	}

	$userComplete		=	new UserTable();

	if ( $regErrorMSG !== null ) {
		HTML_comprofiler::registerForm( $option, $emailpass, $userComplete, $_POST, $regErrorMSG );
	} else {
		$null			=	null;

		HTML_comprofiler::registerForm( $option, $emailpass, $userComplete, $null, $regErrorMSG );
	}
}

function saveRegistration( $option ) {
	global $_CB_framework, $ueConfig, $_POST, $_PLUGINS;

	// simple spoof check security
	checkCBPostIsHTTPS();
	cbSpoofCheck( 'registerForm' );
	cbRegAntiSpamCheck();

	// Check rights to access:
	if ( ( ( $_CB_framework->getCfg( 'allowUserRegistration' ) == '0' ) && ( ( ! isset($ueConfig['reg_admin_allowcbregistration']) ) || $ueConfig['reg_admin_allowcbregistration'] != '1' ) ) || $_CB_framework->myId() ) {
		$msg							=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} else {
		$msg							=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeSaveUserRegistrationRequest', array( &$msg ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	if ( ! isset( $ueConfig['emailpass'] ) ) {
		$ueConfig['emailpass']			=	'0';
	}

	$userComplete						=	new UserTable();

	// Pre-registration trigger:
	$_PLUGINS->trigger( 'onStartSaveUserRegistration', array() );

	if ( $_PLUGINS->is_errors() ) {
		$oldUserComplete				=	new UserTable();

		$userComplete->bindSafely( $_POST, $_CB_framework->getUi(), 'register', $oldUserComplete );

		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $_PLUGINS->getErrorMSG( '<br />' ) );
		return;
	}

	// Check if this user already registered with exactly this username and password:
	$username							=	cbGetParam( $_POST, 'username', '' );
	$usernameExists						=	$userComplete->loadByUsername( $username );

	if ( $usernameExists ) {
		$password						=	cbGetParam( $_POST, 'password', '', _CB_ALLOWRAW );

		if ( $userComplete->verifyPassword( $password ) ) {
			$pwd_md5					=	$userComplete->password;
			$userComplete->password		=	$password;
			$messagesToUser				=	activateUser( $userComplete, 1, 'SameUserRegistrationAgain' );
			$userComplete->password		=	$pwd_md5;

			$return						=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
										.			'<div>' . implode( '</div><div>', $messagesToUser ) . '</div>'
										.		'</div>';

			echo $return;
			return;
		} else {
			$oldUserComplete			=	new UserTable();

			$userComplete->bindSafely( $_POST, $_CB_framework->getUi(), 'register', $oldUserComplete );

			HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, CBTxt::Th( 'UE_USERNAME_NOT_AVAILABLE', "The username '[username]' is already in use.", array( '[username]' =>  htmlspecialchars( $username ) ) ) );
			return;
		}
	}

	// Set id to 0 for autoincrement and store IP address used for registration:
	$userComplete->id			 		=	0;
	$userComplete->registeripaddr		=	cbGetIPlist();

	// Store new user state:
	$saveResult							=	$userComplete->saveSafely( $_POST, $_CB_framework->getUi(), 'register' );

	if ( $saveResult === false ) {
		$regErrorMSG					=	$userComplete->getError();

		$_PLUGINS->trigger( 'onAfterUserRegistrationSaveFailed', array( &$userComplete, &$regErrorMSG, 1 ) );

		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $regErrorMSG );
		return;
	}

	if ( $saveResult['ok'] === true ) {
		$messagesToUser					=	activateUser( $userComplete, 1, 'UserRegistration' );
	} else {
		$messagesToUser					=	array();
	}

	foreach ( $saveResult['tabs'] as $res ) {
		if ( $res ) {
			$messagesToUser[]			=	$res;
		}
	}

	if ( $saveResult['ok'] === false ) {
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $userComplete->getError() );
		return;
	}

	$_PLUGINS->trigger( 'onAfterUserRegistrationMailsSent', array( &$userComplete, &$userComplete, &$messagesToUser, $ueConfig['reg_confirmation'], $ueConfig['reg_admin_approval'], true));

	foreach ( $saveResult['after'] as $res ) {
		if ( $res ) {
			$messagesToUser[]			=	$res;
		}
	}

	if ( $_PLUGINS->is_errors() ) {
		HTML_comprofiler::registerForm( $option, $ueConfig['emailpass'], $userComplete, $_POST, $_PLUGINS->getErrorMSG() );
		return;
	}

	$_PLUGINS->trigger( 'onAfterSaveUserRegistration', array( &$userComplete, &$messagesToUser, 1 ) );

	$return								=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
										.			'<div>' . implode( '</div><div>', $messagesToUser ) . '</div>'
										.		'</div>';

	echo $return;
}


/**
 * Ajax function: Checks the availability of a username for registration and echoes a text containing the result of username search.
 *
 * @deprecated 2.0.0 use cbValidator::getRuleHtmlAttributes instead
 *
 * @param  string  $username
 * @param  string  $function
 */
function performCheckUsername( $username, $function ) {
	global $_CB_database, $ueConfig;

	if ( ( ! isset( $ueConfig['reg_username_checker'] ) ) || ( ! $ueConfig['reg_username_checker'] ) ) {
		echo CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
		exit();
	}
	// simple spoof check security
	cbSpoofCheck( 'registerForm' );
	cbRegAntiSpamCheck();

	$username	=	stripslashes( $username );
	$usernameISO =	$username;			// ajax sends in utf8, but no need to change encoding anymore.

	if ( $_CB_database->isDbCollationCaseInsensitive() ) {
		$query	=	"SELECT COUNT(*) AS result FROM #__users WHERE username = " . $_CB_database->Quote( ( trim( $usernameISO ) ) );
	} else {
		$query	=	"SELECT COUNT(*) AS result FROM #__users WHERE LOWER(username) = " . $_CB_database->Quote( ( strtolower( trim( $usernameISO ) ) ) );
	}
	$_CB_database->setQuery($query);
	$dataObj	=	null;
	if ( $_CB_database->loadObject( $dataObj ) ) {
		/** @var StdClass $dataObj */
		if ( $dataObj->result ) {
			// funily, the output does not need to be UTF8 again:
			if ( $function == 'testexists' ) {
				echo ( '<div class="alert alert-success">' . CBTxt::Th( 'UE_USERNAME_EXISTS_ON_SITE', "The username '[username]' exists on this site.", array( '[username]' =>  htmlspecialchars( $username ) ) ) . '</div>' );
			} else {
				echo ( '<div class="alert alert-danger">' . CBTxt::Th( 'UE_USERNAME_NOT_AVAILABLE', "The username '[username]' is already in use.", array( '[username]' =>  htmlspecialchars( $username ) ) ) . '</div>' );
			}
		} else {
			if ( $function == 'testexists' ) {
				echo ( '<div class="alert alert-danger">' . CBTxt::Th( 'UE_USERNAME_DOESNT_EXISTS', "The username '[username]' does not exist on this site.", array( '[username]' =>  htmlspecialchars( $username ) ) ) . '</div>' );
			} else {
				echo ( '<div class="alert alert-success">' . CBTxt::Th( 'UE_USERNAME_AVAILABLE', "The username '[username]' is available.", array( '[username]' =>  htmlspecialchars( $username ) ) ) . '</div>' );
			}
		}
	} else {
		echo ( '<div class="alert alert-danger">' . CBTxt::Th( 'UE_SEARCH_ERROR', 'Search error' ) . ' !' . '</div>' );
	}
}

/**
 * Ajax function: Checks the availability of a username for registration and echoes a text containing the result of username search.
 *
 * @deprecated 2.0.0 use cbValidator::getRuleHtmlAttributes instead
 *
 * @param  string  $email
 * @param  string  $function
 */
function performCheckEmail( $email, $function ) {
	global $_CB_framework, $_CB_database, $ueConfig;

	$field				=	new \CB\Database\Table\FieldTable();

	$field->load( array( 'name' => 'email' ) );

	$field->params		=	new \CBLib\Registry\Registry( $field->params );

	if ( ! $field->params->get( 'field_check_email', 0 ) ) {
		echo CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
		exit();
	}
	// simple spoof check security
	if ( ( ! cbSpoofCheck( 'registerForm', 'POST', 2 ) ) || ( ! cbRegAntiSpamCheck( 2 ) ) ) {
		echo '<div class="alert alert-danger">' . CBTxt::Th( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' ) . "</div>";
		exit;
	}

	$email		=	stripslashes( $email );
	$emailISO 	=	$email;				// ajax sends in utf8, but no need to change encoding anymore.

	if ( $field->params->get( 'field_check_email', 0 ) > 1 ) {
		if ( $_CB_database->isDbCollationCaseInsensitive() ) {
			$query	=	"SELECT COUNT(*) AS result FROM #__users WHERE email = " . $_CB_database->Quote( ( trim( $emailISO ) ) );
		} else {
			$query	=	"SELECT COUNT(*) AS result FROM #__users WHERE LOWER(email) = " . $_CB_database->Quote( ( strtolower( trim( $emailISO ) ) ) );
		}
		$_CB_database->setQuery($query);
		$dataObj	=	null;
		if ( $_CB_database->loadObject( $dataObj ) ) {
			/** @var StdClass $dataObj */
			if ( $function == 'testexists' ) {
				if ( $dataObj->result ) {
					echo '<div class="alert alert-success">' . CBTxt::Th( 'UE_EMAIL_EXISTS_ON_SITE', "The email '[email]' exists on this site.", array( '[email]' =>  htmlspecialchars( $email ) ) ) . "</div>";
					return;
				} else {
					echo '<div class="alert alert-danger">' . CBTxt::Th( 'UE_EMAIL_DOES_NOT_EXISTS_ON_SITE', "The email '[email]' does not exist on this site.", array( '[email]' =>  htmlspecialchars( $email ) ) ) . "</div>";
					return;
				}
			} else {
				if ( $dataObj->result ) {
					echo '<div class="alert alert-danger">' . CBTxt::Th( 'UE_EMAIL_NOT_AVAILABLE', "The email '[email]' is already in use.", array( '[email]' =>  htmlspecialchars( $email ) ) ) . "</div>";
					return;
				}
			}
		}
	}
	if ( $function == 'testexists' ) {
		echo CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
		return;
	} else {
		$checkResult	=	cbCheckMail( $_CB_framework->getCfg( 'mailfrom' ), $email );
	}
	switch ( $checkResult ) {
		case -2: // Wrong Format
			echo '<span class="alert alert-danger">' . sprintf( CBTxt::Th( 'UE_EMAIL_NOVALID', 'This is not a valid email address.' ) ), htmlspecialchars( $email ) . "</span>";
			break;
		case -1: // Couldn't Check
			break;
		case 0: // Invalid
			if ( $ueConfig['reg_confirmation'] == 0 ) {
				echo '<span class="alert alert-danger">' . sprintf( CBTxt::Th( 'UE_EMAIL_INCORRECT_CHECK', 'This email does not accept email: Please check.' ) ), htmlspecialchars( $email ) . "</span>";
			} else {
				echo '<span class="alert alert-danger">' . sprintf( CBTxt::Th( 'UE_EMAIL_INCORRECT_CHECK_NEEDED', 'This address does not accept email: Needed for confirmation.' ) ), htmlspecialchars( $email ) . "</span>";
			}
			break;
		case 1: // Valid
			echo '<span class="alert alert-success">' . sprintf( CBTxt::Th( 'UE_EMAIL_VERIFIED', 'This email address seems valid.' ) ), htmlspecialchars( $email ) . "</span>";
			break;
		default:
			echo '<span class="alert alert-danger">performCheckEmail:: Unexpected cbCheckMail result.</span>';
			break;
	}
}


function login( $username = null, $password = null, $secretKey = null ) {
	global $_POST, $_CB_framework, $_PLUGINS, $ueConfig;

	checkCBPostIsHTTPS();
	$_PLUGINS->loadPluginGroup( 'user' );

	if ( count( $_POST ) == 0 ) {
		HTML_comprofiler::loginForm( 'com_comprofiler', $_POST );
		return;
	}

	$loginType					=	( isset( $ueConfig['login_type'] ) ? (int) $ueConfig['login_type'] : 0 );

	if ( $loginType == 4 ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ), 'error' );
		return;
	}

	$spoofCheckOk				=	false;

	if ( cbSpoofCheck( 'login', 'POST', 2 ) ) {
		$spoofCheckOk			=	true;
	}

	if ( ! $spoofCheckOk ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' )
			. ' '
			. CBTxt::Th( 'UE_PLEASE_REFRESH', 'Please refresh/reload page before filling-in.' ), 'error' );
		return;
	}

	$messagesToUser				=	array();
	$alertmessages				=	array();

	if ( ( ! $username ) || ( ! $password ) || ( ! $secretKey ) ) {
		$username				=	trim( cbGetParam( $_POST, 'username', '' ) );
		$password				=	trim( cbGetParam( $_POST, 'passwd', '', _CB_ALLOWRAW ) );
		$secretKey				=	trim( cbGetParam( $_POST, 'secretkey', '' ) );

		if ( checkJversion() >=1 ) {
			$username			=	stripslashes( $username );
			$password			=	stripslashes( $password );
			$secretKey			=	stripslashes( $secretKey );
		}
	}

	$rememberMe					=	cbGetParam( $_POST, 'remember' );
    $return						=	trim( stripslashes( cbGetParam( $_POST, 'return', null ) ) );

	if ( cbStartOfStringMatch( $return, 'B:' ) ) {
		$return					=	base64_decode( substr( $return, 2 ) );
		$arrToClean				=	array( 'B' => get_magic_quotes_gpc() ? addslashes( $return ) : $return );
		$return					=	cbGetParam( $arrToClean, 'B', '' );
	}

	if ( ! ( ( cbStartOfStringMatch( $return, $_CB_framework->getCfg( 'live_site' ) ) || cbStartOfStringMatch( $return, 'index.php' ) ) ) ) {
		$return					=	'';
	}

	$message					=	trim( cbGetParam( $_POST, 'message', 0 ) );

	// Do the login including all authentications and event firing:
	cbimport( 'cb.authentication' );

	$cbAuthenticate				=	new CBAuthentication();
	$resultError				=	$cbAuthenticate->login( $username, $password, $rememberMe, $message, $return, $messagesToUser, $alertmessages, $loginType, $secretKey );

	if ( count( $messagesToUser ) > 0 ) {
		$_PLUGINS->trigger( 'onAfterUserLoginFailed', array( $username, $password, $rememberMe, $secretKey, &$return, &$alertmessages, &$messagesToUser, &$resultError ) );

		if ( in_array( cbGetParam( $_POST, 'loginfrom' ), array( 'loginform', 'regform', 'loginmodule' ) ) ) {
			HTML_comprofiler::loginForm( 'com_comprofiler', $_POST, $resultError, $messagesToUser, $alertmessages );
		} else {
			$_CB_framework->enqueueMessage( $resultError, 'error' );

			if ( is_array( $messagesToUser ) && $messagesToUser ) {
				$return			=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
								.			'<div>' . implode( '</div><div>', $messagesToUser ) . '</div>'
								.		'</div>';

				echo $return;
			}
		}
	} elseif ( $resultError ) {
		$_PLUGINS->trigger( 'onAfterUserLoginFailed', array( $username, $password, $rememberMe, $secretKey, &$return, &$alertmessages, &$messagesToUser, &$resultError ) );

		if ( in_array( cbGetParam( $_POST, 'loginfrom' ), array( 'loginform', 'regform', 'loginmodule' ) ) ) {
			HTML_comprofiler::loginForm( 'com_comprofiler', $_POST, $resultError, $messagesToUser, $alertmessages );
		} else {
			$_CB_framework->enqueueMessage( $resultError, 'error' );
		}
	} else {
		$_PLUGINS->trigger( 'onAfterUserLoginSuccess', array( $username, $password, $rememberMe, $secretKey, &$return, &$alertmessages, &$messagesToUser, &$resultError ) );

		cbRedirect( cbSef( $return, false ), ( count( $alertmessages ) > 0 ? stripslashes( implode( '\n', $alertmessages ) ) : '' ) );
	}
}

function logout() {
	global $_CB_framework, $_POST, $_PLUGINS;

	$return							=	trim( stripslashes( cbGetParam( $_POST, 'return', null ) ) );

	if ( cbStartOfStringMatch( $return, 'B:' ) ) {
		$return						=	base64_decode( substr( $return, 2 ) );
		$arrToClean					=	array( 'B' => get_magic_quotes_gpc() ? addslashes( $return ) : $return );
		$return						=	cbGetParam( $arrToClean, 'B', '' );
	}

	$message						=	trim( cbGetParam( $_POST, 'message', 0 ) );

	if ( $return || $message ) {
		$spoofCheckOk				=	false;

		if ( cbSpoofCheck( 'logout', 'POST', 2 ) ) {
			$spoofCheckOk			=	true;
		}

		if ( ! $spoofCheckOk ) {
			$_CB_framework->enqueueMessage(
				CBTxt::Th( 'UE_SESSION_EXPIRED', 'Session expired or cookies are not enabled in your browser. Please press "reload page" in your browser, and enable cookies in your browser.' )
				. ' '
				. CBTxt::Th( 'UE_PLEASE_REFRESH', 'Please refresh/reload page before filling-in.' ),
				'error'
			);
			return;
		}
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	// Do the logout including all authentications and event firing:
	cbimport( 'cb.authentication' );

	$cbAuthenticate					=	new CBAuthentication();
	$resultError					=	$cbAuthenticate->logout( $return );

	if ( $resultError ) {
		$resultError				=	$_PLUGINS->getErrorMSG();

		$_PLUGINS->trigger( 'onAfterUserLogoutFailed', array( &$resultError ) );

		$_CB_framework->enqueueMessage( $resultError );
		return;
	}

	$messageToUser					=	stripslashes( CBTxt::Th( 'LOGOUT_SUCCESS', 'You have successfully logged out' ) );

	$_PLUGINS->trigger( 'onAfterUserLogoutSuccess', array( &$return, &$message, &$messageToUser ) );

	cbRedirect( cbSef( ( $return ? $return : 'index.php' ), false ), ( $message ? $messageToUser : '' ) );
}

function confirm( $confirmcode ) {
	global $_CB_framework, $_PLUGINS;

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeUserConfirmationRequest', array() );

	if ( $_CB_framework->myId() < 1 ) {
		$unscrambledId						=	UserTable::getUserIdFromActivationCode( $confirmcode );

		if ( $unscrambledId ) {
			$cbUser							=	CBuser::getInstance( (int) $unscrambledId );

			if ( $cbUser ) {
				$user						=	$cbUser->getUserData();

				if ( $user && $user->id ) {
					if ( $user->confirmed == 0 ) {
						if ( $user->checkActivationCode( $confirmcode ) ) {
							// THIS is the normal case: user exists, is not yet confirmed, and confirmation code does match:
							$messagesToUser	=	null;
							$confirmed		=	$user->confirmUser( 1, $messagesToUser );
						} else {
							// confirmation code does not match:
							$messagesToUser	=	array( CBTxt::Th( 'UE_WRONG_CONFIRMATION_CODE', 'Wrong confirmation code. Please check your Email and spambox.' ) );
							$confirmed		=	false;
						}
					} else {
						// User has already confirmed: show friendly activation messages depending on his state:
						$messagesToUser		=	getActivationMessage( $user, 'UserConfirmation' );
						$confirmed			=	true;
					}

					if ( $confirmed ) {
						// THIS is the normal case: user exists, is not yet confirmed, and confirmation code does match:
						$class				=	'info';
					} else {
						$class				=	'error';
					}

					$_PLUGINS->trigger( 'onAfterUserConfirmation', array( &$user, $confirmcode, $confirmed, &$class, &$messagesToUser ) );

					$return					=	'<div class="cbUserConfirmation cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
											.		'<div class="' . htmlspecialchars( $class ) . '">'
											.			implode( '</div><div class="' . htmlspecialchars( $class ) . '">', $messagesToUser )
											.		'</div>'
											.	'</div>';

					echo $return; return;
				}
			}
		}

		// this is the error case where the URL is simply not right:
		cbNotAuth( true );
		return;
	} else {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) . ' ' . CBTxt::Th( 'UE_DO_LOGOUT', 'You need to logout.' ), 'error' );
	}
}

function approveImage() {
	global $_CB_framework, $_CB_database, $_POST, $_REQUEST, $_SERVER;

	// simple spoof check security for posts (menus do gets):
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
		cbSpoofCheck( 'moderateimages' );
	}

	$isModerator						=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	$avatars							=	array();

	if ( isset( $_POST['avatar'] ) ) {
		$avatars						=	cbGetParam( $_POST, 'avatar' );
	} else {
		$avatars[]						=	cbGetParam( $_REQUEST, 'avatars' );
	}

	if ( isset( $_POST['images'] ) ) {
		$userImages						=	cbGetParam( $_POST, 'images' );
	} else {
		$userImages						=	cbGetParam( $_REQUEST, 'images' );
	}

	if ( isset( $_POST['act'] ) ) {
		$act							=	cbGetParam( $_POST, 'act' );
	} else {
		$act							=	cbGetParam( $_REQUEST, 'flag' );
	}

	$cbNotification						=	new cbNotification();

	if ( $act == '1' ) {
		if ( $avatars ) foreach ( $avatars as $avatar ) {
			$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n SET " . $_CB_database->NameQuote( 'avatarapproved' ) . " = 1"
										.	', ' . $_CB_database->NameQuote( 'lastupdatedate' ) . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $avatar;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$cbNotification->sendFromSystem( (int) $avatar, CBTxt::T( 'UE_IMAGEAPPROVED_SUB', 'Image Approved' ), CBTxt::T( 'UE_IMAGEAPPROVED_MSG', 'Your image has been approved by a moderator.' ) );
		}

		if ( $userImages ) foreach ( $userImages as $user => $images ) {
			$imageColumns				=	array();

			foreach ( $images as $image ) {
				$imageColumns[]			=	$_CB_database->NameQuote( $image . 'approved' ) . ' = 1';
			}

			$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n SET " . implode( ', ', $imageColumns )
										.	', ' . $_CB_database->NameQuote( 'lastupdatedate' ) . ' = ' . $_CB_database->Quote( $_CB_framework->dateDbOfNow() )
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$cbNotification->sendFromSystem( (int) $user, CBTxt::T( 'UE_IMAGEAPPROVED_SUB', 'Image Approved' ), CBTxt::T( 'UE_IMAGEAPPROVED_MSG', 'Your image has been approved by a moderator.' ) );
		}
	} else {
		if ( $avatars ) foreach ( $avatars as $avatar ) {
			$query						=	'SELECT ' . $_CB_database->NameQuote( 'avatar' )
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $avatar;
			$_CB_database->setQuery( $query );
			$file						=	$_CB_database->loadResult();

			if ( ( preg_match( "/gallery\\//i", $file ) == false ) && is_file( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/' . $file ) ) {
				unlink( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/' . $file );

				if ( is_file( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/tn' . $file ) ) {
					unlink( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/tn' . $file );
				}
			}

			$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n SET " . $_CB_database->NameQuote( 'avatarapproved' ) . " = 1"
										.	', ' . $_CB_database->NameQuote( 'avatar' ) . ' = NULL'
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $avatar;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$cbNotification->sendFromSystem( (int) $avatar, CBTxt::T( 'UE_IMAGEREJECTED_SUB', 'Image Rejected' ), CBTxt::T( 'UE_IMAGEREJECTED_MSG', 'Your image has been rejected by a moderator. Please log in and submit a new image.' ) );
		}

		if ( $userImages ) foreach ( $userImages as $user => $images ) {
			$imageColumns				=	array();

			foreach ( $images as $image ) {
				$imageColumns[]			=	$_CB_database->NameQuote( $image . 'approved' ) . ' = 1'
										.	', ' . $_CB_database->NameQuote( $image ) . ' = NULL';

				$query					=	'SELECT ' . $_CB_database->NameQuote( $image )
										.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user;
				$_CB_database->setQuery( $query );
				$file					=	$_CB_database->loadResult();

				if ( ( preg_match( "/gallery\\//i", $file ) == false ) && is_file( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/' . $file ) ) {
					unlink( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/' . $file );

					if ( is_file( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/tn' . $file ) ) {
						unlink( $_CB_framework->getCfg( 'absolute_path' ) . '/images/comprofiler/tn' . $file );
					}
				}
			}

			$query						=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler' )
										.	"\n SET " . implode( ', ', $imageColumns )
										.	"\n WHERE " . $_CB_database->NameQuote( 'id' ) . " = " . (int) $user;
			$_CB_database->setQuery( $query );
			$_CB_database->query();

			$cbNotification->sendFromSystem( (int) $user, CBTxt::T( 'UE_IMAGEREJECTED_SUB', 'Image Rejected' ), CBTxt::T( 'UE_IMAGEREJECTED_MSG', 'Your image has been rejected by a moderator. Please log in and submit a new image.' ) );
		}
	}

	cbRedirect( $_CB_framework->viewUrl( 'moderateimages', false ), CBTxt::Th( 'UE_USERIMAGEMODERATED_SUCCESSFUL', 'User Image Successfully Moderated!' ));
}

function reportUser( $option, $form = 1, $uid = 0 ) {
	global $_CB_framework, $ueConfig, $_PLUGINS, $_POST;

	if ( $ueConfig['allowUserReports'] == 0 ) {
		$msg					=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! CBuser::getMyInstance()->authoriseView( 'profile', $uid ) ) {
		$msg					=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} else {
		$msg					=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeReportUserRequest', array( $uid, &$msg, $form ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$reportedByUser				=	CBuser::getUserDataInstance( $_CB_framework->myId() );
	$reportedUser				=	CBuser::getUserDataInstance( $uid );

	if ( $form == 1 ) {
		$results				=	$_PLUGINS->trigger( 'onBeforeReportUserForm', array( $uid, &$reportedByUser, &$reportedUser ) );

		if ( $_PLUGINS->is_errors() ) {
			$_CB_framework->enqueueMessage( $_PLUGINS->getErrorMSG( '<br />' ), 'error' );
			return;
		}

		if ( implode( '', $results ) != "" ) {
			$return				=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
								.			'<div>' . implode( '</div><div>', $results ) . '</div>'
								.		'</div>';

			echo $return;
			return;
		}

		HTML_comprofiler::reportUserForm( $option, $uid, $reportedByUser, $reportedUser );
	} else {
		cbSpoofCheck( 'reportuser' );

		$row					=	new UserReportTable();

		$_PLUGINS->trigger( 'onStartSaveReportUser', array( &$row, &$reportedByUser, &$reportedUser ) );

		if ( $_PLUGINS->is_errors() ) {
			cbRedirect( $_CB_framework->viewUrl( 'reportuser', false ), $_PLUGINS->getErrorMSG(), 'error' );
			return;
		}

		if ( ! $row->bind( $_POST ) ) {
			cbRedirect( $_CB_framework->viewUrl( 'reportuser', false ), $row->getError(), 'error' );
			return;
		}

		$row->reportedondate		=	htmlspecialchars( $row->reportedondate, ENT_QUOTES );		//TBD: remove this: not urgent but isn't right
		$row->reportexplaination	=	htmlspecialchars( $row->reportexplaination, ENT_QUOTES );	//TBD: remove this: not urgent but isn't right

		$row->reportedondate	=	$_CB_framework->getUTCDate();

		if ( ! $row->check() ) {
			cbRedirect( $_CB_framework->viewUrl( 'reportuser', false ), $row->getError(), 'error' );
			return;
		}

		$_PLUGINS->trigger( 'onBeforeSaveReportUser', array( &$row, &$reportedByUser, &$reportedUser ) );

		if ( ! $row->store() ) {
			cbRedirect( $_CB_framework->viewUrl( 'reportuser', false ), $row->getError(), 'error' );
			return;
		}

		if ( $ueConfig['moderatorEmail'] == 1 ) {
			$cbNotification		=	new cbNotification();

			$cbNotification->sendToModerators( CBTxt::T( 'UE_USERREPORT_SUB', 'User Report Pending Review' ), CBTxt::T( 'UE_USERREPORT_MSG', 'A user has submitted a report regarding a user that requires your review. Please log in and take the appropriate action.' ) );
		}

		$_PLUGINS->trigger( 'onAfterSaveReportUser', array( &$row, &$reportedByUser, &$reportedUser ) );

		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERREPORT_SUCCESSFUL', 'User report submitted successfully.' ) );
	}
}

function banUser( $option, $uid, $form = 1, $act = 1 ) {
	global $_CB_framework, $ueConfig, $_PLUGINS, $_POST;

	$isModerator				=	Application::MyUser()->isModeratorFor( Application::User( (int) $uid ) );

	if ( ( $_CB_framework->myId() < 1 ) || ( $uid < 1 ) )  {
		$msg					=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} elseif ( $ueConfig['allowUserBanning'] == 0 ) {
		$msg					=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} else {
		$msg					=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeBanUserRequest', array( $uid, &$msg, $form, $act ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$bannedByUser				=	CBuser::getUserDataInstance( $_CB_framework->myId() );
	$bannedUser					=	CBuser::getUserDataInstance( $uid );

	$orgBannedReason			=	$bannedUser->get( 'bannedreason' );

	if ( $form == 1 ) {
		$results				=	$_PLUGINS->trigger( 'onBeforeBanUserForm', array( $uid, &$bannedByUser, &$bannedUser ) );

		if ( $_PLUGINS->is_errors() ) {
			$_CB_framework->enqueueMessage( $_PLUGINS->getErrorMSG( '<br />' ), 'error' );
			return;
		}

		if ( implode( '', $results ) != "" ) {
			$return				=		'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
								.			'<div>' . implode( '</div><div>', $results ) . '</div>'
								.		'</div>';

			echo $return;
			return;
		}

		HTML_comprofiler::banUserForm( $option, $uid, $act, $orgBannedReason, $bannedByUser, $bannedUser );
	} else {
		$dateStr				=	cbFormatDate( 'now' );

		if ( $act == 1 ) {
			// Ban by moderator:
			if ( ( ! $isModerator ) || ( $_CB_framework->myId() != cbGetParam( $_POST, 'bannedby', 0 ) ) ) {
				cbNotAuth( true );
				return;
			}

			cbSpoofCheck( 'banUserForm' );

			$bannedReason		=	'<b>' . '[' . CBTxt::Th( 'UE_MODERATORBANRESPONSE', 'Moderator Response' ) . ', ' . htmlspecialchars( $dateStr ) . ']' . '</b>'
								.	"\n" . htmlspecialchars( stripslashes( cbGetParam( $_POST, 'bannedreason') ) )
								.	"\n" . $orgBannedReason;

			if ( ! $bannedUser->banUser( 1, $bannedByUser, $bannedReason ) ) {
				$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERBAN_FAILED', 'User profile ban failed. Error: [error]', array( '[error]' => $bannedUser->getError() ) ) );
				return;
			}

			$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERBAN_SUCCESSFUL', 'User Profile Ban Successful.' ) );
		} elseif ( $act == 0 ) {
			// Unban by moderator:
			if ( ! $isModerator ) {
				cbNotAuth( true );
				return;
			}

			$bannedReason		=	'<b>' . '[' . CBTxt::Th( 'UE_UNBANUSER', 'User Profile Unbanned' ) . ', ' . htmlspecialchars( $dateStr ) . ']' . '</b>'
								.	"\n" . $orgBannedReason;

			if ( ! $bannedUser->banUser( 0, $bannedByUser, $bannedReason ) ) {
				$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERUNBAN_FAILED', 'Users profile unban failed. Error: [error]', array( '[error]' => $bannedUser->getError() ) ) );
				return;
			}

			$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERUNBAN_SUCCESSFUL', 'Users profile unbanned successfully.' ) );
		} elseif ( $act == 2 ) {
			// Unban request from user:
			if ( $_CB_framework->myId() != $uid ) {
				cbNotAuth( true );
				return;
			}

			$bannedReason		=	'<b>' . '[' . CBTxt::Th( 'UE_USERBANRESPONSE', 'User Response' ) . ', ' . htmlspecialchars( $dateStr ) . ']' . '</b>'
								.	"\n" . htmlspecialchars( stripslashes( cbGetParam( $_POST, 'bannedreason') ) )
								.	"\n" . $orgBannedReason;

			if ( ! $bannedUser->banUser( 2, $bannedByUser, $bannedReason ) ) {
				$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERUNBANREQUEST_FAILED', 'Your unban profile request failed. Error: [error]', array( '[error]' => $bannedUser->getError() ) ) );
			}

			$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_USERUNBANREQUEST_SUCCESSFUL', 'Your unban profile request was successfully submitted.' ) );
		}
	}
}

function processReports(){
	global $_CB_framework, $_CB_database, $_POST;

	// simple spoof check security
	cbSpoofCheck( 'moderatereports' );

	$isModerator	=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	$reports		=	cbGetParam( $_POST, 'reports', array() );

	if ( $reports ) foreach ( $reports as $report ) {
		$query		=	'UPDATE ' . $_CB_database->NameQuote( '#__comprofiler_userreports' )
					.	"\n SET " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 1"
					.	"\n WHERE " . $_CB_database->NameQuote( 'reportid' ) . " = " . (int) $report;
		$_CB_database->setQuery( $query );
		$_CB_database->query();
	}

	cbRedirect( $_CB_framework->viewUrl( 'moderatereports', false ), CBTxt::Th( 'UE_USERREPORTMODERATED_SUCCESSFUL', 'User Report Successfully Moderated!' ) );
}

function moderator( ) {
  global $_CB_framework, $_CB_database;

	$isModerator				=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	// Image approval count:
	$query						=	'SELECT ' . $_CB_database->NameQuote( 'name' )
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'type' ) . " = " . $_CB_database->Quote( 'image' );
	$_CB_database->setQuery( $query );
	$imageFields				=	$_CB_database->loadResultArray();

	$imgApprovSelect			=	array();
	$imgApprovWhere				=	array();

	if ( $imageFields ) foreach ( $imageFields as $imageField ) {
		$imgApprovSelect[]		=	$_CB_database->NameQuote( $imageField . 'approved' );
		$imgApprovWhere[]		=	$_CB_database->NameQuote( $imageField . 'approved' ) . ' = 0';
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

	// User reports count:
	$query						=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_userreports' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'reportedstatus' ) . " = 0";
	$_CB_database->setQuery( $query );
	$userReportsCount			=	$_CB_database->loadResult();

	// Unban request count:
	$query						=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'banned' ) . " = 2"
								.	"\n AND " . $_CB_database->NameQuote( 'approved' ) . " = 1"
								.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
	$_CB_database->setQuery( $query );
	$unbanRequestCount			=	$_CB_database->loadResult();

	// User approval count:
	$query						=	'SELECT COUNT(*)'
								.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler' )
								.	"\n WHERE " . $_CB_database->NameQuote( 'approved' ) . " = 0"
								.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
	$_CB_database->setQuery( $query );
	$userApprovalCount			=	$_CB_database->loadResult();


	if ( ( $imageApprovalCount > 0 ) || ( $userReportsCount > 0 ) || ( $unbanRequestCount > 0 ) || ( $userApprovalCount > 0 ) ) {
		$return					=	null;

		if ( $imageApprovalCount > 0 ) {
			$return				=	'<div>' . CBTxt::Th( 'MODERATION_COUNT_IMAGE_APPROVALS', '%%COUNT%% Image Approval|%%COUNT%% Image Approvals', array( '%%COUNT%%' => $imageApprovalCount ) ) . '</div>';
		}

		if ( $userReportsCount > 0 ) {
			$return				=	'<div>' . CBTxt::Th( 'MODERATION_COUNT_PROFILE_REPORTS', '%%COUNT%% Profile Report|%%COUNT%% Profile Reports', array( '%%COUNT%%' => $userReportsCount ) ) . '</div>';
		}

		if ( $unbanRequestCount > 0 ) {
			$return				=	'<div>' . CBTxt::Th( 'MODERATION_COUNT_UNBAN_REQUESTS', '%%COUNT%% Unban Request|%%COUNT%% Unban Requests', array( '%%COUNT%%' => $unbanRequestCount ) ) . '</div>';
		}

		if ( $userApprovalCount > 0 ) {
			$return				=	'<div>' . CBTxt::Th(  'MODERATION_COUNT_USER_APPROVALS', '%%COUNT%% User Approval|%%COUNT%% User Approvals', array( '%%COUNT%%' => $userApprovalCount ) ) . '</div>';
		}

		echo $return;
	} else {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOACTIONREQUIRED', 'No Pending Actions' ) );
	}
}

function approveUser( $uids ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	// simple spoof check security
	cbSpoofCheck( 'pendingapprovaluser' );

	if ( $ueConfig['allowModUserApproval'] == 0 ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' ), 'error' );
		return;
	}

	$isModerator				=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	if ( ! isset( $ueConfig['emailpass'] ) ) {
		$ueConfig['emailpass']	=	'0';
	}

	if ( $uids ) foreach ( $uids as $uid ) {
		$user					=	CBuser::getUserDataInstance( (int) $uid );

		if ( ! $user->approveUser( 1 ) ) {
			cbRedirect( $_CB_framework->viewUrl( 'pendingapprovaluser', false ), $user->getError(), 'error' );
			return;
		}
	}

	cbRedirect( $_CB_framework->viewUrl( 'pendingapprovaluser', false ), ( ( count( $uids ) ) ? count( $uids ) . ' ' . CBTxt::Th( 'UE_USERAPPROVAL_SUCCESSFUL', 'User(s) was successfully approved!' ) : null ) );
}

function rejectUser( /** @noinspection PhpUnusedParameterInspection */ $option, $uids ) {
	global $_CB_framework, $ueConfig, $_POST, $_PLUGINS;

	// simple spoof check security
	cbSpoofCheck( 'pendingapprovaluser' );

	if ( $ueConfig['allowModUserApproval'] == 0 ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' ), 'error' );
		return;
	}

	$isModerator		=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	if ( $uids ) foreach( $uids as $uid ) {
		$user			=	CBuser::getUserDataInstance( (int) $uid );

		if ( $user->approved != 2 ) {
			$reason		=	stripslashes( cbGetParam( $_POST, 'comment' . (int) $uid, '' ) );

			if ( ! $user->approveUser( 2, $reason ) ) {
				cbRedirect( $_CB_framework->viewUrl( 'pendingapprovaluser', false ), $user->getError(), 'error' );
				return;
			}
		}
	}

	cbRedirect( $_CB_framework->viewUrl( 'pendingapprovaluser', false ), ( ( count( $uids ) ) ? count( $uids ) . ' ' . CBTxt::Th( 'UE_USERREJECT_SUCCESSFUL', 'The user(s) have been successfully rejected!' ) : null ) );
}

function pendingApprovalUsers( $option ) {
	global $_CB_framework, $_CB_database, $_PLUGINS, $ueConfig;

	if ( $ueConfig['allowModUserApproval'] == 0 ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' ), 'error' );
		return;
	}

	$isModerator	=	Application::MyUser()->isGlobalModerator();

	if ( ! $isModerator ) {
		cbNotAuth( true );
		return;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$query			=	'SELECT *'
					.	"\n FROM " . $_CB_database->NameQuote( '#__users' ) . " AS u"
					.	"\n LEFT JOIN " . $_CB_database->NameQuote( '#__comprofiler' ) . " AS c"
					.	' ON c.' . $_CB_database->NameQuote( 'id' ) . ' = u.' . $_CB_database->NameQuote( 'id' )
					.	"\n WHERE " . $_CB_database->NameQuote( 'approved' ) . " = 0"
					.	"\n AND " . $_CB_database->NameQuote( 'confirmed' ) . " = 1";
	$_CB_database->setQuery( $query );
	$users			=	$_CB_database->loadObjectList( null, '\CB\Database\Table\UserTable', array( &$_CB_database ) );;
	HTML_comprofiler::pendingApprovalUsers( $option, $users );
}

//Connections

function addConnection( $userid, $connectionid, $umsg = null, $act = 'connections' ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	if ( ! $ueConfig['allowConnections'] ) {
		$msg	=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		cbNotAuth( true );
		return;
	} else {
		$msg	=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeAddConnectionRequest', array( $userid, $connectionid, &$msg, &$umsg, $act ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon		=	new cbConnection( $userid );

	if ( ! $cbCon->addConnection( $connectionid, stripcslashes( $umsg ) ) ) {
		$msg	=	$cbCon->getErrorMSG();
	} else {
		$msg	=	$cbCon->getUserMSG();
	}

	if ( $act == 'connections' ) {
		cbRedirectToProfile( $userid, $msg, null, 'getConnectionTab' );
	} elseif ( $act == 'manage' ) {
		cbRedirectToProfile( $connectionid, $msg, 'manageconnections', 'cbtabconnections' );
	} else {
		cbRedirectToProfile( $connectionid, $msg );
	}
}

function removeConnection( $userid, $connectionid, $act = 'connections' ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	if ( ! $ueConfig['allowConnections'] ) {
		$msg	=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		cbNotAuth( true );
		return;
	} else {
		$msg	=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeRemoveConnectionRequest', array( $userid, $connectionid, &$msg, $act ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon		=	new cbConnection( $userid );

	if ( ! $cbCon->removeConnection( $userid, $connectionid ) ) {
		$msg	=	$cbCon->getErrorMSG();
	} else {
		$msg	=	$cbCon->getUserMSG();
	}

	if ( $act == 'connections' ) {
		cbRedirectToProfile( $userid, $msg, null, 'getConnectionTab' );
	} elseif ( $act == 'manage' ) {
		cbRedirectToProfile( $connectionid, $msg, 'manageconnections', 'cbtabconnections' );
	} else {
		cbRedirectToProfile( $connectionid, $msg );
	}
}

function denyConnection( $userid, $connectionid, $act = 'connections' ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	if ( ! $ueConfig['allowConnections'] ) {
		$msg	=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		cbNotAuth( true );
		return;
	} else {
		$msg	=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeRemoveConnectionRequest', array( $userid, $connectionid, &$msg, $act ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon		=	new cbConnection( $userid );

	if ( ! $cbCon->denyConnection( $userid, $connectionid ) ) {
		$msg	=	$cbCon->getErrorMSG();
	} else {
		$msg	=	$cbCon->getUserMSG();
	}

	if ( $act == 'connections' ) {
		cbRedirectToProfile( $userid, $msg, null, 'getConnectionTab' );
	} elseif ( $act == 'manage' ) {
		cbRedirectToProfile( $connectionid, $msg, 'manageconnections', 'cbtabconnections' );
	} else {
		cbRedirectToProfile( $connectionid, $msg );
	}
}

function acceptConnection( $userid, $connectionid, $act = 'connections' ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	if ( ! $ueConfig['allowConnections'] ) {
		$msg	=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		cbNotAuth( true );
		return;
	} else {
		$msg	=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeAcceptConnectionRequest', array( $userid, $connectionid, &$msg, $act ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon		=	new cbConnection( $userid );

	if ( $cbCon->isConnectionPending( $userid, $connectionid ) === false ) {
		$_CB_framework->enqueueMessage( CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ), 'error' );
		return;
	} else {
		if ( ! $cbCon->acceptConnection( $userid, $connectionid ) ) {
			$msg	=	$cbCon->getErrorMSG();
		} else {
			$msg	=	$cbCon->getUserMSG();
		}
	}

	if ( $act == 'connections' ) {
		cbRedirectToProfile( $userid, $msg, null, 'getConnectionTab' );
	} elseif ( $act == 'manage' ) {
		cbRedirectToProfile( $connectionid, $msg, 'manageconnections', 'cbtabconnections' );
	} else {
		cbRedirectToProfile( $connectionid, $msg );
	}
}

function manageConnections( $userid ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	if ( ! $ueConfig['allowConnections'] ) {
		$msg		=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ( $_CB_framework->myId() != $userid ) || ( $_CB_framework->myId() == 0 ) ) {
		$msg		=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} else {
		$msg		=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeManageConnectionsRequest', array( $userid, &$msg ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon			=	new cbConnection( $userid );

	$tabs			=	new cbTabs( 0, $_CB_framework->getUi() );
	$tabs->element	=	'';

	$pagingParams	=	$tabs->_getPaging( array(), array( 'connections_' ) );

	$perpage		=	20;		//TBD unhardcode and get the code below better
	$total			=	$cbCon->getConnectionsCount( $userid, true );

	if ( $pagingParams["connections_limitstart"] === null ) {
		$pagingParams["connections_limitstart"]	=	0;
	}

	if ( $pagingParams["connections_limitstart"] > $total ) {
		$pagingParams["connections_limitstart"]	=	0;
	}

	$offset			=	( $pagingParams["connections_limitstart"] ? (int) $pagingParams["connections_limitstart"] : 0 );
	$connections	=	$cbCon->getActiveConnections( $userid, $offset, $perpage );
	$actions		=	$cbCon->getPendingConnections( $userid );
	$connecteds		=	$cbCon->getConnectedToMe( $userid );

	HTML_comprofiler::manageConnections( $connections, $actions, $total, $tabs, $pagingParams, $perpage, $connecteds );
}

function saveConnections( $connectionids ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	// simple spoof check security
	cbSpoofCheck( 'manageconnections' );

	if ( ! $ueConfig['allowConnections'] ) {
		$msg		=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		$msg		=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} else {
		$msg		=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeSaveConnectionsRequest', array( $connectionids, &$msg ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon					=	new cbConnection( $_CB_framework->myId() );

	if ( is_array( $connectionids ) ) foreach( $connectionids as $cid ) {
		$connectionTypes	=	cbGetParam( $_POST, $cid.'connectiontype', array() );

		$cbCon->saveConnection( $cid, stripslashes( cbGetParam( $_POST, $cid . 'description', '' ) ), implode( '|*|', $connectionTypes ) );
	}

	cbRedirectToProfile( null, ( is_array( $connectionids ) ? CBTxt::T( 'UE_CONNECTIONSUPDATEDSUCCESSFULL', 'Your connections are successfully updated!' ) : null ), 'manageconnections', '1' );
}

function processConnectionActions( $connectionids ) {
	global $_CB_framework, $ueConfig, $_PLUGINS;

	// simple spoof check security
	cbSpoofCheck( 'manageconnections' );

	if ( ! $ueConfig['allowConnections'] ) {
		$msg		=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
	} elseif ( ! ( $_CB_framework->myId() > 0 ) ) {
		$msg		=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );
	} else {
		$msg		=	null;
	}

	$_PLUGINS->loadPluginGroup( 'user' );

	$_PLUGINS->trigger( 'onBeforeProcessConnectionsRequest', array( $connectionids, &$msg ) );

	if ( $msg ) {
		$_CB_framework->enqueueMessage( $msg, 'error' );
		return;
	}

	$cbCon			=	new cbConnection( $_CB_framework->myId() );

	if ( is_array( $connectionids ) ) foreach( $connectionids as $cid ) {
		$action		=	cbGetParam( $_POST, $cid . 'action' );

		if ( $action == 'd' ) {
			$cbCon->denyConnection( $_CB_framework->myId(), $cid );
		} elseif ( $action == 'a' ) {
			$cbCon->acceptConnection( $_CB_framework->myId(), $cid );
		}
	}

	$error			=	$cbCon->getErrorMSG();

	if ( $error ) {
		cbRedirect( $_CB_framework->viewUrl( 'manageconnections', false ), $error, 'error' );
	} else {
		cbRedirect( $_CB_framework->viewUrl( 'manageconnections', false ), ( ( is_array($connectionids) ) ? CBTxt::Th( 'UE_CONNECTIONACTIONSSUCCESSFULL', 'Connection actions successful!' ) : null ) );
	}
}
/**
 * Checks if a page is executed https, and if not, if it should be according to login module HTTPS posts specifications
 * 
 * @param  boolean  $return  [default: false] : True: returns if https switchover is needed for the POST form (if not already on HTTPS and login module asks for it). False: errors 403 if not in https and it's configured in login module.
 * @return boolean           True: switchover needed (returned only if $return = true)
 */
function checkCBPostIsHTTPS( $return = false ) {
	global $_CB_framework, $_CB_database, $_SERVER;

	$isHttps			=	( isset( $_SERVER['HTTPS'] ) && ( ! empty( $_SERVER['HTTPS'] ) ) && ( $_SERVER['HTTPS'] != 'off' ) );

	if ( ( ! $isHttps ) && file_exists( $_CB_framework->getCfg( 'absolute_path' ) . '/modules/' . ( checkJversion() > 0 ? 'mod_cblogin/' : null ) . 'mod_cblogin.php' ) ) {
		$query			=	'SELECT ' . $_CB_database->NameQuote( 'params' )
						.	"\n FROM " . $_CB_database->NameQuote( '#__modules' )
						.	"\n WHERE " . $_CB_database->NameQuote( 'module' ) . " = " . $_CB_database->Quote( 'mod_cblogin' )
						.	"\n ORDER BY " . $_CB_database->NameQuote( 'ordering' );
		$_CB_database->setQuery( $query, 0, 1 );
		$module			=	$_CB_database->loadResult();

		if ( $module ) {
			$params		=	new Registry( $module );

			$https_post	=	( $params->get( 'https_post', 0 ) != 0 );
		} else {
			$https_post	=	false;
		}
	} else {
		$https_post		=	false;
	}

	if ( $return ) {
		return $https_post;
	} else {
		if ( $https_post && ( ! $isHttps ) ) {
			header( 'HTTP/1.0 403 Forbidden' );
			exit( CBTxt::T( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' ) );
		}
	}

	return null;
}
