<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 6:05 PM $
* @package CBLib\Permission
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Entity;


defined('CBLIB') or die();

interface AuthoriseInterface
{
	/**
	 * Checks if this entity is authorized to perform $task on $asset
	 *
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return boolean          True: Authorized, False: Not authorized
	 */
	public function isAuthorizedToPerformActionOnAsset( $action, $asset );

	/**
	 * Checks if this entity can view a given access level (or is a super-user who can view everything)
	 *
	 * @param  int      $accessLevel               Access-level to check
	 * @param  boolean  $authoriseAlsoIfSuperUser  [optional default true] authorises also if user is super-user
	 * @return boolean                             True: Can view, False: Can not view
	 */
	public function canViewAccessLevel( $accessLevel, $authoriseAlsoIfSuperUser = true );

	/**
	 * Gets an array of the authorised user-groups for this entity
	 *
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array
	 */
	public function getAuthorisedGroups( $inheritedOnesToo = true );

	/**
	 * Gets an array of the authorised access levels for the entity
	 *
	 * @return int[]
	 */
	public function getAuthorisedViewLevels( );

	/**
	 * Checks if $this user is Super-Admin
	 *
	 * @return boolean  True: Yes, False: No
	 */
	public function isSuperAdmin( );

	/**
	 * Checks if $this user is Moderator for $entity
	 *
	 * @param  AuthoriseInterface  $entity  User Id to moderate
	 * @return boolean                      True: Yes, False: No
	 */
	public function isModeratorFor( AuthoriseInterface $entity );

	/**
	 * Checks if $this user is a Global Moderator for the site or Super-administrator
	 *
	 * @return boolean                      True: Yes, False: No
	 */
	public function isGlobalModerator( );
}
