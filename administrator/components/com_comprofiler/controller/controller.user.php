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
use CB\Database\Table\UserTable;

// ensure this file is being included by a parent file
if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

class CBController_user {

	/**
	 * Checks user access permission
	 *
	 * @param  int $userIdPosted
	 * @return null|string
	 */
	private function _authorizedEdit( $userIdPosted ) {
		global $_CB_framework;

		$iAmAdmin				=	Application::MyUser()->isSuperAdmin();

		if ( ! $iAmAdmin ) {
			if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' ) ) {
				if ( $userIdPosted == 0 ) {
					$action		=	'core.create';
				} elseif ( $userIdPosted == $_CB_framework->myId() ) {
					$action		=	'core.edit.own';
				} else {
					$action		=	'core.edit';
				}

				$iAmAdmin		=	( Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, 'com_users' )
									&& ( ! Application::User( (int) $userIdPosted )->isSuperAdmin() ) );
			}
		}

		if ( ! $iAmAdmin ) {
			return CBTxt::T( "Not Authorized" );
		} else {
			return null;
		}
	}

	/**
	 * Outputs legacy user edit display
	 *
	 * @param int $uid
	 * @param string $option
	 */
	public function editUser( $uid = 0, $option = 'users' ) {
		global $_CB_framework, $_PLUGINS;

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );

		$msg						=	$this->_authorizedEdit( (int) $uid );

		if ( ! $msg ) {
			$msg					=	checkCBpermissions( array( (int) $uid ), 'edit', true );
		}

		$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( (int) $uid, &$msg, 2 ) );

		if ( $msg ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), $msg, 'error' );
		}

		$_PLUGINS->loadPluginGroup( 'user' );

		$cbUser						=&	CBuser::getInstance( (int) $uid );
		$cmsUserExists				=	( (int) $uid != 0 ) && ( $cbUser !== null );

		if ( ! $cmsUserExists ) {
			$cbUser					=&	CBuser::getInstance( null );
		}

		$user						=&	$cbUser->getUserData();

		if ( $cmsUserExists && ( $user->user_id != null ) ) {
			// Edit existing CB user:
			$newCBuser				=	0;
		} else {
			$newCBuser				=	1;

			if ( $cmsUserExists ) {
				// Edit existing CMS (but new CB) user:
				$user->approved		=	1;
				$user->confirmed	=	1;
				$user->banned		=	0;
			} else {
				// New user:
				$user->block		=	0;
				$user->approved		=	1;
				$user->confirmed	=	1;
				$user->sendEmail	=	0;
				$user->banned		=	0;
				$user->gids			=	array( (int) $_CB_framework->getCfg( 'new_usertype' ) );
			}
		}

		$null						=	null;
		$userView					=	_CBloadView( 'user' );

		/** @var CBController_user $userView */
		$userView->edituser( $user, $option, $newCBuser, $null );
	}

	/**
	 * Saves legacy user edit display
	 *
	 * @param string $option
	 * @param string $task
	 */
	public function saveUser( $option, $task = 'save' ) {
		global $_CB_framework, $_CB_Backend_task, $_POST, $_PLUGINS;

		cbimport( 'language.all' );
		cbimport( 'cb.tabs' );
		cbimport( 'cb.params' );
		cbimport( 'cb.adminfilesystem' );
		cbimport( 'cb.imgtoolbox' );

		$userIdPosted				=	(int) cbGetParam( $_POST, 'id', 0 );

		if ( $userIdPosted == 0 ) {
			$_POST['id']			=	null;
		}

		$msg						=	$this->_authorizedEdit( $userIdPosted );

		if ( ! $msg ) {
			if ( $userIdPosted != 0 ) {
				$msg				=	checkCBpermissions( array( $userIdPosted ), 'save', true );
			} else {
				$msg				=	checkCBpermissions( null, 'save', true );
			}
		}

		if ( $userIdPosted != 0 ) {
			$_PLUGINS->trigger( 'onBeforeUserProfileSaveRequest', array( $userIdPosted, &$msg, 2 ) );
		}

		if ( $msg ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), $msg, 'error' );
		}

		$_PLUGINS->loadPluginGroup( 'user' );

		// Get current user state:

		if ( $userIdPosted != 0 ) {
			$userComplete			=	CBuser::getUserDataInstance( $userIdPosted );

			if ( ! ( $userComplete && $userComplete->id ) ) {
				cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), CBTxt::T( 'Your profile could not be updated.' ), 'error' );
			}
		} else {
			$userComplete			=	new UserTable();
		}

		// Store new user state:
		$saveResult					=	$userComplete->saveSafely( $_POST, $_CB_framework->getUi(), 'edit' );

		if ( ! $saveResult ) {
			$regErrorMSG			=	$userComplete->getError();

			$msg					=	checkCBpermissions( array( (int) $userComplete->id ), 'edit', true );

			if ( $userIdPosted != 0 ) {
				$_PLUGINS->trigger( 'onBeforeUserProfileEditRequest', array( (int) $userComplete->id, &$msg, 2 ) );
			}

			if ( $msg ) {
				cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), $msg, 'error' );
			}

			if ( $userIdPosted != 0 ) {
				$_PLUGINS->trigger( 'onAfterUserProfileSaveFailed', array( &$userComplete, &$regErrorMSG, 2 ) );
			} else {
				$_PLUGINS->trigger( 'onAfterUserRegistrationSaveFailed', array( &$userComplete, &$regErrorMSG, 2 ) );
			}

			$_CB_framework->enqueueMessage( $regErrorMSG, 'error' );

			$_CB_Backend_task		=	'edit'; // so the toolbar comes up...

			$_PLUGINS->loadPluginGroup( 'user' ); // resets plugin errors

			$userView				=	_CBloadView( 'user' );

			/** @var CBController_user $userView */
			$userView->edituser( $userComplete, $option, ( $userComplete->user_id != null ? 0 : 1 ), $_POST );
			return;
		}

		// Checks-in the row:
		$userComplete->checkin();

		if ( $userIdPosted != 0 ) {
			$_PLUGINS->trigger( 'onAfterUserProfileSaved', array( &$userComplete, 2 ) );
		} else {
			$messagesToUser			=	array();

			$_PLUGINS->trigger( 'onAfterSaveUserRegistration', array( &$userComplete, &$messagesToUser, 2 ) );
		}

		if ( $task == 'apply' ) {
			cbRedirect( $_CB_framework->backendViewUrl( 'edit', false, array( 'cid' => (int) $userComplete->user_id ) ), CBTxt::T( 'SUCCESSFULLY_SAVED_USER_USERNAME', 'Successfully Saved User: [username]', array( '[username]' => $userComplete->username ) ) );
		} else {
			cbRedirect( $_CB_framework->backendViewUrl( 'showusers', false ), CBTxt::T( 'SUCCESSFULLY_SAVED_USER_USERNAME', 'Successfully Saved User: [username]', array( '[username]' => $userComplete->username ) ) );
		}
	}
}