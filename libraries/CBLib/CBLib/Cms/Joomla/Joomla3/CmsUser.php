<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 6:15 PM $
* @package CBLib\Cms\Joomla\Joomla3
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Cms\Joomla\Joomla3;

use CBLib\Cms\CmsUserInterface;
use CBLib\Input\Get;
use CBLib\Registry\GetterInterface;
use JAccess;

defined('CBLIB') or die();

/**
 * CBLib\Cms\Joomla\Joomla3\CmsUser Class implementation
 * 
 */
class CmsUser implements CmsUserInterface
{
	/**
	 * CmsUser's instances
	 * @var self[]
	 */
	protected static $cmsUsers;

	/**
	 * User id of logged-in user
	 * @var int|boolean
	 */
	protected static $myId			=	false;

	/**
	 * The corresponding Joomla JUser object
	 *
	 * @var \JUser
	 */
	protected $cmsOwnUser;

	/**
	 * Constructor
	 *
	 * @param  int|string  $userId
	 */
	protected function __construct( $userId )
	{
		if ( $userId ) {
			$this->cmsOwnUser	=	\JUser::getInstance( $userId );
		} else {
			// In Joomla 2.5.20, commit c7c37221 is missing so we need to avoid Joomla caching guest:
			// Joomla bug-fix PR: https://github.com/joomla/joomla-cms/pull/3619
			$this->cmsOwnUser	=	new \JUser();
		}
	}

	/**
	 * Singletons factory
	 *
	 * @param  int|array|null  $userIdOrConditions  [optional] default: NULL: viewing user, int: User-id (0: guest), array: Criteria, e.g. array( 'username' => 'uniqueUsername' ) or array( 'email' => 'uniqueEmail' )
	 * @return self|boolean                         Boolean FALSE if user does not exist (and userId not 0)
	 */
	public static function getInstance( $userIdOrConditions = null )
	{
		if ( $userIdOrConditions === null )
		{
			$userId			=	static::getMyId();
		}
		elseif ( is_array( $userIdOrConditions ) )
		{
			if ( ( count( $userIdOrConditions ) == 1 ) && ( array_keys( $userIdOrConditions ) == array( 'username' ) ) )
			{
				$jUser		=	\JUser::getInstance( $userIdOrConditions['username'] );

				if ( $jUser == false ) {
					return false;
				}
				$userId		=	(int) $jUser->id;
			}
			else
			{
				$ids		=	static::getIds( $userIdOrConditions, null, 0, 2 );

				if ( is_array( $ids ) && ( count( $ids ) == 1 ) )
				{
					$userId	=	(int) array_pop( $ids );
				}
				else
				{
					return false;
				}
			}
		}
		else
		{
			$userId			=	(int) $userIdOrConditions;
		}

		if ( ! isset( static::$cmsUsers[$userId] ) )
		{
			$self			=	new static( $userId );

			if ( $userId == 0 )
			{
				return $self;
			}

			static::$cmsUsers[$userId]	=	$self;
		}

		return static::$cmsUsers[$userId];
	}

	/**
	 * Gets the ids of the users matching $conditions ordered by $ordering from $offset within $limit
	 *
	 * @param  array    $conditions  column => value  pairs OR array( column => array( operator, value ) ) where value int, float, string, or array of int (array will implode & operator = becomes IN), or object cbSqlQueryPart
	 * @param  array    $ordering    column => dir (ASC/DESC)
	 * @param  int      $offset      The offset to start selection
	 * @param  int      $limit       LIMIT statement (0=no limit)
	 * @return mixed
	 */
	public static function getIds( array $conditions, array $ordering = null, $offset = 0, $limit = 0 )
	{
		/** @var \JTableUser $jUserTable */
		$jUserTable	=	\JTable::getInstance( 'user' );
		$properties	=	$jUserTable->getProperties();

		$db			=	\JFactory::getDbo();

		$query		=	$db->getQuery( true )
			->select( $db->quoteName( $jUserTable->getKeyName() ) )
			->from($db->quoteName( $jUserTable->getTableName() ) );

		foreach ( $conditions as $key => $value )
		{
			if ( array_key_exists( $key, $properties ) )
			{
					$query->where($db->quoteName( $key ) . ' = ' . $db->quote( $value ) );
			}
		}

		if ( empty( $ordering ) )
		{
			$ordering	=	array( $jUserTable->getKeyName() => 'ASC' );
		}

		foreach ( $ordering as $orderField => $ascDesc )
		{
			$query->order( $db->quoteName( $orderField ) . ( strtoupper( $ascDesc ) == 'DESC' ? ' DESC' : '' ) );
		}

		$db->setQuery( $query, $offset, $limit );

		return $db->loadColumn();
	}

	/**
	 * Resets the cache for $this cms user
	 *
	 * @return void
	 */
	public function resetCache()
	{
		unset( self::$cmsUsers[$this->cmsOwnUser->id] );

		if ( $this->cmsOwnUser->id == self::$myId ) {
			self::$myId		=	false;
		}
	}

	/**
	 * Gets the user id of the logged-in user (0 if guest)
	 *
	 * @return int  User-id
	 */
	public static function getMyId( )
	{
		if ( self::$myId === false ) {
			self::$myId		=	(int) \JFactory::getUser()->id;
		}

		return self::$myId;
	}

	/**
	 * Get an attribute of the CMS user object
	 *
	 * @param   string|string[]  $key      Name of index or array of names of indexes, each with name or html-input-name-encoded array selection, e.g. a[b][c]
	 * @param   mixed            $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param   string|array     $type     [optional] Default: GetterInterface::RAW. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public function get( $key, $default = null, $type = GetterInterface::RAW )
	{
		return Get::clean( $this->cmsOwnUser->get( $key, $default ), $type );
	}

	/**
	 * Check if a key path exists.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key )
	{
		return in_array( $key, $this->getProperties() );
	}

	/**
	 * Returns an associative array of Cms User properties that can be accessed with get().
	 *
	 * @return array
	 */
	public function getProperties( )
	{
		static $properties	=	null;

		if ( $properties == null ) {
			$properties		=	$this->cmsOwnUser->getProperties();
		}

		return $properties;
	}

	/**
	 * Method to get a parameter value from CMS user object
	 *
	 * @param   string  $key      Parameter key
	 * @param   mixed   $default  Parameter default value
	 * @param   string  $type     [optional] Default: GetterInterface::RAW. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed             The value or the default if it did not exist
	 */
	public function getParam( $key, $default = null, $type = GetterInterface::RAW )
	{
		return Get::clean( $this->cmsOwnUser->getParam( $key, $default ), $type );
	}

	/**
	 * Method to set a parameter
	 *
	 * @param  string  $key    Parameter key
	 * @param  mixed   $value  Parameter value
	 *
	 * @return self            For chaining
	 */
	public function setParam( $key, $value )
	{
		$this->cmsOwnUser->setParam( $key, $value );

		return $this;
	}

	/**
	 * Method to bind an associative array of data to a user object
	 *
	 * @param  array    &$array  The associative array to bind to the object
	 * @return boolean           True on success
	 */
	public function bind( &$array )
	{
		return $this->cmsOwnUser->bind( $array );
	}

	/**
	 * Method to save the JUser object to the database
	 *
	 * @param   boolean  $updateOnly  Save the object only if not a new user
	 *                                Currently only used in the user reset password method.
	 * @return  boolean  True on success
	 *
	 * @throws  \RuntimeException
	 */
	public function save( $updateOnly = false )
	{
		return $this->cmsOwnUser->save( $updateOnly );
	}

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
	public function isAuthorizedToPerformActionOnAsset( $action, $asset )
	{
		if ( $asset == 'root' ) {
			$asset	=	null;
		}
		return $this->cmsOwnUser->authorise( $action, $asset );
	}

	/**
	 * Checks if this entity can view a given access level
	 *
	 * @param $accessLevel
	 * @return boolean         True: Can view, False: Can not view
	 */
	public function canViewAccessLevel( $accessLevel )
	{
		return in_array( (int) $accessLevel, $this->getAuthorisedViewLevels() );
	}

	/**
	 * Gets an array of the authorised user-groups for this entity
	 *
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array
	 */
	public function getAuthorisedGroups( $inheritedOnesToo = true )
	{
		if ( $inheritedOnesToo ) {
			return $this->cmsOwnUser->getAuthorisedGroups( $inheritedOnesToo );
		} else {
			return JAccess::getGroupsByUser( $this->cmsOwnUser->id, false );
		}
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return int[]
	 */
	public function getAuthorisedViewLevels( )
	{
		return array_unique( Get::arrayToIntegers( $this->cmsOwnUser->getAuthorisedViewLevels() ) );
	}
}
