<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:10 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * CBAuthentication Class implementation
 * CB High-level authentication class and methods:
 */
class CBAuthentication
{
	/**
	 * Logins on host CMS using any allowed authentication methods
	 *
	 * @param  string          $username        The username
	 * @param  string|boolean  $password        Well, The password OR strictly boolean false for login without password
	 * @param  boolean         $rememberMe      If login should be remembered in a cookie to be sent back to user's browser
	 * @param  boolean         $message         If an alert message should be prepared on successful login
	 * @param  string          $return          IN & OUT: IN: return URL NOT SEFED for normal login completition (unless an event says different), OUT: redirection url (no htmlspecialchars) NOT SEFED
	 * @param  array           $messagesToUser  OUT: messages to display to user (html)
	 * @param  array           $alertMessages   OUT: messages to alert to user (text)
	 * @param  int             $loginType       0: username, 1: email, 2: username or email, 3: username, email or CMS authentication
	 * @param  string          $secretKey       secretKey used for two step authentication
	 * @return string                           Error message if error
	 */
	public function login( $username, $password, $rememberMe, $message, &$return, &$messagesToUser, &$alertMessages, $loginType = 0, $secretKey = null )
	{
		global $_CB_framework, $ueConfig, $_PLUGINS;

		$returnURL										=	null;
		$loggedIn										=	false;

		if ( ( ! $username ) || ( ( ! $password ) && ( $password !== false ) ) ) {
			$resultError								=	CBTxt::T( 'LOGIN_INCOMPLETE', 'Please complete the username and password fields.' );
		} else {
			$_PLUGINS->loadPluginGroup('user');
			$_PLUGINS->trigger( 'onBeforeLogin', array( &$username, &$password, &$secretKey ) );

			$resultError								=	null;
			$showSysMessage								=	true;
			$stopLogin									=	false;
			$firstLogin									=	false;

			$row										=	new UserTable();

			if($_PLUGINS->is_errors()) {
				$resultError							=	$_PLUGINS->getErrorMSG();
			} else {
				$foundUser								=	false;

				// Try login by CB authentication trigger:
				$_PLUGINS->trigger( 'onLoginAuthentication', array( &$username, &$password, &$row, $loginType, &$foundUser, &$stopLogin, &$resultError, &$messagesToUser, &$alertMessages, &$return, &$secretKey ) );

				if ( ! $foundUser ) {
					if ( $loginType != 2 ) {
						// login by username:
						$foundUser						=	$row->loadByUsername( $username ) && ( ( $password === false ) || $row->verifyPassword( $password ) );
					}

					if ( ( ! $foundUser ) && ( $loginType >= 1 ) ) {
						// login by email:
						$foundUser						=	$row->loadByEmail( $username ) && ( ( $password === false ) || $row->verifyPassword( $password ) );
						if ( $foundUser ) {
							$username					=	$row->username;
						}
					}

					if ( ( ! $foundUser ) && ( $loginType > 2 ) ) {
						// If no result, try login by CMS authentication:
						if ( $_CB_framework->login( $username, $password, $rememberMe, null, $secretKey ) ) {
							$foundUser					=	$row->load( (int) $_CB_framework->myId() );		// core user might not have username set, so we use id (bug #3303 fix)
							$this->cbSplitSingleName( $row );
							$row->confirmed				=	1;
							$row->approved				=	1;
							$row->store();		// synchronizes with comprofiler table
							$loggedIn					=	true;
						}
					}
				}

				if ( $foundUser ) {
					$returnPluginsOverrides				=	null;
					$pluginResults = $_PLUGINS->trigger( 'onDuringLogin', array( &$row, 1, &$returnPluginsOverrides ) );

					if ( $returnPluginsOverrides ) {
						$return							=	$returnPluginsOverrides;
					}

					if ( is_array( $pluginResults ) && count( $pluginResults ) ) {
						foreach ( $pluginResults as $res ) {
							if ( is_array( $res ) ) {
								if ( isset( $res['messagesToUser'] ) ) {
									$messagesToUser[]	=	$res['messagesToUser'];
								}

								if ( isset( $res['alertMessage'] ) ) {
									$alertMessages[]	=	$res['alertMessage'];
								}

								if ( isset( $res['showSysMessage'] ) ) {
									$showSysMessage		=	$showSysMessage && $res['showSysMessage'];
								}

								if ( isset( $res['stopLogin'] ) ) {
									$stopLogin			=	$stopLogin || $res['stopLogin'];
								}
							}
						}
					}

					if($_PLUGINS->is_errors()) {
						$resultError					=	$_PLUGINS->getErrorMSG();
					}
					elseif ( $stopLogin ) {
						// login stopped: don't even check for errors...
					}
					elseif ($row->approved == 2){
						$resultError					=	CBTxt::T( 'LOGIN_REJECTED', 'Your sign up request was rejected!' );
					}
					elseif ($row->confirmed != 1){
						if ( $row->cbactivation == '' ) {
							$row->store();		// just in case the activation code was missing
						}
						$cbNotification = new cbNotification();
						$cbNotification->sendFromSystem(
							$row->id,
							CBTxt::T(stripslashes($ueConfig['reg_pend_appr_sub'])),
							CBTxt::T(stripslashes($ueConfig['reg_pend_appr_msg'])),
							true,
							( isset( $ueConfig['reg_email_html'] ) ? (int) $ueConfig['reg_email_html']  : 0 )
						);
						$resultError = CBTxt::T( 'LOGIN_NOT_CONFIRMED', 'Your sign up process is not yet complete! Please check again your email for further instructions that have just been resent. If you don\'t find the email, check your spam-box. Make sure that your email account options are not set to immediately delete spam. If that was the case, just try logging in again to receive a new instructions email.' );
					}
					elseif ($row->approved == 0){
						$resultError					=	CBTxt::T( 'LOGIN_NOT_APPROVED', 'Your account has not yet been approved!' );
					}
					elseif ($row->block == 1) {
						$resultError					=	CBTxt::T( 'LOGIN_BLOCKED', 'Your login is blocked.' );
					}
					elseif ($row->lastvisitDate == '0000-00-00 00:00:00') {
						$firstLogin						=	true;
						if (isset($ueConfig['reg_first_visit_url']) and ($ueConfig['reg_first_visit_url'] != "")) {
							$return						=	$ueConfig['reg_first_visit_url'];
						} else {
							if ( $returnPluginsOverrides ) {
								$return					=	$returnPluginsOverrides;	// by default return to homepage on first login (or on page overridden by plugin).
							}
						}

						$_PLUGINS->trigger( 'onBeforeFirstLogin', array( &$row, $username, $password, &$return, $secretKey ));

						if ($_PLUGINS->is_errors()) {
							$resultError				=	$_PLUGINS->getErrorMSG( "<br />" );
						}
					}
				} else {
					if ( $loginType < 2 ) {
						$resultError					=	CBTxt::T( 'LOGIN_INCORRECT_USER_NOT_FOUND LOGIN_INCORRECT', 'Incorrect username or password. Please try again.' );
					} else {
						$resultError					=	CBTxt::T( 'UE_INCORRECT_EMAIL_OR_PASSWORD', 'Incorrect email or password. Please try again.' );
					}
				}
			}

			if ( $resultError ) {
				if ( $showSysMessage ) {
					$alertMessages[]					=	$resultError;
				}
			} elseif ( ! $stopLogin ) {
				if ( ! $loggedIn ) {
					$_PLUGINS->trigger( 'onDoLoginNow', array( $username, $password, $rememberMe, &$row, &$loggedIn, &$resultError, &$messagesToUser, &$alertMessages, &$return, $secretKey ) );
				}

				if ( ! $loggedIn ) {
					$_CB_framework->login( $username, $password, $rememberMe, null, $secretKey );
					$loggedIn							=	true;
				}

				if ( $firstLogin ) {
					$_PLUGINS->trigger( 'onAfterFirstLogin', array( &$row, $loggedIn ) );
				}

				$_PLUGINS->trigger( 'onAfterLogin', array( &$row, $loggedIn ) );

				if ( $loggedIn && $message && $showSysMessage ) {
					$alertMessages[]					=	CBTxt::T( 'LOGIN_SUCCESS', 'You have successfully logged in' );
				}

				if ( ! $loggedIn ) {
					$resultError						=	CBTxt::T( 'LOGIN_INCORRECT_USER_AUTHENTICATION_FAILED LOGIN_INCORRECT', 'Incorrect username or password. Please try again.' );
				}

				// changing com_comprofiler to comprofiler is a quick-fix for SEF ON on return path...
				if ( $return && !( strpos( $return, 'comprofiler' /* 'com_comprofiler' */ ) && ( strpos( $return, 'login') || strpos( $return, 'logout') || strpos( $return, 'registers' ) || strpos( strtolower( $return ), 'lostpassword' ) ) ) ) {
					// checks for the presence of a return url
					// and ensures that this url is not the registration or login pages
					$returnURL							=	$return;
				} elseif ( ! $returnURL ) {
					$returnURL							=	'index.php';
				}
			}
		}

		if ( ! $loggedIn ) {
			$_PLUGINS->trigger( 'onLoginFailed', array( &$resultError, &$returnURL ) );
		}

		$return											=	$returnURL;

		return $resultError;
	}

	/**
	 * Logouts on host CMS using any allowed authentication methods
	 *
	 * @param  string  $return   IN&OUT: IN: suggested URL for redirect, OUT: needed URL for redirect (unsefed)
	 * @return string            null or HTML-clean error to display
	 */
	public function logout( $return )
	{
		global $_POST, $_CB_framework, $_PLUGINS;

		$myId				=	(int) $_CB_framework->myId();

		if ( $myId ) {
			$myCbUser		=	CBuser::getInstance( $myId );

			if ( $myCbUser !== null ) {
				$myUser		=	$myCbUser->getUserData();

				$_PLUGINS->loadPluginGroup('user');
				$_PLUGINS->trigger( 'onBeforeLogout', array( $myUser ) );

				if($_PLUGINS->is_errors()) {
					return $_PLUGINS->getErrorMSG();
				}

				$loggedOut	=	false;

				$_PLUGINS->trigger( 'onDoLogoutNow', array( &$loggedOut, &$myUser, &$return ) );

				if ( ! $loggedOut ) {
					$_CB_framework->logout();
				}

				$_PLUGINS->trigger( 'onAfterLogout', array( $myUser, true ) );
			}
		}

		if ( ! ( ( cbStartOfStringMatch( $return, $_CB_framework->getCfg( 'live_site' ) ) || cbStartOfStringMatch( $return, 'index.php' ) ) ) ) {
			$return			=	null;
		} elseif ( strpos( $return, 'comprofiler' /* 'com_comprofiler' */ ) && ( strpos( $return, 'login') || strpos( $return, 'logout') || strpos( $return, 'registers' ) || strpos( strtolower( $return ), 'lostpassword' ) ) ) {
			// checks for the presence of a return url
			// and ensures that this url is not the registration or login pages
			$return			=	null;
		}

		return null;
	}

	/**
	 * Splits $user->name into $user->firstname, $user->middlename, $user->lastname
	 *
	 * @param  UserTable  $user  IN+OUT: changes name, firstname, middlename and/or lastname depending on CB Config
	 * @return void
	 */
	private function cbSplitSingleName( $user )
	{
		global $ueConfig;

		switch ( $ueConfig['name_style'] ) {
			case 2:
				// firstname + lastname:
				$posLname					=	strrpos( $user->name, ' ' );
				if ( $posLname !== false ) {
					$user->firstname		=	substr( $user->name, 0, $posLname );
					$user->lastname			=	substr( $user->name, $posLname + 1 );
				} else {
					$user->firstname		=	'';
					$user->lastname			=	$user->name;
				}
				// Equivalent to:
				// $sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,lastname,firstname) "
				//	  ." SELECT id,id, SUBSTRING_INDEX(name,' ',-1), "
				//					 ."SUBSTRING( name, 1, length( name ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ) "
				//	  ." FROM #__users";
				break;

			case 3:
				// firstname + middlename + lastname:
				$posMname					=	strpos( $user->name, ' ' );
				$posLname					=	strrpos( $user->name, ' ' );
				if ( $posLname !== false ) {
					$user->lastname			=	substr( $user->name, $posLname + 1 );
					$user->firstname		=	substr( $user->name, 0, $posMname );
					if ( $posMname !== $posLname ) {
						$user->middlename	=	substr( $user->name, $posMname + 1, $posLname - $posMname -1 );
					} else {
						$user->middlename	=	'';
					}
				} else {
					$user->firstname		=	'';
					$user->lastname			=	$user->name;
				}
				// Equivalent to:
				// $sql = "INSERT IGNORE INTO #__comprofiler(id,user_id,middlename,lastname,firstname) "
				//	 . " SELECT id,id,SUBSTRING( name, INSTR( name, ' ' ) +1,"
				//	 						  ." length( name ) - INSTR( name, ' ' ) - length( SUBSTRING_INDEX( name, ' ', -1 ) ) -1 ),"
				//	 		 ." SUBSTRING_INDEX(name,' ',-1),"
				//	 		 ." IF(INSTR(name,' '),SUBSTRING_INDEX( name, ' ', 1 ),'') "
				//	 . " FROM #__users";
				break;

			default:
				// name only: nothing to do !
				break;
		}
	}
}
