<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:00 AM $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Language\CBTxt;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * CBACL Class implementation
 * Legacy Class CBACL of CB 1.9
 * @deprecated 2.0 : Use \CBLib\Entity\User\User isAuthorizedToPerformActionOnAsset() and canViewAccessLevel()
 * @see \CBLib\Entity\User\User
 */
class CBACL
{
	/**
	 * @var JAccess
	 */
	protected $_acl;

	/**
	 * @var DatabaseDriverInterface
	 */
	protected $_db;

	/**
	 * Constructor
	 *
	 * @param  JAccess                  $acl
	 * @param  DatabaseDriverInterface  $db
	 */
	public function __construct( $acl, DatabaseDriverInterface $db )
	{
		$this->_acl	=	$acl;
		$this->_db	=	$db;
	}

	/**
	 * @deprecated 2.0 always use ids only for storage
	 *
	 * @param  string       $groupName
	 * @return null|string
	 */
	public function get_group_id( $groupName )
	{
		$query		=	'SELECT ' . $this->_db->NameQuote( 'id' )
			.	"\n FROM " . $this->_db->NameQuote( '#__usergroups' )
			.	"\n WHERE " . $this->_db->NameQuote( 'title' ) . " = " . $this->_db->Quote( $groupName );
		$this->_db->setQuery( $query );
		$return		=	$this->_db->loadResult();

		return $return;
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Get Group name of $groupId
	 *
	 * @deprecated 2.0 use Application::CmsPermissions()->getGroupName( $groupId )
	 * @see CmsPermissionsInterface::getGroupName()
	 *
	 * @param  int          $groupId  Group Id
	 * @return null|string            name (starts with '[' if deleted group name)
	 */
	public function get_group_name( $groupId )
	{
		return Application::CmsPermissions()->getGroupName( $groupId );
	}

	/**
	 * @deprecated 2.0 use Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetName );
	 * @see User::isAuthorizedToPerformActionOnAsset()
	 *
	 * @param  string  $assetName
	 * @param  string  $action
	 * @return boolean
	 */
	public function acl_check( $assetName = null, $action = null )
	{
		return Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetName );
	}

	/**
	 * @deprecated 2.0 use Application::MyUser()->getAuthorisedGroups( $recurse ); or Application::User( (int) $user_id )->getAuthorisedGroups( $recurse );
	 * @see User::getAuthorisedGroups()
	 *
	 * @param null $var_1
	 * @param null $var_2
	 * @param null $var_3
	 * @return array
	 */
	protected function get_object_groups( $var_1 = null, $var_2 = null, $var_3 = null )
	{
		$user_id	=	( is_integer( $var_1 ) ? $var_1 : $var_2 );
		$recurse	=	( $var_3 == 'RECURSE' ? true : false );

		return Application::User( (int) $user_id )->getAuthorisedGroups( $recurse );
	}

	protected function get_group_children( $var_1 = null, /** @noinspection PhpUnusedParameterInspection */ $var_2 = null, $var_3 = null )
	{

		if ( ! $var_3 ) {
			$var_3	=	'NO_RECURSE';
		}

		$query		=	'SELECT g1.' . $this->_db->NameQuote( 'id' )
			.	"\n FROM " . $this->_db->NameQuote( '#__usergroups' ) . " AS g1";

		if ( $var_3 == 'RECURSE' ) {
			$query	.=	"\n LEFT JOIN " . $this->_db->NameQuote( '#__usergroups' ) . " AS g2"
				.	' ON g2.' . $this->_db->NameQuote( 'lft' ) . ' < g1.' . $this->_db->NameQuote( 'lft' )
				.	' AND g2.' . $this->_db->NameQuote( 'rgt' ) . ' > g1.' . $this->_db->NameQuote( 'rgt' )
				.	"\n WHERE g2." . $this->_db->NameQuote( 'id' ) . " = " . (int) $var_1;
		} else {
			$query	.=	"\n WHERE g1." . $this->_db->NameQuote( 'parent_id' ) . " = " . (int) $var_1;

		}

		$query		.=	"\n ORDER BY g1." . $this->_db->NameQuote( 'title' );
		$this->_db->setQuery( $query );
		$return		=	$this->_db->loadResultArray();

		return $return;
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Gets all groups of the CMS
	 *
	 * @deprecated 2.0 use Application::CmsPermissions()->getAllGroups( $html )
	 * @see CmsPermissionsInterface::getAllGroups()
	 *
	 * @param null $var_1
	 * @param null $var_2
	 * @param null $var_3
	 * @param bool $html
	 * @return array|StdClass[]
	 */
	public function get_group_children_tree( /** @noinspection PhpUnusedParameterInspection */ $var_1 = null, $var_2 = null, $var_3 = null, $html = true )
	{
		$userGroups			=	Application::CmsPermissions()->getAllGroups( $html );

		if ( ! $html ) {
			$ugOld			=	array();
			foreach ( $userGroups as $value => $text ) {
				$ugOld[]	=	array( 'value' => $value, 'text' => $text );
			}
			return $ugOld;
		}

		return $userGroups;
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Gets access levels of CMS for $user_id
	 *
	 * @deprecated 2.0 use Application::MyUser()->getAuthorisedViewLevels() or Application::User( (int) $user_id )->getAuthorisedViewLevels()
	 * @see User::getAuthorisedViewLevels()
	 *
	 * @param  int      $user_id
	 * @param  boolean  $recurse		(DEPRECATED 1.8)
	 * @return array of int
	 */
	public function get_object_access( $user_id, /** @noinspection PhpUnusedParameterInspection */ $recurse = false )
	{
		return Application::User( (int) $user_id )->getAuthorisedViewLevels();
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Gives list of view access levels available with translated texts for the levels
	 *
	 * @deprecated 2.0 use Application::CmsPermissions()->getAllViewAccessLevels( $htmlOnly, $filterByVisibleToUser )
	 * @see CmsPermissionsInterface::getAllViewAccessLevels()
	 *
	 * @param  boolean|string  $html                 false/0: array( 'value' =>, 'text' =>), true/1: ready for moscomprofilerHTML::selectList, 2: array( value => text )
	 * @param  boolean         $oldCb1NumberingUnusedNow
	 * @param  boolean         $filterByVisibleToMe  Restrict result by only View Access Levels visible to me
	 * @return array                                 Access levels
	 */
	public function get_access_children_tree( $html = true, /** @noinspection PhpUnusedParameterInspection */ $oldCb1NumberingUnusedNow = true, $filterByVisibleToMe = false )
	{
		$filterByVisibleToUser					=	$filterByVisibleToMe ? Application::MyUser() : null;
		$htmlOnly								=	( $html === true );

		$accessLevels							=	Application::CmsPermissions()->getAllViewAccessLevels( $htmlOnly, $filterByVisibleToUser );

		if ( $html === false ) {
			$oldLevels							=	array();
			foreach ( $accessLevels as $value => $text ) {
				$oldLevels[]					=	array( 'value' => $value, 'text' => $text );
			}
			return $oldLevels;
		}

		return $accessLevels;
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * @deprecated 2.0 use Application::CmsPermissions()->getAllViewAccessLevels( $htmlOnly, $filterByVisibleToUser )
	 *
	 * @param $access_gid
	 * @param $recurse
	 * @param $user_gids
	 * @return bool
	 */
	public function get_allowed_access( $access_gid, $recurse, $user_gids )
	{
		global $_CB_framework;

		if ( ! is_array( $user_gids ) ) {
			$user_gids				=	array( $user_gids );
		}

		if ( ( $access_gid == -2 ) || ( ( $access_gid == -1 ) && ( $user_gids && ( ! in_array(  $_CB_framework->getCfg( 'guest_usergroup' ), $user_gids ) ) ) ) ) {
			return true;
		} else {
			if ( in_array( $access_gid, $user_gids ) ) {
				return true;
			} else {
				if ( $recurse == 'RECURSE' ) {
					/** @noinspection PhpDeprecationInspection */
					$group_children	=	$this->get_group_parent_ids( $access_gid );

					if ( is_array( $group_children ) && ( count( $group_children ) > 0 ) ) {
						if ( array_intersect( $user_gids, $group_children ) ) {
							return true;
						}
					}
				}
			}

			return false;
		}
	}

	/**
	 * @deprecated 2.0 No use anymore for such functionality
	 *
	 * @param  int    $gid
	 * @return array
	 */
	public function get_group_children_ids( $gid )
	{
		static $gids					=	array();

		$gid							=	(int) $gid;

		if ( ! isset( $gids[$gid] ) ) {
			static $grps				=	null;
			static $paths				=	null;

			if ( ! isset( $grps ) ) {
				$query					=	'SELECT *'
					.	"\n FROM " . $this->_db->NameQuote( '#__usergroups' )
					.	"\n ORDER BY " . $this->_db->NameQuote( 'lft' );
				$this->_db->setQuery( $query );
				$grps					=	$this->_db->loadObjectList( 'id' );
			}

			if ( ! array_key_exists( $gid, $grps ) ) {
				return array();
			}

			if ( ! isset( $paths[$gid] ) ) {
				jimport('joomla.access.access');
				$isSuper				=	JAccess::checkGroup( $gid, 'core.admin' );

				$paths[$gid]			=	array();
				foreach( $grps as $grp ) {
					if ( ( ( $grp->lft <= $grps[$gid]->lft ) && ( $grp->rgt >= $grps[$gid]->rgt ) ) || $isSuper ) {
						$paths[$gid][]	=	$grp->id;
					}
				}
			}

			$type						=	$this->get_parent_container( $grps[$gid], $grps );

			if ( in_array( $type, array( 2, 3 ) ) ) {
				/** @noinspection PhpDeprecationInspection */
				$paths[$gid]			=	array_merge( $paths[$gid], array_diff( $this->get_group_parent_ids( 2 ), $this->get_group_parent_ids( $gid ) ) );
			}

			$paths[$gid]				=	array_unique( $paths[$gid] );

			sort( $paths[$gid], SORT_NUMERIC );

			$groups						=	$paths[$gid];

			for ( $i = 0, $n = count( $groups ); $i < $n; $i++ ) {
				$groups[$i]				=	(int) $groups[$i];
			}

			$standardlist				=	array( -2 );

			global $_CB_framework;
			if ( $gid && ( $gid !=  $_CB_framework->getCfg( 'guest_usergroup' ) ) ) {
				$standardlist[]			=	-1;
			}

			$groups						=	array_merge( $groups, $standardlist );

			$gids[$gid]					=	$groups;
		}

		return $gids[$gid];
	}

	/**
	 * @deprecated 2.0 No use anymore for such functionality
	 *
	 * @param  int    $gid
	 * @return array
	 */
	public function get_group_parent_ids( $gid = null )
	{

		static $gids					=	array();

		$gid							=	(int) $gid;

		if ( ! isset( $gids[$gid] ) ) {

			static $grps				=	null;
			static $paths				=	null;

			if ( ! isset( $grps ) ) {
				$query					=	'SELECT *'
					.	"\n FROM " . $this->_db->NameQuote( '#__usergroups' )
					.	"\n ORDER BY " . $this->_db->NameQuote( 'lft' );
				$this->_db->setQuery( $query );
				$grps					=	$this->_db->loadObjectList( 'id' );
			}

			if ( ! array_key_exists( $gid, $grps ) ) {
				return array();
			}

			if ( ! isset( $paths[$gid] ) ) {
				$paths[$gid]			=	array();

				foreach( $grps as $grp ) {
					if ( ( $grp->lft >= $grps[$gid]->lft ) && ( $grp->rgt <= $grps[$gid]->rgt ) ) {
						$paths[$gid][]	=	$grp->id;
					}
				}
			}

			$type						=	$this->get_parent_container( $grps[$gid], $grps );

			if ( ( $type === 1 ) && ( $gid !== 6 ) ) {
				/** @noinspection PhpDeprecationInspection */
				$paths[$gid]			=	array_merge( $paths[$gid], $this->get_group_parent_ids( 6 ) );
			} elseif ( ( $type === 2 ) && ( $gid !== 8 ) ) {
				/** @noinspection PhpDeprecationInspection */
				$paths[$gid]			=	array_merge( $paths[$gid], $this->get_group_parent_ids( 8 ) );
			}

			$paths[$gid]				=	array_unique( $paths[$gid] );

			sort( $paths[$gid], SORT_NUMERIC );

			$groups						=	$paths[$gid];

			for ( $i = 0, $n = count( $groups ); $i < $n; $i++ ) {
				$groups[$i]				=	(int) $groups[$i];
			}

			$gids[$gid]					=	$groups;
		}

		return $gids[$gid];
	}

	protected function get_parent_container( $grp, $groups )
	{
		if ( $grp && $groups ) {
			$id			=	(int) $grp->id;
			$parent		=	(int) $grp->parent_id;
			$grps		=	array( $parent, $id );

			// Go no further if group has no parent:
			if ( $parent ) {
				if ( in_array( 2, $grps ) ) {
					return 1; // Registered
				} elseif ( in_array( 6, $grps ) ) {
					return 2; // Manager
				} elseif ( in_array( 8, $grps ) ) {
					return 3; // Super Administrator
				}

				// Loop through for deep groups:
				return $this->get_parent_container( $groups[$parent], $groups );
			} else {
				return 0; // Root
			}
		}

		return null; // Unknown
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Checks if the user is a super-admin
	 *
	 * @since 1.8 (and param $userId since 1.8.1)
	 * @deprecated 2.0 use \CBLib\Application\Application::MyUser()->isSuperAdmin(); or \CBLib\Application\Application::User( (int) $userId )->isSuperAdmin();
	 * @see \CBLib\Entity\User\User::isSuperAdmin()
	 *
	 * @param  int|null  $userId  User id (default: NULL means logged-in user)
	 * @return boolean            TRUE: Yes, user is super-admin, FALSE otherwise
	 */
	public function amIaSuperAdmin( $userId = null )
	{
		if ( $userId === null ) {
			return Application::MyUser()->isSuperAdmin();
		}

		return Application::User( (int) $userId )->isSuperAdmin();
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Checks if at least a group within $groups gives the authorization to perform an $action on an $asset
	 *
	 * @since 1.8 (and Joomla 1.6+ only for now)
	 * @deprecated 2.0 use Application::CmsPermissions()->checkGroupsForActionOnAsset( $groups, $action, $asset )
	 * @see CmsPermissions::checkGroupsForActionOnAsset()
	 *
	 * @param  array   $groups
	 * @param  string  $action
	 * @param  string  $asset
	 * @return boolean
	 */
	public function authorizeGroupsForAction( $groups, $action = 'core.admin', $asset = 'com_comprofiler' )
	{
		return Application::CmsPermissions()->checkGroupsForActionOnAsset( $groups, $action, $asset );
	}

	/**
	 * @todo: Unused in 2.0: Remove in 2.1:
	 * Gives all groups to which user $myId is assigned (but none below)
	 *
	 * @since 1.8
	 * @deprecated 2.0 use Application::MyUser()->getAuthorisedGroups( false ); or Application::User( (int) $myId )->getAuthorisedGroups( false );
	 * @see User::getAuthorisedGroups()
	 *
	 * @param  int  $myId      User-id
	 * @return array of int    Group ids
	 */
	public function myGroups( $myId )
	{
		return Application::User( (int) $myId )->getAuthorisedGroups( false );
	}

	/**
	 * @deprecated 2.0 No use anymore for such functionality
	 *
	 * @param int      $myId
	 * @param boolean  $raw
	 * @param boolean  $exact
	 * @return array
	 */
	public function get_groups_below_me( $myId = null, $raw = false, $exact = false )
	{
		global $_CB_framework;

		static $gids			=	array();

		if ( $myId == null ) {
			$myId				=	$_CB_framework->myId();
		} else {
			$myId				=	(int) $myId;
		}

		$id						=	(int) $myId . '_'. (int) $exact;

		if ( ! isset( $gids[$id] ) ) {
			$my_groups			=	Application::User( (int) $myId )->getAuthorisedGroups( false );
			$my_gids			=	array();

			if ( $my_groups ) foreach ( $my_groups as $gid ) {
				/** @noinspection PhpDeprecationInspection */
				$my_gids		=	array_unique( array_merge( $my_gids, $this->get_group_children_ids( $gid ) ) );

				/** @noinspection PhpDeprecationInspection */
				$my_gids		=	array_unique( array_merge( $my_gids, $this->get_object_groups( $myId, null, 'RECURSE' ) ) );
			}

			if ( ( ! is_array( $my_gids ) ) || empty( $my_gids ) ) {
				$my_gids		=	array();
			} else {
				cbArrayToInts( $my_gids );

				if ( $exact ) foreach ( $my_gids as $k => $v ) {
					if ( in_array( $v, $my_groups ) ) {
						unset( $my_gids[$k] );
					}
				}
			}

			$groups				=	Application::CmsPermissions()->getAllGroups( true );

			if ( $groups ) {
				foreach ( $groups as $k => $v ) {
					if ( ! in_array( (int) $v->value, $my_gids ) ) {
						unset( $groups[$k] );
					}
				}
			}

			$gids[$id]			=	array_values( $groups );
		}

		$rows					=	$gids[$id];

		if ( $raw ) {
			// in raw mode, makes array of strict ints:
			$grps				=	array( -2 );

			if ( $myId ) {
				$grps[]			=	-1;
			}

			if ( $rows ) {
				foreach ( $rows as $row ) {
					$grps[]		=	(int) $row->value;
				}
			} else {
				$grps[]			=	(int) $_CB_framework->getCfg( 'guest_usergroup' );
			}

			$rows				=	$grps;
		} elseif ( ! $rows ) {
			$rows				=	array();
		}

		return $rows;
	}

	/**
	 * Prepare top most GID from array of IDs
	 * @deprecated 2.0 No use anymore for such functionality
	 *
	 * @param array $gids
	 * @return int
	 */
	public function getBackwardsCompatibleGid( $gids )
	{
		static $mod			=	null;
		static $admin		=	null;
		static $super_admin	=	null;

		if ( $super_admin === null ) {
			$mod			=	$this->mapGroupNamesToValues( 'Manager' );
			$admin			=	$this->mapGroupNamesToValues( 'Administrator' );
			$super_admin	=	$this->mapGroupNamesToValues( 'Superadministrator' );
		}

		$gids				=	(array) $gids;

		cbArrayToInts( $gids );

		if ( in_array( $super_admin, $gids ) ) {
			$gid			=	$super_admin;
		} elseif ( in_array( $admin, $gids ) ) {
			$gid			=	$admin;
		} elseif ( in_array( $mod, $gids ) ) {
			$gid			=	$mod;
		} else {
			$gid			=	( empty( $gids ) ? null : $gids[( count( $gids ) - 1 )] );
		}

		return $gid;
	}

	/**
	 * Remap literal groups (such as in default values) to the hardcoded CMS values
	 * @deprecated 2.0 No use anymore for such functionality: Except for getting Public group: $_CB_framework->getCfg( 'guest_usergroup' )
	 *
	 * @param  string|array  $name  of int|string
	 * @return int|array of int
	 */
	public function mapGroupNamesToValues( $name )
	{
		static $ps						=	null;

		$selected						=	(array) $name;
		foreach ( $selected as $k => $v ) {
			if ( ! is_numeric( $v ) ) {
				if ( ! $ps ) {
					$ps					=	array( 'Root' => 0 , 'Users' => 0 , 'Public' =>  1, 'Registered' =>  2, 'Author' =>  3, 'Editor' =>  4, 'Publisher' =>  5, 'Backend' => 0 , 'Manager' =>  6, 'Administrator' =>  7, 'Superadministrator' =>  8, 'Guest' => 9 );
					if ( ! checkJversion( 'j3.0+' ) ) {
						$ps['Guest']	=	0;
					}
				}
				if ( array_key_exists( $v, $ps ) ) {
					if ( $ps[$v] != 0 ) {
						$selected[$k]	=	$ps[$v];
					} else {
						unset( $selected[$k] );
					}
				} else {
					$selected[$k]		=	$v;
				}
			}
		}
		if ( ! is_array( $name ) ) {
			$selected					=	$selected[0];
		}
		return $selected;
	}

	/**
	 * @deprecated 2.0 No use anymore for such functionality, since we have Permissions for that and we should not be depending on groups
	 *
	 * @param  array    $user_ids
	 * @param  string   $action
	 * @param  boolean  $allow_myself
	 * @return null|string
	 */
	public function get_users_permission( $user_ids, $action, $allow_myself = false )
	{
		global $_CB_framework, $_PLUGINS;

		$msg							=	null;


		if ( is_array( $user_ids ) && count( $user_ids ) ) {
			$obj						=	new UserTable( $this->_db );

			foreach ( $user_ids as $user_id ) {
				if ( $user_id != 0 ) {
					if ( $obj->load( (int) $user_id ) ) {
						/** @noinspection PhpDeprecationInspection */
						$groups			=	$this->get_object_groups( $user_id );

						if ( isset( $groups[0] ) ) {
							$this_group =	strtolower( Application::CmsPermissions()->getGroupName( $groups[0] ) );
						} else {
							$this_group	=	'Registered';
						}
					} else {
						$msg			.=	'User not found. ';
						$this_group		=	null;
					}
				} else {
					$this_group			=	'Registered';
				}

				if ( $user_id == $_CB_framework->myId() ) {
					if ( ! $allow_myself ) {
						$msg			.=	"You cannot $action Yourself! ";
					}
				} else {
					if ( ! Application::MyUser()->isSuperAdmin() ) {
						/** @noinspection PhpDeprecationInspection */
						$userGroups		=	$this->get_object_groups( $user_id );
						/** @noinspection PhpDeprecationInspection */
						$myGroups		=	$this->get_object_groups( $_CB_framework->myId() );

						$iAmAdmin		=	( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' )
											&& Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' ) );
						$exactGids		=	! $iAmAdmin;
						/** @noinspection PhpDeprecationInspection */
						$myGidsTree		=	$this->get_groups_below_me( $_CB_framework->myId(), true, $exactGids );
						$isHeSAdmin		=	Application::User( (int) $user_id )->isSuperAdmin();

						if ( ( ( array_values( $userGroups ) == array_values( $myGroups ) ) && ( ! $iAmAdmin ) )
							|| ( $user_id && $userGroups && ( ! array_intersect( $userGroups, $myGidsTree ) ) )
							|| $isHeSAdmin )
						{
							$msg		.=	"You cannot $action a `$this_group`. Only higher-level users have this power. ";
						}
					}
				}
			}
		} else {
			if ( $user_ids == $_CB_framework->myId() ) {
				if ( ! $allow_myself ) {
					$msg				.=	"You cannot $action Yourself! ";
				}
			} else {
				if ( ! Application::MyUser()->isSuperAdmin() ) {
					/** @noinspection PhpDeprecationInspection */
					$userGroups			=	$this->get_object_groups( $user_ids );
					/** @noinspection PhpDeprecationInspection */
					$myGroups			=	$this->get_object_groups( $_CB_framework->myId() );

					$iAmAdmin			=	( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' )
											&& Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' ) );
					$exactGids			=	! $iAmAdmin;
					/** @noinspection PhpDeprecationInspection */
					$myGidsTree			=	$this->get_groups_below_me( $_CB_framework->myId(), true, $exactGids );
					$isHeSAdmin			=	Application::User( (int) $user_ids )->isSuperAdmin();

					if ( ( ( array_values( $userGroups ) == array_values( $myGroups ) ) && ( ! $iAmAdmin ) )
						|| ( $user_ids && $userGroups && ( ! array_intersect( $userGroups, $myGidsTree ) ) )
						|| $isHeSAdmin )
					{
						$msg			.=	"You cannot $action a user. Only higher-level users have this power. ";
					}
				}
			}
		}

		if ( $_PLUGINS ) {
			$_PLUGINS->trigger( 'onUsersPermission', array( $user_ids, $action, $allow_myself, &$msg ) );
		}

		return $msg;
	}

	/**
	 * @deprecated 2.0 We need to add that Config allowModeratorsUserEdit as param when we remove its use.
	 *             Current uses are only: cbCheckIfUserCanPerformUserTask( $user->id, 'allowModeratorsUserEdit' )
	 *
	 * @param  int     $user_id
	 * @param  string  $action
	 * @return boolean|null|string
	 */
	public function get_user_permission_task( $user_id, $action )
	{
		global $_CB_framework, $_PLUGINS, $ueConfig;

		if ( $user_id == 0 ) {
			$user_id					=	$_CB_framework->myId();
		} else {
			$user_id					=	(int) $user_id;		}

		if ( $user_id == 0 ) {
			$ret						=	false;
		} elseif ( $user_id == $_CB_framework->myId() ) {
			$ret						=	null;
		} else {
			if ( ( ! isset( $ueConfig[$action] ) ) || ( $ueConfig[$action] == 0 ) ) {
				$ret					=	CBTxt::Th( 'UE_FUNCTIONALITY_DISABLED', 'This functionality is currently disabled.' );
			} elseif ( $ueConfig[$action] == 1 ) {
				$isModerator			=	Application::MyUser()->isGlobalModerator();

				if ( ! $isModerator ) {
					$ret				=	false;
				} else {
					$isModerator_user	=	Application::User( (int) $user_id )->isGlobalModerator();

					if ( $isModerator_user ) {
						/** @noinspection PhpDeprecationInspection */
						$ret			=	$this->get_users_permission( array( $user_id ), 'edit', true );
					} else {
						$ret			=	null;
					}
				}
			} elseif ( $ueConfig[$action] > 1 ) {
				// 8: super admins only
				// 7: admins and super admins only
				if ( Application::MyUser()->isSuperAdmin() ) {
					$ret				=	null;
				} elseif ( $ueConfig[$action] != 7 ) {
					$ret				=	false;
				} else {
					// Admins and Super-admins:
					if ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' )
						 && Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' ) )
					{
						$ret			=	null;
					} else {
						$ret			=	false;
					}
				}
			} else {
				$ret					=	false;
			}
		}

		if ( $ret === false ) {
			$ret						=	CBTxt::Th( 'UE_NOT_AUTHORIZED', 'You are not authorized to view this page!' );

			if ( $_CB_framework->myId() < 1 ) {
				$ret 					.=	'<br />' . CBTxt::Th( 'UE_DO_LOGIN', 'You need to log in.' );
			}
		}

		if ( $_PLUGINS ) {
			$_PLUGINS->trigger( 'onUserPermissionTask', array( $user_id, $action, &$ret ) );
		}

		return $ret;
	}

	/**
	 * @deprecated 2.0 use Application::MyUser()->isGlobalModerator(); or Application::User( (int) $user_id )->isGlobalModerator();
	 * @see User::isGlobalModerator()
	 *
	 * @param  int      $user_id
	 * @return boolean
	 */
	public function get_user_moderator( $user_id )
	{
		global $_PLUGINS;

		$isModerator		=	Application::User( (int) $user_id )->isGlobalModerator();

		if ( $_PLUGINS ) {
			$_PLUGINS->trigger( 'onUserModerator', array( $user_id, &$isModerator ) );
		}

		return $isModerator;
	}
}
