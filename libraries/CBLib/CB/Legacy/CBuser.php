<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/16/14 4:46 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Language\CBTxt;
use CBLib\Registry\GetterInterface;
use CB\Database\Table\UserTable;

defined('CBLIB') or die();

/**
 * CBuser Class implementation
 * Lightweight CB user class
 *
 */
class CBuser
{
	/**
	 * CB user object for database tables
	 * (needs to be public for backwards compatibility)
	 * @var UserTable
	 */
	public $_cbuser;

	/**
	 * the CB tabs object for that user
	 * (needs to be public for backwards compatibility)
	 * @var cbTabs
	 */
	public $_cbtabs	=	null;

	/**
	 * Database
	 * @var DatabaseDriverInterface
	 */
	protected $_db;

	/**
	 * For function advanceNoticeOfUsersNeeded( $usersIds )
	 * @var array of int  id to load at next needed SQL query
	 */
	private static $idsToLoad						=	array();

	/**
	 * Constructor
	 */
	public function __construct( )
	{
		global $_CB_database;

		$this->_db			=	$_CB_database;
	}

	/**
	 * returns the current CBuser instance by reference for myId
	 * @since 1.8
	 *
	 * @return CBuser
	 */
	public static function getMyInstance()
	{
		global $_CB_framework;

		return self::getInstance( (int) $_CB_framework->myId(), false );
	}

	/**
	 * returns CBuser instance by reference for the specificed user id or a new instance if user id is 0
	 *
	 * @param  int|null     $userId     User id
	 * @param  boolean      $allowNull  Forces return of CBuser even if no match found
	 * @return CBuser|null              Returns null if id is specified, but not loaded or empty CBuser if $allowNull is false
	 */
	public static function & getInstance( $userId, $allowNull = true )
	{
		if ( $userId !== null ) {
			$userId		=	(int) $userId;
		}

		$user			=& CBuser::_getOrSetInstance( $userId );

		if ( ( $user === null ) && ( ! $allowNull ) ) {
			$userId		=	null;

			$user		=& CBuser::_getOrSetInstance( $userId );
		}

		return $user;
	}

	/**
	 * returns UserTable instance by reference for myId
	 * @since 1.8
	 *
	 * @return UserTable
	 */
	public static function getMyUserDataInstance()
	{
		global $_CB_framework;

		return self::getUserDataInstance( (int) $_CB_framework->myId() );
	}

	/**
	 * returns UserTable instance by reference for the specificed user id or a new instance if user id is 0
	 * @since 1.2.3
	 *
	 * @param  int        $userId
	 * @return UserTable
	 */
	public static function & getUserDataInstance( $userId )
	{
		$cbUser		=	CBuser::getInstance( (int) $userId, false );

		return $cbUser->getUserData();
	}

	/**
	 * Creates and sets a new instance of CBuser to $user
	 *
	 * @param  UserTable  $user
	 * @return CBuser
	 */
	public static function & setUserGetCBUserInstance( & $user )
	{
		if ( is_object( $user ) ) {
			return CBuser::_getOrSetInstance( $user );
		} else {
			trigger_error( 'CBUser::setUserGetCBUserInstance called without object', E_USER_ERROR );
			$null				=	null;
			return $null;
		}
	}

	/**
	 * Private storage holder of the instances of CBuser
	 *
	 * @param  int|int[]|UserTable|null   $userOrValidId
	 * @return CBuser|null
	 */
	private static function & _getOrSetInstance( & $userOrValidId )
	{
		/** @var CBuser[] $instances */
		static $instances							=	array();

		if ( is_int( $userOrValidId ) && ( $userOrValidId !== 0 ) ) {
			if ( ! isset( $instances[$userOrValidId] ) ) {
				cbimport( 'cb.tabs' );
				if ( count( self::$idsToLoad ) == 0 ) {
					$instances[$userOrValidId]		=	new CBuser();
					if ( ! $instances[$userOrValidId]->load( $userOrValidId ) ) {
						unset( $instances[$userOrValidId] );
						$null						=	null;
						return $null;
					}
				} else {
					// Add user to load to list if not already there:
					self::advanceNoticeOfUsersNeeded( array( $userOrValidId ) );
					// Loads all users from list:
					self::loadUsersMatchingIdIntoList( self::$idsToLoad, $instances );
					self::$idsToLoad				=	array();
					if ( ! isset( $instances[$userOrValidId] ) ) {
						$null						=	null;
						return $null;
					}
				}
			}
			return $instances[$userOrValidId];
		} elseif ( is_object( $userOrValidId ) && isset( $userOrValidId->id ) && $userOrValidId->id ) {
			// overwrite on purpose previous cached user, if any:
			$instances[(int) $userOrValidId->id]	=	new CBuser();
			$instances[(int) $userOrValidId->id]->loadCbRow( $userOrValidId );
			return $instances[(int) $userOrValidId->id];
		} elseif ( is_array( $userOrValidId ) ) {
			// Clear cache internal function to free memory in heavy tasks:
			foreach ( $userOrValidId as $id ) {
				unset( $instances[(int) $id]->_cbuser );
				unset( $instances[(int) $id]->_cbtabs );
				unset( $instances[(int) $id] );
			}
			$null	=	null;
			return $null;
		} else {
			cbimport( 'cb.tabs' );
			$cbUser									=	new CBuser();
			$cbUser->_cbuser						=	new UserTable( $cbUser->_db );
			return $cbUser;
		}
	}

	/**
	 * Loads from database a new user of $cbUserId
	 *
	 * @param  int      $cbUserId  User id
	 * @return boolean  True: loaded ok, False:load failed
	 */
	function load( $cbUserId )
	{
		$this->_cbuser		=	new UserTable( $this->_db );
		return  $this->_cbuser->load( $cbUserId );
	}

	/**
	 * Loads a list of UserTable into an existing array if they are not already in it
	 * (indexed by key of this table)
	 * @since 1.4
	 *
	 * @param  array    $usersIds      array of id to load
	 * @param  array    $objectsArray  IN/OUT   (int) id => UserTable users
	 */
	private static function loadUsersMatchingIdIntoList( $usersIds, &$objectsArray )
	{
		$cbUser									=	new CBuser();
		$cbUser->_cbuser						=	new UserTable( $cbUser->_db );
		$cbUser->_cbuser->loadUsersMatchingIdIntoList( $usersIds, $objectsArray, 'CBuser' );
	}

	/**
	 * Copy the named array or object content into this object as vars
	 * All $arr values are filled in vars of $this->_cbuser
	 *
	 * @access private this is just to be usable by UserTable::loadUsersMatchingIdIntoList()
	 *
	 * @param  array               $arr    The input array
	 */
	public function bindThisUserFromDbArray( $arr )
	{
		$this->_cbuser							=	new UserTable( $this->_db );
		$this->_cbuser->bindThisUserFromDbArray( $arr );
	}

	/**
	 * Sets an additional list of user records to also load and cache with next SQL query
	 * e.g.:
	 * CBuser::advanceNoticeOfUsersNeeded( array( 66, 67, 65 ) );		// just remembers
	 * CBuser::advanceNoticeOfUsersNeeded( array( 64, 65 ) );			// just remembers
	 * echo CBuser::getUserDataInstance( 64 )->id;		// echo's 64	// and loads 64-67
	 * CBuser::advanceNoticeOfUsersNeeded( array( 68, 67, 69, 71 ) );	// just remembers
	 * echo CBuser::getUserDataInstance( 67 )->id;		// echos 67		// and doesn't load
	 * echo CBuser::getUserDataInstance( 69 )->username;	// echos	// and loads 68,69,71
	 *
	 * @param  int[] $usersIds
	 * @return void
	 */
	public static function advanceNoticeOfUsersNeeded( $usersIds )
	{
		self::$idsToLoad	=	array_unique( array_merge( self::$idsToLoad, $usersIds ) );
	}

	/**
	 * Unsets users in cache, clearing in the cache
	 *
	 * @param  int[]  $usersIds
	 * @return void
	 */
	public static function unsetUsersNotNeeded( $usersIds )
	{
		self::_getOrSetInstance( $usersIds );
	}

	/**
	 * Loads the CBuser with the $row
	 *
	 * @param  UserTable  $row  UserTable data object
	 * @return void
	 */
	public function loadCbRow( &$row )
	{
		$this->_cbuser	=&	$row;
	}

	/**
	 * Returns the User's profile data
	 *
	 * @return UserTable
	 */
	public function & getUserData( )
	{
		return $this->_cbuser;
	}

	/**
	 * Creates if needed cbTabs object
	 *
	 * @param  boolean  $outputTabpaneScript
	 * @return cbTabs
	 */
	public function & _getCbTabs( $outputTabpaneScript = true )
	{
		if ( $this->_cbtabs === null ) {
			global $_CB_framework;

			cbimport('cb.tabs');

			$this->_cbtabs	=	new cbTabs( 0, $_CB_framework->getUi(), null, $outputTabpaneScript );
		}

		if ( $outputTabpaneScript ) {
			$this->_cbtabs->outputTabJS();
		}

		return $this->_cbtabs;
	}

	/**
	 * Formatter:
	 * Returns a field in specified format
	 *
	 * @param  string                $fieldName     Name of field to render
	 * @param  mixed                 $defaultValue  Value if field is not in reach of viewer user or innexistant
	 * @param  string                $output        'html', 'xml', 'json', 'php', 'csvheader', 'csv', 'rss', 'fieldslist', 'htmledit'
	 * @param  string                $formatting    'tr', 'td', 'div', 'span', 'none',   'table'??
	 * @param  string                $reason        'profile' for user profile view and edit, 'register' for registration, 'search' for searches
	 * @param  int                   $list_compare_types   IF reason == 'search' : 0 : simple 'is' search, 1 : advanced search with modes, 2 : simple 'any' search
	 * @param  boolean               $fullAccess    IF true do not take in account current user's viewing rights
	 * @param  null|array            $fieldVariables    parses an array of variables into field variables to be sent with the field
	 * @return mixed
	 */
	public function getField( $fieldName, $defaultValue = null, $output = 'html', $formatting = 'none', $reason = 'profile', $list_compare_types = 0, $fullAccess = false, $fieldVariables = array() )
	{
		global $_CB_framework, $_PLUGINS;

		$tabs			=&	$this->_getCbTabs( false );
		$fields			=	$tabs->_getTabFieldsDb( null, $this->getMyUserDataInstance( $_CB_framework->myId() ), $reason, $fieldName, true, $fullAccess );
		if ( isset( $fields[0] ) ) {
			$field		=	$fields[0];
			if ( $fieldVariables ) foreach ( $fieldVariables as $name => $value ) {
				$field->set( $name, $value );
			}
			$value		=	$_PLUGINS->callField( $field->type, 'getFieldRow', array( &$field, &$this->_cbuser, $output, $formatting, $reason, $list_compare_types ), $field );
		} else {
			$value		=	$defaultValue;
		}
		return $value;
	}

	/**
	 * Gets the rendering of a $position of $this user profile
	 *
	 * @param  string       $position  CB userprofile layout-position
	 * @return string|null             HTML or null if $position does not exist
	 */
	public function getPosition( $position )
	{
		$userViewTabs	=	$this->getProfileView( $position );
		if ( isset( $userViewTabs[$position] ) ) {
			return $userViewTabs[$position];
		} else {
			return null;
		}
	}

	/**
	 * Gets the rendered $tab
	 *
	 * @param  int          $tab           Tab id to render
	 * @param  string       $defaultValue  Default HTML to render if the tab does not render
	 * @param  string       $output        [optional default='html'] Output format
	 * @param  string       $formatting    [optional] Formatting
	 * @param  string       $reason        View of tab (default: 'profile')
	 * @return string|null                 HTML or null if $position does not exist
	 */
	public function getTab( $tab, $defaultValue = null, $output = 'html', $formatting = null, $reason = 'profile' )
	{
		$tabs			=&	$this->_getCbTabs();
		$tabs->generateViewTabsContent( $this->_cbuser, '', $tab, $output, $formatting, $reason );
		return $tabs->getProfileTabHtml( $tab, $defaultValue );
	}

	/**
	 * Gets html code for all cb tabs, sorted by position (default: all, no position name in db means "cb_tabmain")
	 *
	 * @param  string     $position  Name of position if only one position to display (default: null)
	 * @return array                 Array of string with html to display at each position, key = position name, or NULL if position is empty.
	 */
	public function getProfileView( $position = '' )
	{
		$tabs			=&	$this->_getCbTabs();
		return $tabs->getViewTabs( $this->_cbuser, $position );
	}

	/**
	 * DO NOT USE: This function will disapear in favor of a new one in very next minor release.
	 * you should use
	 * CBuser->getField( 'avatar' , null, 'csv', 'none', 'list' );
	 * instead of this derpreciated call !
	 *
	 * @deprecated 1.9	CBuser->getField( 'avatar' , null, 'csv', 'none', 'list' );  	//TODO Unused now: Remove in CB 2.0 RC
	 *
	 * @param  int          $showAvatar  2: Return default avatar if no avatar defined
	 * @return string|null
	 */
	public function avatarFilePath( $showAvatar = 2 )
	{
		global $_CB_framework, $_CB_database;

		$liveSite							=	$_CB_framework->getCfg( 'live_site' );
		$absolutePath						=	$_CB_framework->getCfg( 'absolute_path' );

		$return								=	null;

		if ( $this->_cbuser && $this->_cbuser->id ) {
			$query							=	'SELECT *'
				.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_fields' )
				.	"\n WHERE " . $_CB_database->NameQuote( 'name' ). " = " . $_CB_database->Quote( 'avatar' );
			$_CB_database->setQuery( $query );
			$field							=	new \CB\Database\Table\FieldTable();
			$_CB_database->loadObject( $field );

			$field->params					=	new \CBLib\Registry\Registry( $field->params );

			$value							=	$this->_cbuser->avatar;
			$approvedValue					=	$this->_cbuser->avatarapproved;

			if ( ( $value != '' ) && ( ( $approvedValue > 0 ) || ( $showAvatar == 10 ) ) ) {
				if ( strpos( $value, 'gallery/' ) === false ) {
					$return					=	'/images/comprofiler/tn' . $value;
				} else {
					$galleryPath			=	$field->params->get( 'image_gallery_path', null );

					if ( ! $galleryPath ) {
						$galleryPath		=	'/images/comprofiler/gallery';
					}

					$return					=	$galleryPath . '/' . preg_replace( '!^gallery/(tn)?!', 'tn', $value );

					if ( ! is_file( $absolutePath . $return ) ) {
						$return				=	$galleryPath . '/' . preg_replace( '!^gallery/!', '', $value );
					}
				}

				if ( ! is_file( $absolutePath . $return ) ) {
					$return					=	null;
				}
			}

			if ( ( $return === null ) && ( $showAvatar == 2 ) ) {
				$imagesBase					=	'avatar';

				if ( $field->name == 'canvas' ) {
					$imagesBase				=	'canvas';
				}

				if ( $approvedValue == 0 ) {
					$icon					=	$field->params->get( 'pendingDefaultAvatar', null );

					if ( $icon == 'none' ) {
						return null;
					} elseif ( $icon ) {
						if ( ( $icon != 'pending_n.png' ) && ( ! is_file( selectTemplate( 'absolute_path' ) . '/images/' . $imagesBase . '/tn' . $icon ) ) ) {
							$icon			=	null;
						}
					}

					if ( ! $icon ) {
						$icon				=	'pending_n.png';
					}
				} else {
					$icon					=	$field->params->get( 'defaultAvatar', null );

					if ( $icon == 'none' ) {
						return null;
					} elseif ( $icon ) {
						if ( ( $icon != 'nophoto_n.png' ) && ( ! is_file( selectTemplate( 'absolute_path' ) . '/images/' . $imagesBase . '/tn' . $icon ) ) ) {
							$icon			=	null;
						}
					}

					if ( ! $icon ) {
						$icon				=	'nophoto_n.png';
					}
				}

				return selectTemplate() . 'images/' . $imagesBase . '/tn' . $icon;
			}
		}

		if ( $return ) {
			$return							=	$liveSite . $return;
		}

		return $return;
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
	 * $assetname:
	 * 'com_comprofiler' (default) : For all CB aspects except user management
	 * 'com_users'                 : For all user management aspects (except core.manage, left for deactivating core Joomla User)
	 * null                        : For global super-user rights check: ( 'core.admin', null )
	 *
	 * @since 1.8
	 * @deprecated 2.0 use Application::MyUser()->isAuthorizedToPerformActionOnAsset( $action, $assetname ) or Application::User( (int) $id )->isAuthorizedToPerformActionOnAsset( $action, $assetname );
	 * @see User::isAuthorizedToPerformActionOnAsset()
	 *
	 * @param  string  $action     Action to perform: core.admin, core.manage, core.create, core.delete, core.edit, core.edit.state, core.edit.own, ...
	 * @param  string  $assetname  OPTIONAL: asset name e.g. "com_comprofiler.plugin.$pluginId" or "com_users", or null for global rights
	 * @return boolean             True: Authorised, False: Not authorized
	 */
	public function authoriseAction( $action, $assetname = 'com_comprofiler' )
	{
		global $_PLUGINS;

		$authorization				=	Application::User( (int) $this->_cbuser->id )->isAuthorizedToPerformActionOnAsset( $action, $assetname );

		if ( $_PLUGINS ) {
			// First backend authorization is done before loading plugins:
			$_PLUGINS->trigger( 'onAfterAuthorizeAction', array( $action, $assetname, &$authorization ) );
		}
		return $authorization;
	}

	/**
	 * Check authorization to view for $contentType for content $id
	 *
	 * @since 1.8 (experimental)
	 *
	 * @param  string  $contentType  Content-type: 'profile'
	 * @param  int     $id           Content id
	 * @return boolean               True if authorized, False if not authorized
	 */
	public function authoriseView( $contentType, $id )
	{
		global $_PLUGINS;

		$authorization		=	false;

		if ( $contentType == 'profile' ) {
			// Check for profile View Access Level (new in CB 2.0):
			$viewAccessLevel	=	Application::Config()->get( 'profile_viewaccesslevel', 0, GetterInterface::INT  );

			if ( $viewAccessLevel == 0 ) {
				// No configuration here: Check for old config (safely defaulting to Special View access level) until config is re-saved:
				$oldGID			=	Application::Config()->get( 'allow_profileviewbyGID', 3, GetterInterface::INT );
				$mapping		=	array( -2 => 1, -1 => 2, 0 => 1, 1 => 1, 2 => 2, 3 => 3 );
				if ( in_array( $oldGID, $mapping ) ) {
					$viewAccessLevel	=	$mapping[$oldGID];
				} else {
					// Default here is "Special" Access level until configuration is saved:
					$viewAccessLevel	=	3;
				}
			}

			$authorization	=	Application::MyUser()->canViewAccessLevel( $viewAccessLevel );
		}

		if ( $contentType == 'userslist' ) {
			$authorization	=	Application::MyUser()->canViewAccessLevel( cbUsersList::getInstance( $id )->viewaccesslevel );
		}

		if ( $_PLUGINS ) {
			$_PLUGINS->trigger( 'onAfterAuthorizeView', array( $contentType, $id, &$authorization ) );
		}
		return $authorization;
	}

	/**
	 * Get authorized Access Levels (STRICTLY int and STRICTLY unique ids) for this user
	 *
	 * @since 1.8
	 * @deprecated 2.0 use Application::MyUser()->getAuthorisedViewLevels() or Application::User( (int) $userId )->getAuthorisedViewLevels();
	 * @see User::getAuthorisedViewLevels()
	 *
	 * @return array of int              STRICTLY int and STRICTLY unique ids
	 */
	public function getAuthorisedViewLevelsIds( )
	{
		return Application::MyUser()->getAuthorisedViewLevels();
	}

	/**
	 * Replaces [fieldname] by the content of the user row (except for [password])
	 *
	 * @param  string         $msg
	 * @param  boolean|array  $htmlspecialchars  on replaced values only: FALSE : no htmlspecialchars, TRUE: do htmlspecialchars, ARRAY: callback method
	 * @param  boolean        $menuStats
	 * @param  array          $extraStrings
	 * @param  boolean|int    $translateLanguage  on $msg only
	 * @return string
	 */
	public function replaceUserVars( $msg, $htmlspecialchars = true, $menuStats = true, $extraStrings = null, $translateLanguage = true )
	{
		if ( $extraStrings === null ) {
			$extraStrings	=	array();
		}

		if ( $translateLanguage === true ) {
			$msg			=	$htmlspecialchars ? CBTxt::Th( $msg ) : CBTxt::T( $msg );
		}

		if ( strpos( $msg, '[' ) === false ) {
			return $msg;
		}

		$msg				=	$this->_evaluateIfs( $msg, $extraStrings );
		$msg				=	$this->_evaluateCbTags( $msg );
		$msg				=	$this->_evaluateCbFields( $msg, $htmlspecialchars );

		foreach( $extraStrings AS $k => $v ) {
			if( ( ! is_object( $v ) ) && ( ! is_array( $v ) ) ) {
				if ( is_array( $htmlspecialchars ) ) {
					$v		=	call_user_func_array( $htmlspecialchars, array( $v ) );
				}
				$msg		=	cbstr_ireplace("[".$k."]", $htmlspecialchars === true ? htmlspecialchars( $v ) : $v, $msg );
			}
		}
		if ( $menuStats ) {
			// find [menu .... : path1:path2:path3 /] and replace with HTML code if menu active, otherwise remove it all
			$msg = $this->_replacePragma( $msg, 'menu', 'menuBar' );
			// no more [status ] as they are standard fields !		$msg = $this->_replacePragma( $msg, $row, 'status', 'menuList' );
		}
		$msg = str_replace( array( "&91;", "&93;" ), array( "[", "]" ), $msg );
		return $msg;
	}

	/**
	 * INTERNAL PRIVATE METHODS:
	 */

	/**
	 * Explodes a text like: href="text1" img="text'it" alt='alt"joe'   into an array with defined keys and values, but null for missing ones.
	 * @access private
	 *
	 * @param  string    $text	     Text to parse
	 * @param  string[]  $validTags	 Valid tag names
	 * @return array                 Array( "tagname" => "tagvalue", "notsetTagname" => null)
	 */
	private function _explodeTags( $text, $validTags )
	{
		$text = trim($text);
		$result = array();
		foreach ($validTags as $tagName) {
			$result[$tagName] = null;
		}
		while ( $text != "" ) {
			$posEqual = strpos( $text, "=" );
			if ( $posEqual !== false ) {
				$tagName	= trim( substr( $text, 0, $posEqual ) );
				$text		= trim( substr( $text, $posEqual + 1 ) );
				$quoteMark	= substr( $text, 0, 1);
				$posEndQuote	= strpos( $text, $quoteMark, 1 );

				if ( ($posEndQuote !== false) && in_array( $quoteMark, array( "'", '"' ) ) ) {
					$tagValue	= substr( $text, 1, $posEndQuote - 1 );
					$text		= trim( substr( $text, $posEndQuote + 1 ) );
					if ( in_array( $tagName, $validTags ) ) {
						$result[$tagName] = $tagValue;
					}
				} else {
					break;
				}
			} else {
				break;
			}
		}
		return $result;
	}

	/**
	 * Replaces "$1" in $text with $cbMenuTagsArray[$cbMenuTagsArrayKey] if non-null but doesn't tag if empty
	 * otherwise replace by $cbMenu[$cbMenuKey] if set and non-empty
	 * @access private
	 *
	 * @param  string[]  $cbMenuTagsArray
	 * @param  string    $cbMenuTagsArrayKey
	 * @param  string[]  $cbMenu
	 * @param  string    $cbMenuKey
	 * @param  string    $text
	 * @return string
	 */
	private function _placeTags( $cbMenuTagsArray, $cbMenuTagsArrayKey, $cbMenu, $cbMenuKey, $text )
	{
		if ( $cbMenuTagsArray[$cbMenuTagsArrayKey] !== null) {
			if ( $cbMenuTagsArray[$cbMenuTagsArrayKey] != "" ) {
				return str_replace( '$1', /*allow tags! htmlspecialchars */ ( $cbMenuTagsArray[$cbMenuTagsArrayKey] ), $text );
			} else {
				return null;
			}
		} elseif ( isset($cbMenu[$cbMenuKey]) && ( $cbMenu[$cbMenuKey] !== null ) && ( $cbMenu[$cbMenuKey] !== "" ) ) {
			return str_replace( '$1', $cbMenu[$cbMenuKey], $text );
		} else {
			return null;
		}
	}

	/**
	 * Replaces complex pragmas
	 *
	 * @param  string    $msg
	 * @param  string    $pragma           the tag between the brackets "[$pragma]"
	 * @param  string    $position       the CB menu position
	 * @param  boolean   $htmlspecialcharsEncoded  True if menu tags should remain htmlspecialchared
	 * @return string
	 */
	private function _replacePragma( $msg, $pragma, $position, $htmlspecialcharsEncoded = true )
	{
		global $_PLUGINS;

		$msgResult = "";
		$pragmaLen = strlen( $pragma );
		while ( ( $foundPosBegin = strpos( $msg, "[" . $pragma ) ) !== false ) {
			$foundPosEnd = strpos( $msg, "[/" . $pragma . "]", $foundPosBegin + $pragmaLen + 1 );
			if ( $foundPosEnd !== false ) {
				$foundPosTagEnd = strpos( $msg, "]", $foundPosBegin + $pragmaLen + 1 );
				if ( ( $foundPosTagEnd !== false ) && ( $foundPosTagEnd < $foundPosEnd ) ) {
					// found [menu .... : $cbMenuTreePath /] : check to see if $cbMenuTreePath is in current menu:
					$cbMenuTreePath = substr( $msg, $foundPosTagEnd + 1, $foundPosEnd - ($foundPosTagEnd + 1) );
					$cbMenuTreePathArray = explode( ":", $cbMenuTreePath );
					$pm = $_PLUGINS->getMenus();
					$pmc=count($pm);
					for ( $i=0; $i<$pmc; $i++ ) {
						if ( $pm[$i]['position'] == $position ) {
							$arrayPos = $pm[$i]['arrayPos'];
							foreach ( $cbMenuTreePathArray as $menuName ) {
								if ( is_array( $arrayPos ) && ( key( $arrayPos ) == trim( $menuName ) ) ) {
									$arrayPos = $arrayPos[key( $arrayPos )];
								} else {
									// not matching full menu path: check next:
									break;
								}
							}
							if ( !is_array( $arrayPos ) ) {
								// came to end of path: match found: stop searching:
								break;
							}
						}
					}
					// replace by nothing in case not found:
					$replaceString = "";
					if ( $i < $pmc ) {
						// found: replace with menu item: first check for qualifiers for special changes:
						$cbMenuTags = substr( $msg, $foundPosBegin + $pragmaLen + 1, $foundPosTagEnd - ($foundPosBegin + $pragmaLen + 1) );
						if ($htmlspecialcharsEncoded) {
							$cbMenuTags = cbUnHtmlspecialchars( $cbMenuTags );
						}
						$cbMenuTagsArray = $this->_explodeTags( $cbMenuTags, array( "href", "target", "title", "class", "style", "img", "caption") );
						if (substr(ltrim( $pm[$i]['url'] ),0,2) == '<a') {
							$matches			=	null;
							if ( preg_match( '/ href="([^"]+)"/i', $pm[$i]['url'], $matches ) ) {
								$pm[$i]['url']	=	$matches[1];
							}
						}
						$replaceString .= $this->_placeTags( $cbMenuTagsArray, 'href', $pm[$i], 'url', '<a href="$1"'
							. $this->_placeTags( $cbMenuTagsArray, 'target', $pm[$i], 'target', ' target="$1"' )
							. $this->_placeTags( $cbMenuTagsArray, 'title', $pm[$i], 'tooltip', ' title="$1"' )
							. $this->_placeTags( $cbMenuTagsArray, 'class', $pm[$i], 'undef', ' class="$1"' )
							. $this->_placeTags( $cbMenuTagsArray, 'style', $pm[$i], 'undef', ' style="$1"' )
							. ">"
						);
						$replaceString .= $this->_placeTags( $cbMenuTagsArray, 'img', $pm[$i], 'img', '$1' );
						$replaceString .= $this->_placeTags( $cbMenuTagsArray, 'caption', $pm[$i], 'caption', '$1' );
						$replaceString .= $this->_placeTags( $cbMenuTagsArray, 'href', $pm[$i], 'url', '</a>' );

						/*	$this->menuBar->addObjectItem( $pm[$i]['arrayPos'], $pm[$i]['caption'],
							isset($pm[$i]['url'])	?$pm[$i]['url']		:"",
							isset($pm[$i]['target'])?$pm[$i]['target']	:"",
							isset($pm[$i]['img'])	?$pm[$i]['img']		:null,
							isset($pm[$i]['alt'])	?$pm[$i]['alt']		:null,
							isset($pm[$i]['tooltip'])?$pm[$i]['tooltip']:null,
							isset($pm[$i]['keystroke'])?$pm[$i]['keystroke']:null );
						*/
					}
					$msgResult .= substr( $msg, 0, $foundPosBegin );
					$msgResult .= $replaceString;
					$msg		= substr( $msg, $foundPosEnd + $pragmaLen + 3 );
					//        $srchtxt = "[menu:".$cbMenuTreePath."]";    // get new search text
					//        $msg = str_replace($srchtxt,$replaceString,$msg);    // replace founded case insensitive search text with $replace
				} else {
					break;
				}
			} else {
				break;
			}
		}
		return $msgResult . $msg;
	}

	/**
	 * Evaluate an user attribute
	 * Private function: DO NOT USE AS PUBLIC!
	 * (it is public because PHP 5.3 does not support $this in anonymous functions within function _evaluateIfs() below)
	 *
	 * @param  string       $userAttrVal  Attribute value ('#me', '#displayed', '#displayedOrMe', '1231' (user-id))
	 * @return CBuser                     CB User corresponding to $userAttrValue
	 */
	public function & _evaluateUserAttrib( $userAttrVal )
	{
		global $_CB_framework;

		if ( $userAttrVal !== '' ) {
			$uid			=	null;

			if ( ( $userAttrVal == '#displayed' ) || ( $userAttrVal == '#displayedOrMe' ) ) {
				$uid		=	(int) $_CB_framework->displayedUser();
			}

			if ( ( ! $uid ) && ( ( $userAttrVal == '#displayedOrMe' ) || ( $userAttrVal == '#me' ) ) ) {
				$uid		=	(int) Application::MyUser()->getUserId();
			}

			if ( ( ! $uid ) && preg_match( '/^[1-9][0-9]*$/', $userAttrVal ) ) {
				$uid		=	(int) $userAttrVal;
			}

			if ( $uid ) {
				if ( $uid == $this->_cbuser->id ) {
					$user	=	$this;
				} else {
					$user	=	CBuser::getInstance( (int) $uid, false );
				}
			} else {
				$user		=	CBuser::getInstance( null );
			}
		} else {
			$user			=	$this;
		}

		return $user;
	}

	/**
	 * Evaluate [cb:if ...]
	 *
	 * @access private  (public because it's recursively calling itself in the preg_replace_callback function at bottom)
	 *
	 * @param  string|array  $input
	 * @param  array         $extraStrings
	 * @return string
	 */
	public function _evaluateIfs( $input, $extraStrings = array() )
	{
//		$regex										=	"#\[if ([^\]]+)\](.*?)\[/if\]#s";
//		$regex 										=	'#\[indent]((?:[^[]|\[(?!/?indent])|(?R))+)\[/indent]#s';
		$regex										=	'#\[cb:if(?: +user="([^"/\[\] ]+)")?( +[^\]]+)\]((?:[^\[]|\[(?!/?cb:if[^\]]*])|(?R))+)\[/cb:if]#';
		$that										=	$this;

		return preg_replace_callback( $regex, function( array $matches ) use ( $extraStrings, $that )
		{
			$regex2									=	'# +(?:(&&|and|\|\||or|) +)?([^=<!>~ ]+) *(=|<|>|>=|<=|<>|!=|=~|!~| includes |!includes ) *"([^"]*)"#';
			$conditions								=	null;

			if ( preg_match_all( $regex2, $matches[2], $conditions ) ) {
				$user								=	$that->_evaluateUserAttrib( $matches[1] );
				$resultsIdx							=	0;
				$results							=	array( $resultsIdx => true );

				for ( $i = 0, $n = count( $conditions[0] ); $i < $n; $i++ ) {
					$operator						=	$conditions[1][$i];
					$field							=	$conditions[2][$i];
					$compare						=	$conditions[3][$i];
					$value							=	$conditions[4][$i];

					if ( $field === 'viewaccesslevel' ) {
						$var = Application::User( (int) $user->getUserData()->get( 'id' ) )->getAuthorisedViewLevels();
					} elseif ( $field === 'usergroup' ) {
						$var						=	Application::User( (int) $user->getUserData()->get( 'id' ) )->getAuthorisedGroups();
					} elseif ( $field === 'application_context' ) {
						$var						=	( Application::Cms()->getClientId() ? 'administrator' : 'frontend' );
					} elseif ( $field === 'language_code' ) {
						list( $var )				=	explode( '-', Application::Cms()->getLanguageTag() );
					} elseif ( $field === 'language_tag' ) {
						$var						=	Application::Cms()->getLanguageTag();
					} elseif ( $field ) {
						if ( isset( $extraStrings[$field] ) ) {
							$var					=	$extraStrings[$field];
						} else {
							$var					=	$user->getField( $field, null, 'php', 'none', 'profile', 0, true );		// allow accessing all fields in the if

							if ( is_array( $var ) ) {
								$var				=	array_shift( $var );
							} elseif ( isset( $user->_cbuser->$field ) ) {
								// fall-back to the record if it exists:
								$var				=	$user->_cbuser->get( $field );
							} else {
								$fieldLower			=	strtolower( $field );

								if ( isset( $user->_cbuser->$fieldLower ) ) {
									// second fall-back to the record if it exists:
									$var			=	$user->_cbuser->get( $fieldLower );
								} else {
									$var			=	null;
								}
							}
						}
					} else {
						$var						=	null;
					}

					// When using an includes or !includes operator ensure the value stays an array if it is an array:
					if ( is_array( $var ) && ( ! in_array( $compare, array( ' includes ', '!includes ' ) ) ) ) {
						$var						=	implode( '|*|', $var );
					}

					// Ensure user id is always an integer:
					if ( in_array( $field, array( 'id', 'user_id' ) ) ) {
						$var						=	(int) $var;
					}

					if ( ( $field == 'user_id' ) && ( $value == 'myid' ) ) {
						$value						=	(int) Application::MyUser()->getUserId();
					}

					switch ( $compare ) {
						case '=':
							$r						=	( $var == $value );
							break;
						case '<':
							$r						=	( $var < $value );
							break;
						case '>':
							$r						=	( $var > $value );
							break;
						case '>=':
							$r						=	( $var >= $value );
							break;
						case '<=':
							$r						=	( $var <= $value );
							break;
						case '<>':
						case '!=':
							$r						=	( $var != $value );
							break;
						case '=~':
						case '!~':
							$ma						=	@preg_match( $value, $var );
							$r						=	( $compare == '=~' ? ( $ma === 1 ) : ( $ma == 0 ) );

							if ( $ma === false ) {
								// error in regexp itself:
								global $_CB_framework;

								if ( $_CB_framework->getCfg( 'debug' ) > 0 ) {
									echo sprintf( CBTxt::T("CB Regexp Error %s in expression %s"), ( ( ! is_callable( 'preg_last_error' ) ) ? '' : preg_last_error() ), htmlspecialchars( $value ) );
								}
							}
							break;
						case ' includes ':
							// [cb:if viewaccesslevel includes "1"] or [cb:if multicheckboxfield includes "choice2"]
							$r						=	in_array( $value, (array) $var );
							break;
						case '!includes ':
							// [cb:if viewaccesslevel !includes "3"] or [cb:if multicheckboxfield !includes "choice2"]
							$r						=	! in_array( $value, (array) $var );
							break;
						default:
							return CBTxt::T( 'UNDEFINED_IF_COMPARISON_OPERATOR_OPERATOR', 'Undefined [cb:if ...] comparison operator [OPERATOR] !', array( '[OPERATOR]' => $compare ) );
					}

					if ( in_array( $operator, array( 'or', '||' ) ) ) {
						$resultsIdx++;

						$results[++$resultsIdx]		=	true;
					}

					// combine and:
					$results[$resultsIdx]			=	( $results[$resultsIdx] && $r );
				}

				// combine or:
				$r									=	false;

				foreach ( $results as $rr ) {
					$r								=	( $r || $rr );
				}

				return ( $r ? $matches[3] : '' );
			} else {
				return '';
			}
		}, $input );
	}

	/**
	 * Evaluate CB Fields replacements
	 *
	 * @access private  (public because it's recursively calling itself in the preg_replace_callback function at bottom)
	 *
	 * @param  string|array   $input
	 * @param  boolean|array  $htmlspecialcharsParam  on replaced values only: FALSE : no htmlspecialchars, TRUE: do htmlspecialchars, ARRAY: callback method
	 * @return string
	 */
	public function _evaluateCbFields( $input, $htmlspecialcharsParam = null )
	{
		static $htmlspecialchars	=	null;

		$regex						=	'/\[([\w-]+)\]/';

		if ( is_array( $input ) ) {
			$val					=	$this->getField( $input[1], null, 'php', 'none', 'profile', 0, true );		// allow accessing all fields in the data

			if ( is_array( $val ) ) {
				$val				=	array_shift( $val );

				if ( is_array( $val ) ) {
					$val			=	implode( '|*|', $val );
				}
			} elseif ( isset( $this->_cbuser->$input[1] ) ) {
				$val				=	$this->_cbuser->get( $input[1] );

				if ( is_array( $val ) ) {
					$val			=	implode( '|*|', $val );
				}
			} else {
				$lowercaseVarName	=	strtolower( $input[1] );

				if ( isset( $this->_cbuser->$lowercaseVarName ) ) {
					$val			=	$this->_cbuser->get( $lowercaseVarName );

					if ( is_array( $val ) ) {
						$val		=	implode( '|*|', $val );
					}
				} else {
					$val			=	array();		// avoid substitution
				}
			}

			// Ensure user id is always an integer:
			if ( in_array( $input[1], array( 'id', 'user_id' ) ) ) {
				$val				=	( is_object( $val ) || is_array( $val ) ? 0 : (int) $val );
			}

			if ( ( ! is_object( $val ) ) && ( ! is_array( $val ) ) ) {
				if ( ! ( ( strtolower( $input[1] ) == 'password' ) && ( strlen( $val ) >= 32 ) ) ) {
					if ( is_array( $htmlspecialchars ) ) {
						$val		=	call_user_func_array( $htmlspecialchars, array( $val ) );
					} elseif ( $htmlspecialchars ) {
						$val		=	htmlspecialchars( $val );
					}

					return $val;
				}
			}

			return '[' . $input[1] . ']';
		}

		$htmlspecialchars			=	$htmlspecialcharsParam;

		return preg_replace_callback( $regex, array( $this, '_evaluateCbFields' ), $input );
	}

	/**
	 * Evaluates CB Tags
	 *
	 * @access private  (public because of recursive preg_replace_callback use at end)
	 *
	 * @param  string|array  $input
	 * @return string
	 */
	public function _evaluateCbTags( $input )
	{
		global $_CB_framework;

		$regex							=	'#\[cb:(userdata +field|userfield +field|usertab +tab|userposition +position|date +format|url +location|config +param)="((?:[^"]|\\\\")+)"(?: +user="([^"/\] ]+)")?(?: +default="((?:[^"]|\\\\")+)")?(?: +output="([a-zA-Z]+)")?(?: +formatting="([a-zA-Z]+)")?(?: +reason="([a-zA-Z]+)")?(?: +list="([0-9]+)")? */\]#';

		if ( is_array( $input ) ) {
			if ( isset( $input[3] ) ) {
				$user					=	$this->_evaluateUserAttrib( $input[3] );
			} else {
				$user					=	$this;
			}

			$keywords					=	explode( ' ', $input[1] );
			$type						=	$keywords[0];

			switch ( $type ) {
				case 'userdata':
					$field				=	$input[2];
					$default			=	( isset( $input[4] ) ? CBTxt::T( str_replace( '\"', '"', $input[4] ) ) : null );
					$reason				=	( isset( $input[7] ) ? ( $input[7] !== '' ? $input[7] : 'profile' ) : 'profile' );
					$var				=	$user->getField( $field, $default, 'php', 'none', $reason, 0, true );		// allow accessing all fields in the data

					if ( is_array( $var ) ) {
						$var			=	array_shift( $var );

						if ( is_array( $var ) ) {
							$var		=	implode( '|*|', $var );
						}
					} elseif ( isset( $user->_cbuser->$field ) ) {
						// fall-back to the record if it exists:
						$var			=	$user->_cbuser->get( $field );

						if ( is_array( $var ) ) {
							$var		=	implode( '|*|', $var );
						}
					} else {
						$fieldLower		=	strtolower( $field );

						if ( isset( $user->_cbuser->$fieldLower ) ) {
							// second fall-back to the record if it exists:
							$var		=	$user->_cbuser->get( $fieldLower );

							if ( is_array( $var ) ) {
								$var	=	implode( '|*|', $var );
							}
						} else {
							$var		=	null;
						}
					}

					// Ensure user id is always an integer:
					if ( in_array( $field, array( 'id', 'user_id' ) ) ) {
						$var			=	(int) $var;
					}

					return $var;
					break;
				case 'userfield':
				case 'usertab':
					$default			=	( isset( $input[4] ) ? CBTxt::T( str_replace( '\"', '"', $input[4] ) ) : null );
					$output				=	( isset( $input[5] ) ? ( $input[5] !== '' ? $input[5] : 'html' ) : 'html' );
					$formatting			=	( isset( $input[6] ) ? ( $input[6] !== '' ? $input[6] : 'none' ) : 'none' );
					$reason				=	( isset( $input[7] ) ? ( $input[7] !== '' ? $input[7] : 'profile' ) : 'profile' );

					if ( $type == 'userfield' ) {
						$field			=	$user->getField( $input[2], $default, $output, $formatting, $reason, 0, false );		// do not allow accessing all fields in the fields

						if ( ( $output == 'php' ) && ( is_array( $field ) ) ) {
							$field		=	array_shift( $field );

							if ( is_array( $field ) ) {
								$field	=	implode( '|*|', $field );
							}
						}

						// Ensure user id is always an integer:
						if ( in_array( $input[2], array( 'id', 'user_id' ) ) ) {
							$field		=	(int) $field;
						}

						return $field;
					} else {
						return $user->getTab( $input[2], $default, ( $output == 'none' ? null : $output ), $formatting, $reason );
					}
					break;
				case 'userposition':
					return $user->getPosition( $input[2] );
					break;
				case 'date':
					return date( $input[2], $_CB_framework->now() );
					break;
				case 'url':
					switch ( $input[2] ) {
						case 'login':
						case 'logout':
						case 'registers':
						case 'lostpassword':
						case 'manageconnections':
							return $_CB_framework->viewUrl( $input[2], false );
							break;
						case 'profile_view':
							return $_CB_framework->userProfileUrl( $user->_cbuser->id, false );
							break;
						case 'profile_edit':
							return $_CB_framework->userProfileEditUrl( $user->_cbuser->id, false );
							break;
						case 'list':
							$list		=	( isset( $input[8] ) ? ( $input[8] !== '' ? $input[8] : null ) : null );

							return $_CB_framework->userProfilesListUrl( $list, false );
							break;
						case 'itemid':
							return getCBprofileItemid( false );
							break;
						default:
							return '';
					}
					break;
				case 'config':
					switch ( $input[2] ) {
						case 'live_site':
						case 'sitename':
						case 'lang':
						case 'lang_name':
						case 'lang_tag':
							return $_CB_framework->getCfg( $input[2] );
							break;
						default:
							return '';
					}
					break;
				default:
					return '';
			}
		}

		return preg_replace_callback( $regex, array( $this, '_evaluateCbTags' ), $input );
	}
}
