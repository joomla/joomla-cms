<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/22/14 9:33 PM $
* @package CBLib\Cms\Joomla
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla;

use CBLib\Cms\Joomla\Joomla3\CmsPermissions;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\CmsPermissionsUpgrader Class implementation
 *
 * THIS CLASS IS NOT PART OF THE API AND ONLY FOR INSTALLER USE
 */
abstract class CmsPermissionsUpgrader extends CmsPermissions
{
	/**
	 * Converts old CB 1.x group ids into CB 2.x View Access Level.
	 * If $titleIfCreate is provided, it will create the new View Access Level if none matches precisely the old way.
	 * THIS FUNCTION IS NOT PART OF THE API AND ONLY FOR INSTALLER USE
	 *
	 * @param  CmsPermissions  $cmsPermissions
	 * @param  int          $oldGroupId     Old CB 1.x "User Access Group"
	 * @param  string|null  $titleIfCreate  Title for the new View Access Level to create if none match 100%
	 * @return int                          View Access Level corresponding to the $oldGroupId (or new created level)
	 */
	public static function _convertOldGroupToViewAccessLevel( CmsPermissions $cmsPermissions, $oldGroupId, $titleIfCreate )
	{
		global $_CB_framework;

		$oldGroupId			=	(int) $oldGroupId;

		if ( $oldGroupId == -2 ) {
			// Public:
			$oldGroupId		=	1;		// Joomla root group is the "Public" one, "Guest" is only not logged-in users.
		} elseif ( $oldGroupId == -1 ) {
			// Registered:
			$oldGroupId		=	(int) $_CB_framework->getCfg( 'new_usertype' );
		}

		// Get the 1.x-compatible "parent groups":
		$allOldGroups		=	$_CB_framework->acl->get_group_parent_ids( $oldGroupId );
		sort( $allOldGroups, SORT_NUMERIC );

		if ( count( $allOldGroups ) == 0 ) {
			// Fallback for inexistent groups: Every user has at least Public group (root of groups tree):
			$allOldGroups		=	$cmsPermissions->getGroupTree( 1 );
			sort( $allOldGroups, SORT_NUMERIC );
		}

		// First try to find exactly matching View Access Level:

		foreach ( array_keys( $cmsPermissions->getAllViewAccessLevels() ) as $viewAccessLevel ) {
			$allNewGroups	=	$cmsPermissions->getGroupsOfViewAccessLevel( $viewAccessLevel, true );

			sort( $allNewGroups, SORT_NUMERIC );

			if ( $allNewGroups === $allOldGroups ) {
				return $viewAccessLevel;
			}
		}

		if ( $titleIfCreate ) {
			// If none found, creates a new View Access Level:
			$allOldChildGroups	=	self::getMostChildGroups( $cmsPermissions, $allOldGroups );

			// Get max ordering:
			$query				=	'SELECT MAX(a.' . $cmsPermissions->_db->NameQuote( 'ordering' ) . ')'
				.	"\n FROM " . $cmsPermissions->_db->NameQuote( '#__viewlevels' ) . ' AS a';
			$maxOrdering		=	$cmsPermissions->_db->setQuery( $query )->loadResult();

			// Insert the element:
			$object				=	new \StdClass;
			$object->id			=	null;;
			$object->title		=	$titleIfCreate;
			$object->ordering	=	$maxOrdering + 1;
			$object->rules		=	json_encode( $allOldChildGroups );

			try {
				if ( $cmsPermissions->_db->insertObject( '#__viewlevels', $object, 'id' ) ) {
					$cmsPermissions->clearCacheViewAccessLevels();

					return $object->id;
				}
			}
			catch ( \RuntimeException $e ) {
				try {
					$_CB_framework->enqueueMessage( $e->getMessage() );
				}
				catch ( \Exception $e ) {
					// fall through to return 3 as best guess.
				}
			}
		}

		// Best blind guess: 'Special' default View Access Level
		return 3;
	}

	/**
	 * Gets the minimal set of groups that result in $groups by inheritence of View Access Levels
	 *
	 * @param  CmsPermissions  $cmsPermissions
	 * @param  int[]           $groups          Groups with parent groups
	 * @return int[]                            Groups without parent groups
	 */
	protected static function getMostChildGroups( CmsPermissions $cmsPermissions, $groups )
	{
		$minimalGroups		=	$groups;

		foreach ( $groups as $grp ) {
			$grpWithParents	=	$cmsPermissions->getGroupTree( $grp );

			$minimalGroups	=	array_diff( $minimalGroups, array_diff( $grpWithParents, (array) $grp ) );
		}

		return array_values( $minimalGroups );
	}
}
