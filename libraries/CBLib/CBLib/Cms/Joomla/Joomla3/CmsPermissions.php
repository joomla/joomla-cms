<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 11:10 PM $
* @package CBLib\Cms\Joomla\Joomla3
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla\Joomla3;

use CBLib\Cms\CmsPermissionsInterface;
use CBLib\Cms\Joomla\CmsPermissionsUpgrader;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Entity\AuthoriseInterface;
use CBLib\Language\CBTxt;
use JAccess;
use JHtml;
use JText;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\Joomla3\CmsPermissions Class implementation
 * 
 */
class CmsPermissions implements CmsPermissionsInterface
{
	/**
	 * All groups as objects with ->value, ->text (untranslated) and ->level properties
	 * @var \StdClass[]
	 */
	protected $allGroups				=	null;

	/**
	 * All groups as objects with ->value, ->text (untranslated) and ->level properties
	 * @var array[]
	 */
	protected $allGroupTrees			=	array();

	/**
	 * Groups allowed to perform an action on an asset as array( action => array( asset => array( groups ) ) )
	 * @var array[]
	 */
	protected $groupsForActionOnAsset	=	array();
	/**
	 * All View Access Levels as array( value => 'translated-text' )
	 * @var string[]
	 */
	protected $allViewAccessLevels		=	null;

	/**
	 * JSON-strings of access levels' rules
	 * @var string[]
	 */
	protected $allViewAccessLevelRules	=	array();

	/**
	 * @var DatabaseDriverInterface
	 */
	protected $_db;

	/**
	 * Constructor for Container make
	 *
	 * @param  DatabaseDriverInterface  $db  Database to use for particular queries (see at end)
	 */
	public function __construct( DatabaseDriverInterface $db )
	{
		$this->_db				=	$db;
	}

	/**
	 * Gets a list of all user Groups available in the CMS ACL
	 *
	 * @param  boolean            $html                   TRUE: Output array of HTML StdClass options value/text, FALSE: Output array( int AccessLevelId => string AccessLevelName )
	 * @param  string             $indentString           Add the indent specified
	 * @return array|\StdClass[]                          Array( id => 'text' ) if $html=false, or array( StdClass with ->value and ->text ) if $html=true
	 */
	public function getAllGroups( $html = false, $indentString = '| ' )
	{
		$groups					=	$this->loadAllGroups();

		$userGroups				=	array();

		foreach ( $groups as $group ) {
			$indentedText		=	str_repeat( $indentString, $group->level ) . JText::_( $group->text );

			if ( $html ) {
				$userGroups[]	=	JHtml::_( 'select.option', $group->value, $indentedText );
			} else {
				$userGroups[(int) $group->value]	=	$indentedText;
			}
		}

		return $userGroups;
	}

	/**
	 * Gets a list of all View Access Levels available in the CMS ACL (translated by CMS)
	 *
	 * @param  boolean                     $html                   TRUE: Output array of HTML StdClass options value/text, FALSE: Output array( int AccessLevelId => string AccessLevelName )
	 * @param  AuthoriseInterface|boolean  $filterByVisibleToUser  [optional] default: FALSE: No filtering, User: viewing user
	 * @return array|\StdClass[]                                   Array( id => 'text' ) if $html=false, or array( StdClass with ->value and ->text ) if $html=true
	 */
	public function getAllViewAccessLevels( $html = false, AuthoriseInterface $filterByVisibleToUser = null )
	{
		$filter							=	false;
		$viewAccessLevels				=	array();

		if ( $filterByVisibleToUser !== null ) {
			if ( ! $filterByVisibleToUser->isSuperAdmin() ) {
				$filter					=	true;
				$viewAccessLevels		=	$filterByVisibleToUser->getAuthorisedViewLevels();
			}
		}

		$access_levels					=	array();

		$levels							=	$this->loadAllViewAccessLevels();

		if ( ( ! $html ) && ( ! $filter ) ) {
			return $levels;
		}

		foreach ( $levels as $value => $text ) {
			if ( $filter && ( ! in_array( (int) $value, $viewAccessLevels ) ) ) {
				continue;
			}

			if ( $html ) {
				$access_levels[]		=	\JHtml::_( 'select.option', $value, $text );
			} else {
				$access_levels[$value]	=	$text;
			}
		}

		return $access_levels;
	}

	/**
	 * Gets the name of a Group corresponding to the group id
	 *
	 * @param  int      $groupId       Group id
	 * @param  string   $indentString  Add the indent specified
	 * @return string                  Group name (returns translated '[Deleted User Group id %%USER_GROUP_ID%%]' if nonexistent)
	 */
	public function getGroupName( $groupId, $indentString = null )
	{
		$groupId				=	(int) $groupId;

		$groups					=	$this->loadAllGroups();

		if ( isset( $groups[$groupId] ) ) {
			$text				=	JText::_( $groups[$groupId]->text );

			if ( $indentString !== null ) {
				$text			=	str_repeat( $indentString, $groups[$groupId]->level ) . $text;
			}

			return $text;
		}

		return CBTxt::T( 'DELETED_USER_GROUP_ID_USER_GROUP_ID', '[Deleted User Group id %%USER_GROUP_ID%%]', array( '%%USER_GROUP_ID%%' => $groupId ) );
	}

	/**
	 * Gets the name of a View Access Level corresponding to an id
	 *
	 * @param  int     $accessLevelId  View Access Level id
	 * @return string                  View Access Level name (returns translated '[Deleted Access Level id %%ACCESS_LEVEL_ID%%]' if nonexistent)
	 */
	public function getViewAccessLevelName( $accessLevelId )
	{
		$accessLevelId		=	(int) $accessLevelId;

		$levels				=	$this->loadAllViewAccessLevels();

		if ( isset( $levels[$accessLevelId] ) ) {
			return $levels[$accessLevelId];
		}

		return CBTxt::T( 'DELETED_ACCESS_LEVEL_ID_ACCESS_LEVEL_ID', '[Deleted Access Level id %%ACCESS_LEVEL_ID%%]', array( '%%ACCESS_LEVEL_ID%%' => $accessLevelId ) );
	}

	/**
	 * Checks if at least a user group within $groups gives the authorization to perform an $action on an $asset
	 *
	 * @param  int[]   $groups  User Groups ids to check if one of them gives authorization
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return boolean          True: Authorized, False: Not authorized
	 */
	public function checkGroupsForActionOnAsset( $groups, $action, $asset )
	{
		foreach ( $groups as $gid) {
			if ( $this->checkGroup( $gid, $action, $asset ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Gets groups which are authorized to perform an $action on an $asset
	 * Warning: Not very fast/efficient: Do not use in front-end. Use only in admin area.
	 *
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return int[]            Group ids that are authorized
	 */
	public function getGroupsAuthorizedForActionOnAsset( $action, $asset )
	{
		if ( ! $asset ) {
			$asset											=	'root';
		}

		if ( ! isset( $this->groupsForActionOnAsset[$action] ) ) {
			$this->groupsForActionOnAsset[$action]			=	array();
		}

		if ( ! isset( $this->groupsForActionOnAsset[$action][$asset] ) ) {

			$allGroups										=	array_keys( $this->loadAllGroups() );

			$authorizedGroups								=	array();

			foreach ( $allGroups as $gid ) {
				if ( $this->checkGroup( $gid, $action, $asset ) ) {
					$authorizedGroups[]						=	$gid;
				}
			}

			$this->groupsForActionOnAsset[$action][$asset]	=	$authorizedGroups;
		}

		return $this->groupsForActionOnAsset[$action][$asset];
	}

	/**
	 * Method to check if a group is authorised to perform an action on an asset.
	 *
	 * @param   integer  $groupId  The path to the group for which to check authorisation.
	 * @param   string   $action   The name of the action to authorise.
	 * @param   mixed    $asset    Integer asset id or the name of the asset as a string.  Defaults to the global asset node.
	 * @return  boolean            True if authorised.
	 */
	protected function checkGroup( $groupId, $action, $asset )
	{
		return JAccess::checkGroup( $groupId, $action, $asset );
	}

	/**
	 * Check if any of $groups including their inherited/recursive groups have access to $viewAccessLevel
	 *
	 * @param  int[]|int    $groups           Group(s) wanting access
	 * @param  int          $viewAccessLevel  View Access level of item to access
	 * @return boolean                        True: Has view access, False: No view access
	 */
	public function checkGroupsForViewAccessLevel( $groups, $viewAccessLevel )
	{
		$allGroups					=	array();

		$groupsOfViewAccessLevel	=	$this->getGroupsOfViewAccessLevel( $viewAccessLevel, false );

		if ( count( array_intersect( (array) $groups, $allGroups ) ) > 0 ) {
			return true;
		}

		foreach ( $groupsOfViewAccessLevel as $g ) {
			$allGroups				=	array_merge( $allGroups, $this->getGroupTree( $g ) );
		}

		return ( count( array_intersect( (array) $groups, $allGroups ) ) > 0 );
	}

	/**
	 * Converts old CB 1.x group ids into CB 2.x View Access Level.
	 * If $titleIfCreate is provided, it will create the new View Access Level if none matches precisely the old way.
	 * THIS FUNCTION IS NOT PART OF THE API AND ONLY FOR INSTALLER USE
	 *
	 * @param  int          $oldGroupId     Old CB 1.x "User Access Group"
	 * @param  string|null  $titleIfCreate  Title for the new View Access Level to create if none match 100%
	 * @return int                          View Access Level corresponding to the $oldGroupId (or new created level)
	 */
	public function convertOldGroupToViewAccessLevel( $oldGroupId, $titleIfCreate )
	{
		return CmsPermissionsUpgrader::_convertOldGroupToViewAccessLevel( $this, $oldGroupId, $titleIfCreate );
	}

	/**
	 * Gets all groups configured in a View Access Level (without the permissions-inheriting groups)
	 *
	 * @param  int    $viewAccessLevel     View Access level
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array                       Groups configured for that View Access Level
	 */
	public function getGroupsOfViewAccessLevel( $viewAccessLevel, $inheritedOnesToo )
	{
		$viewAccessLevel	=	(int) $viewAccessLevel;

		$this->loadAllViewAccessLevels();
		if ( substr( $this->allViewAccessLevelRules[$viewAccessLevel], 0, 1 ) == '[' ) {
			$groups			=	json_decode( $this->allViewAccessLevelRules[$viewAccessLevel] );

			if ( $inheritedOnesToo ) {
				$groups		=	$this->getAllGroupsTree( $groups );
			}
		} else {
			$groups			=	array();
		}

		return $groups;
	}

	/**
	 * Gets all permissions-inheriting groups for a given set of Group Ids
	 *
	 * @param  int[]  $groupsIds  Group Id
	 * @return int[]              Groups (including Group Id) that also inherit groups view access levels authorizations
	 */
	protected function getAllGroupsTree( $groupsIds )
	{
		$allGroups	=	array();

		foreach ( $groupsIds as $group ) {
			$allGroups	=	array_merge( $allGroups, $this->getGroupTree( $group ) );
		}

		return array_unique( $allGroups, SORT_NUMERIC );
	}

	/**
	 * Gets all permissions-inheriting groups for a given Group Id
	 *
	 * @param  int    $groupId  Group Id
	 * @return int[]            Groups (including Group Id) that also inherit groups view access levels authorizations
	 */
	protected function getGroupTree( $groupId )
	{
		$groupId			=	(int) $groupId;

		$allGroups			=	$this->loadAllGroups();

		if ( ! array_key_exists( $groupId, $allGroups ) ) {
			return array();
		}

		// Get parent groups and leaf group
		if ( ! isset( $this->allGroupTrees[$groupId] ) ) {
			$this->allGroupTrees[$groupId] = array();

			$groupIdGroup	=	$allGroups[$groupId];

			foreach ($allGroups as $group)
			{
				// Here to get the Path to the ROOT of the tree (to get the groups from which the $groupId inherits, the line below would be:
				// if ($group->lft <= $groupIdGroup->lft && $group->rgt >= $groupIdGroup->rgt)

				if ($group->lft >= $groupIdGroup->lft && $group->rgt <= $groupIdGroup->rgt)
				{
					$this->allGroupTrees[$groupId][]	=	(int) $group->value;	// which is $group->id
				}
			}
		}

		return $this->allGroupTrees[$groupId];
	}

	/**
	 * Internal method: Loads if needed all User Groups, and caches them
	 *
	 * @return \StdClass[]  All groups as objects with ->value, ->text and ->level properties
	 */
	protected function loadAllGroups( )
	{
		if ( $this->allGroups === null ) {
			// Not possible since this is part of com_users:  $groups = \UsersHelper::getGroups();

			$query					=	'SELECT a.' . $this->_db->NameQuote( 'id' ) . ' AS value'
				.	', a.' . $this->_db->NameQuote( 'title' ) . ' AS text'
				.	', COUNT( DISTINCT b.' . $this->_db->NameQuote( 'id' ) . ' ) AS level'
				.	', a.' . $this->_db->NameQuote( 'lft' )
				.	', a.' . $this->_db->NameQuote( 'rgt' )
				.	"\n FROM " . $this->_db->NameQuote( '#__usergroups' ) . " AS a"
				.	"\n LEFT JOIN " . $this->_db->NameQuote( '#__usergroups' ) . " AS b"
				.	' ON a.' . $this->_db->NameQuote( 'lft' ) . ' > b.' . $this->_db->NameQuote( 'lft' )
				.	' AND a.' . $this->_db->NameQuote( 'rgt' ) . ' < b.' . $this->_db->NameQuote( 'rgt' )
				.	"\n GROUP BY a." . $this->_db->NameQuote( 'id' )
				.	"\n ORDER BY a." . $this->_db->NameQuote( 'lft' ) . " ASC";

			$this->_db->setQuery( $query );
			$this->allGroups		=	$this->_db->loadObjectList( 'value' );
		}

		return $this->allGroups;
	}

	/**
	 * Internal method: Loads if needed all View Access Levels, and caches them
	 *
	 * @return string[]  All View Access Levels as array( value => 'translated-text' )
	 */
	protected function loadAllViewAccessLevels( )
	{
		if ( $this->allViewAccessLevels === null ) {
			$query					=	'SELECT a.' . $this->_db->NameQuote( 'id' ) . ' AS value'
				.	', a.' . $this->_db->NameQuote( 'title' ) . ' AS text'
				.	', a.' . $this->_db->NameQuote( 'rules' ) . ' AS assetrules'
				.	"\n FROM " . $this->_db->NameQuote( '#__viewlevels' ) . " AS a"
				.	"\n ORDER BY a." . $this->_db->NameQuote( 'ordering' ) . " ASC";

			$accessLevels		=	$this->_db->setQuery( $query )->loadObjectList( 'value' );

			foreach ( $accessLevels as $level ) {
				$value									=	(int) $level->value;
				$text									=	JText::_( $level->text );
				$this->allViewAccessLevels[$value]		=	$text;
				$this->allViewAccessLevelRules[$value]	=	$level->assetrules;
			}
		}

		return $this->allViewAccessLevels;
	}

	/**
	 * Internal method to clear View Access Levels Cache when CmsPermissionsUpgrader adds View Access Levels
	 * @see CmsPermissionsUpgrader::_convertOldGroupToViewAccessLevel
	 *
	 * @return void
	 */
	protected function clearCacheViewAccessLevels( )
	{
		$this->allViewAccessLevels		=	null;
	}
}
