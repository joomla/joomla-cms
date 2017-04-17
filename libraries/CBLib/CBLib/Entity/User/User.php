<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/17/14 5:32 PM $
* @package CBLib\Entity\User
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Entity\User;

use CBLib\Application\Config;
use CBLib\Cms\CmsInterface;
use CBLib\Cms\CmsUserInterface;
use CBLib\Entity\AuthoriseInterface;
use CBLib\Entity\EntityInterface;
use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

/**
 * CBLib\Entity\User\User Class implementation
 * 
 */
class User implements EntityInterface, AuthoriseInterface, GetterInterface
{
	/**
	 * Cached users
	 * @var self[]
	 */
	protected static $usersCache;

	/**
	 * User id of logged-in user
	 * @var int|boolean
	 */
	protected static $myId			=	false;

	/**
	 * User id
	 * @var int
	 */
	protected $id;

	/**
	 * Our own CMS User object driving the Cms User
	 * @var CmsUserInterface
	 */
	protected $cmsUser;

	/**
	 * @var int
	 */
	protected $moderatorViewAccessLevel;

	/**
	 * Constructor (Not to be called directly: Use Application::MyUser() or Application::User( (int) $userId )
	 * @see Application::MyUser()
	 *
	 * @param  int               $userId
	 * @param  CmsUserInterface  $cmsUser
	 * @param  Config            $config
	 */
	protected function __construct( $userId, CmsUserInterface $cmsUser, Config $config )
	{
		$this->id							=	(int) $userId;
		$this->cmsUser						=	$cmsUser;
		$this->moderatorViewAccessLevel		=	$config->get( 'moderator_viewaccesslevel', 3, GetterInterface::INT );
	}

	/**
	 * Gets the User object corresponding to user id
	 *
	 * This is ONLY for the Application Container You need to use instead:
	 * Application::MyUser() or Application::User( $idOrCondition ) only
	 * @see \CBLib\Application\Application::MyUser()
	 *
	 * @param  int|array|null  $idOrConditions  [optional] default: NULL: viewing user, int: User-id (0: guest), array: Criteria, e.g. array( 'username' => 'uniqueUsername' ) or array( 'email' => 'uniqueEmail' )
	 * @param  CmsInterface    $cms             Cms object
	 * @param  Config          $config          Config
	 * @return User                             User: if exists (registered user: singleton, guest: new instance), Guest user new instance if user not found
	 */
	public static function getInstanceForContainerOnly( $idOrConditions = null, CmsInterface $cms, Config $config )
	{
		$cmsUser				=	null;

		if ( $idOrConditions === null )
		{
			// Get viewing user:
			if ( self::$myId === false )
			{
				$cmsUser		=	$cms->getCmsUser( null );
				self::$myId		=	(int) $cmsUser->get( 'id' );
			}

			$idOrConditions		=	self::$myId;
		}
		elseif ( is_string( $idOrConditions ) && ! is_numeric( $idOrConditions ) )
		{
			// Not a numeric $userIdOrAspect: Try getting the cmsUser by username:
			$cmsUser			=	$cms->getCmsUser( $idOrConditions );

			if ( ! $cmsUser )
			{
				// No corresponding CMS user exists: Return a guest User instead:
				return new self( 0, $cms->getCmsUser( 0 ), $config );
			}

			$idOrConditions	=	(int) $cmsUser->get( 'id' );
		}

		$key					=	(string) ( (int) $idOrConditions );

		if ( ! isset( self::$usersCache[$key] ) )
		{
			if ( ! $cmsUser )
			{
				$cmsUser		=	$cms->getCmsUser( $idOrConditions );
			}

			if ( $cmsUser )
			{
				$self			=	new self( $idOrConditions, $cmsUser, $config );

				if ( (int) $idOrConditions === 0 )
				{
					// No cache for guest/new user:
					return $self;
				}

				self::$usersCache[$key]		=	$self;
			}
		}

		return self::$usersCache[$key];
	}

	/**
	 * Resets the cache for $this user
	 *
	 * @return void
	 */
	public function resetCache()
	{
		$this->cmsUser->resetCache();
		unset( self::$usersCache[$this->id] );

		if ( $this->id == self::$myId )
		{
			self::$myId		=	false;
		}
	}

	/**
	 * Gets a parameter of $this->cmsUser user
	 *
	 * @param   string|string[]        $key      Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param   mixed|GetterInterface  $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param   string|array           $type     [optional] Default: null: GetterInterface::COMMAND. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return  mixed
	 *
	 * @throws \Exception
	 */
	public function get( $key, $default = null, $type = null )
	{
		return $this->cmsUser->get( $key, $default, $type );
	}

	/**
	 * Checks a parameter of $this->cmsUser user
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key )
	{
		return $this->cmsUser->has( $key );
	}

	/**
	 * Gets the user id of $this user (0 if guest)
	 *
	 * @return int  User-id
	 */
	public function getUserId( )
	{
		return $this->id;
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
		return $this->cmsUser->isAuthorizedToPerformActionOnAsset( $action, $asset );
	}

	/**
	 * Checks if this entity can view a given access level (or is a super-user who can view everything)
	 *
	 * @param  int      $accessLevel               Access-level to check
	 * @param  boolean  $authoriseAlsoIfSuperUser  [optional default true] authorises also if user is super-user
	 * @return boolean                             True: Can view, False: Can not view
	 */
	public function canViewAccessLevel( $accessLevel, $authoriseAlsoIfSuperUser = true )
	{
		return $this->cmsUser->canViewAccessLevel( $accessLevel )
			   || ( $authoriseAlsoIfSuperUser && $this->isSuperAdmin() );
	}

	/**
	 * Gets an array of the authorised user-groups for this entity
	 *
	 * @param  boolean  $inheritedOnesToo  True to include inherited user groups.
	 * @return array
	 */
	public function getAuthorisedGroups( $inheritedOnesToo = true )
	{
		return $this->cmsUser->getAuthorisedGroups( $inheritedOnesToo );
	}

	/**
	 * Gets an array of the authorised access levels for the user
	 *
	 * @return int[]
	 */
	public function getAuthorisedViewLevels( )
	{
		return $this->cmsUser->getAuthorisedViewLevels();
	}

	/**
	 * Checks if $this user is Super-Admin
	 *
	 * @return boolean  True: Yes, False: No
	 */
	public function isSuperAdmin( )
	{
		return $this->isAuthorizedToPerformActionOnAsset( 'core.admin', 'root' );
	}

	/**
	 * Checks if $this user is Moderator for $entity or Super-administrator
	 * Right now this implementation is relying on global moderator status,
	 * but in future it might be on a per-user or per-group basis.
	 *
	 * @param  AuthoriseInterface  $entity  User Id to moderate
	 * @return boolean                      True: Yes, False: No
	 */
	public function isModeratorFor( AuthoriseInterface $entity )
	{
		// Checks if $this is authorized AND ( it is for himself OR the other $entity is not moderator )
		// OR $this is a super-admin:
		return ( ( $this->canViewAccessLevel( $this->moderatorViewAccessLevel )
				   && ( ( $entity instanceof self && ( $this->getUserId() == $entity->getUserId() ) )
						|| ! $entity->canViewAccessLevel( $this->moderatorViewAccessLevel )
					  )
				 )
				 || $this->isSuperAdmin()
			   );
	}

	/**
	 * Checks if $this user is a Global Moderator for the site or Super-administrator
	 *
	 * @return boolean                      True: Yes, False: No
	 */
	public function isGlobalModerator( )
	{
		return ( $this->canViewAccessLevel( $this->moderatorViewAccessLevel ) );
	}
}
