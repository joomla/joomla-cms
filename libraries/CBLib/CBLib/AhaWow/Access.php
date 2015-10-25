<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 08.06.13 17:38 $
* @package CBLib\AhaWow
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\AhaWow;

use CBLib\Application\Application;
use CBLib\Language\CBTxt;
use CBLib\Xml\SimpleXMLElement;

defined('CBLIB') or die();

/**
 * CBLib\AhaWow\Access Class implementation
 * 
 */
class Access {

	/**
	 * Checks authorization to perform an action: <action permission="core.edit or core.edit.own and core.manage" (and has prio over or)
	 *
	 * @param  SimpleXMLElement  $action
	 * @return boolean
	 * @throws \InvalidArgumentException
	 */
	public static function authorised( $action ) {
		$permission		=	$action->attributes( 'permission' );
		if ( $permission === null ) {
			return true;
		}

		$assetname			=	$action->attributes( 'permissionasset' );

		if ( ! $assetname ) {
			$parent			=	$action->xpath( 'ancestor::*[@permissionasset]' );

			if ( $parent ) {
				$assetname	=	$parent[0]->attributes( 'permissionasset' );
			}
		}

		if ( ! $assetname ) {
			trigger_error( CBTxt::T( 'TAG_NAME_MISSING_ASSET_NAME', '[tag] [name] missing asset name', array( '[tag]' => $action->getName(), '[name]' => $action->attributes( 'name' ) ) ) );
			$assetName		=	'com_cbsubs';		// CBSubs GPL 3.0.0 is the only ones that will ever need that !
		}

		/// $me				=	CBuser::getMyInstance();

		$ors			=	explode( ' or ', $permission );
		foreach ( $ors as $or ) {
			$ands		=	explode( ' and ', $or );
			$stillOk	=	true;
			foreach ( $ands as $perm ) {
				/// if ( ! $me->authoriseAction( trim( $perm ), $assetname ) ) {
				if ( ! static::authoriseAction( trim( $perm ), $assetname ) ) {
					$stillOk	=	false;
					break;
				}
			}
			if ( $stillOk ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check for authorization to perform an action on an asset.
	 *
	 * $action:
	 * Configure         core.admin
	 * Access component  core.manage
	 * Create            core.create
	 * Delete            core.delete
	 * Edit              core.edit
	 * Edit State        core.edit.state    (e.g. block users and get CB/users administration mails)
	 * Edit Own          core.edit.own
	 *
	 * Baskets:
	 * Pay:              baskets.pay
	 * Record payment    baskets.recordpayment
	 * Refund:           baskets.refund
	 *
	 * $assetname:
	 * 'com_comprofiler.plugin.cbsubs' (default) : For all CBSubs aspects except user management
	 * '.plan.id'                  : For plan number id
	 * 'com_users'                 : For all user management aspects (except core.manage, left for deactivating core Joomla User)
	 * null                        : For global super-user rights check: ( 'core.admin', null )
	 *
	 * @since 2.0
	 *
	 * @param  string        $action     Action to perform: core.admin, core.manage, core.create, core.delete, core.edit, core.edit.state, core.edit.own, ...
	 * @param  string        $assetName  OPTIONAL: asset name e.g. "com_comprofiler.plugin.$pluginId" or "com_users", or null for global rights
	 * @return boolean|null              True: Authorized, False: Not Authorized, Null: Default (not authorized
	 * @throws \InvalidArgumentException
	 */
	public static function authoriseAction( $action, $assetName = 'root' ) {
		global $_CB_framework;

		if ( ! $assetName ) {
			trigger_error( CBTxt::T( 'ACTION_MISSING_ASSET_NAME', '[action] missing asset name', array( '[action]' => $action ) ), E_USER_NOTICE );
			$assetName							=	'com_cbsubs';		// CBSubs GPL 3.0.0 is the only ones that will ever need that !
		}

		static $cache							=	array();

		$myId									=	$_CB_framework->myId();

		if ( ! isset( $cache[$myId][$assetName][$action] ) ) {
			if ( Application::MyUser()->isSuperAdmin() ) {
				// Super Admins have all rights:
				$authorized						=	true;
			} else {
				// Send null asset name if requesting root permissions:
				if ( $assetName == 'root' ) {
					$assetName					=	null;
				}

				$authorized						=	Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetName );
			}

			$cache[$myId][$assetName][$action]	=	$authorized;
		}

		return $cache[$myId][$assetName][$action];
	}
}
