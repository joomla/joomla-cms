<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 11:09 PM $
* @package CBLib\Cms
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Cms;


use CBLib\Entity\AuthoriseInterface;

defined('CBLIB') or die();

interface CmsPermissionsInterface
{
	/**
	 * Gets a list of all user Groups available in the CMS ACL
	 *
	 * @param  boolean            $html                   TRUE: Output array of HTML StdClass options value/text, FALSE: Output array( int AccessLevelId => string AccessLevelName )
	 * @param  string             $indentString           Add the indent specified
	 * @return array|\StdClass[]                          Array( id => 'text' ) if $html=false, or array( StdClass with ->value and ->text ) if $html=true
	 */
	public function getAllGroups( $html = false, $indentString = '| ' );

	/**
	 * Gets a list of all View Access Levels available in the CMS ACL (translated by CMS)
	 *
	 * @param  boolean                     $html                   TRUE: Output array of HTML StdClass options value/text, FALSE: Output array( int AccessLevelId => string AccessLevelName )
	 * @param  AuthoriseInterface|boolean  $filterByVisibleToUser  [optional] default: FALSE: No filtering, User: viewing user
	 * @return array|\StdClass[]                                   Array( id => 'text' ) if $html=false, or array( StdClass with ->value and ->text ) if $html=true
	 */
	public function getAllViewAccessLevels( $html = false, AuthoriseInterface $filterByVisibleToUser = null );

	/**
	 * Gets the name of a Group corresponding to the group id
	 *
	 * @param  int      $groupId       Group id
	 * @param  string   $indentString  Add the indent specified
	 * @return string                  Group name (returns translated '[Deleted User Group id %%USER_GROUP_ID%%]' if nonexistent)
	 */
	public function getGroupName( $groupId, $indentString = null );

	/**
	 * Gets the name of a View Access Level corresponding to an id
	 *
	 * @param  int     $accessLevelId  View Access Level id
	 * @return string                  View Access Level name (returns translated '[Deleted Access Level id %%ACCESS_LEVEL_ID%%]' if nonexistent)
	 */
	public function getViewAccessLevelName( $accessLevelId );

	/**
	 * Checks if at least a user group within $groups gives the authorization to perform an $action on an $asset
	 *
	 * @param  int[]   $groups  User Groups ids to check if one of them gives authorization
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return boolean          True: Authorized, False: Not authorized
	 */
	public function checkGroupsForActionOnAsset( $groups, $action, $asset );

	/**
	 * Gets groups which are authorized to perform an $action on an $asset
	 * Warning: Not very fast/efficient: Do not use in front-end. Use only in admin area.
	 *
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return int[]            Group ids that are authorized
	 */
	public function getGroupsAuthorizedForActionOnAsset( $action, $asset );

	/**
	 * Check if any of $groups including their inherited/recursive groups have access to $viewAccessLevel
	 *
	 * @param  int[]|int    $groups           Group(s) wanting access
	 * @param  int          $viewAccessLevel  View Access level of item to access
	 * @return boolean                        True: Has view access, False: No view access
	 */
	public function checkGroupsForViewAccessLevel( $groups, $viewAccessLevel );

	/**
	 * Converts old CB 1.x group ids into CB 2.x View Access Level.
	 * If $titleIfCreate is provided, it will create the new View Access Level if none matches precisely the old way.
	 * THIS FUNCTION IS NOT PART OF THE API AND ONLY FOR INSTALLER USE
	 *
	 * @param  int          $oldGroupId     Old CB 1.x "User Access Group"
	 * @param  string|null  $titleIfCreate  Title for the new View Access Level to create if none match 100%
	 * @return int                          View Access Level corresponding to the $oldGroupId (or new created level)
	 */
	public function convertOldGroupToViewAccessLevel( $oldGroupId, $titleIfCreate );

	/**
	 * Gets all groups configured in a View Access Level (without the permissions-inheriting groups)
	 *
	 * @param  int    $viewAccessLevel     View Access level
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array                       Groups configured for that View Access Level
	 */
	public function getGroupsOfViewAccessLevel( $viewAccessLevel, $inheritedOnesToo );
}
