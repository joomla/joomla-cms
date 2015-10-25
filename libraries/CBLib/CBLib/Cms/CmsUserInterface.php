<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 6:40 PM $
* @package CBLib\Cms\Joomla
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


namespace CBLib\Cms;

use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

interface CmsUserInterface extends GetterInterface
{
	/**
	 * Singletons factory
	 *
	 * @param  int|array|null  $userIdOrCriteria  [optional] default: NULL: viewing user, int: User-id (0: guest), array: Criteria, e.g. array( 'username' => 'uniqueUsername' ) or array( 'email' => 'uniqueEmail' )
	 * @return self|boolean                       Boolean FALSE if user does not exist (and userId not 0)
	 */
	public static function getInstance( $userIdOrCriteria = null );

	/**
	 * Resets the cache for $this cms user
	 *
	 * @return void
	 */
	public function resetCache();

	/**
	 * Gets the user id of the logged-in user (0 if guest)
	 *
	 * @return int  User-id
	 */
	public static function getMyId( );

	/**
	 * Returns an associative array of Cms User properties that can be accessed with get().
	 *
	 * @return array
	 */
	public function getProperties( );

	/**
	 * Method to get a parameter value from CMS user object
	 *
	 * @param   string  $key      Parameter key
	 * @param   mixed   $default  Parameter default value
	 * @param   string  $type     [optional] Default: GetterInterface::RAW. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed             The value or the default if it did not exist
	 */
	public function getParam( $key, $default = null, $type = GetterInterface::RAW );

	/**
	 * Method to set a parameter
	 *
	 * @param  string  $key    Parameter key
	 * @param  mixed   $value  Parameter value
	 *
	 * @return self            For chaining
	 */
	public function setParam( $key, $value );

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param  array    &$array  The associative array to bind to the object
	 * @return boolean           True on success
	 */
	public function bind( &$array );

	/**
	 * Method to save the JUser object to the database
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new user
	 *                                Currently only used in the user reset password method.
	 * @return  boolean  True on success
	 *
	 * @throws  \RuntimeException
	 */
	public function save( $updateOnly = false );

	/**
	 *
	 * PERMISSIONS and ACCESS LEVELS
	 *
	 */

	/**
	 * Checks if this entity is authorized to perform $task on $asset
	 *
	 * @param  string  $action  Action to perform on $asset
	 * @param  string  $asset   Asset to perform $task on
	 * @return boolean          True: Authorized, False: Not authorized
	 */
	public function isAuthorizedToPerformActionOnAsset( $action, $asset );

	/**
	 * Checks if this entity can view a given access level
	 *
	 * @param $accessLevel
	 * @return boolean         True: Can view, False: Can not view
	 */
	public function canViewAccessLevel( $accessLevel );

	/**
	 * Gets an array of the authorised user-groups for this entity
	 *
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array
	 */
	public function getAuthorisedGroups( $inheritedOnesToo = true );

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return int[]
	 */
	public function getAuthorisedViewLevels( );
}
