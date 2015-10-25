<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 5/4/14 1:08 AM $
* @package CB\Database\Table
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CB\Database\Table;

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
// Temporary:
use \CBuser;
use \cbTabs;
use \cbNotification;
use \CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * CB\Database\Table\UserTable Class implementation
 *
 * TODO: This UserTable is still WIP, only inherited methods should be considered stable
 */
class UserTable extends ComprofilerTable
{
	/** @var string */
	public $name						=	null;
	/** @var string */
	public $username					=	null;
	/** @var string */
	public $email						=	null;
	/** @var string */
	public $password					=	null;
	/** @var int */
	public $block						=	null;
	/** @var int */
	public $sendEmail					=	null;
	/**
	 * @var array
	 * @deprecated 2.0 (use ViewAccessLevels and Permissions instead of directly User's Groups (except for editing the user)
	 */
	public $gids						=	array();
	/** @var string (SQL:Date) */
	public $registerDate				=	null;
	/** @var string (SQL:Date) */
	public $lastvisitDate				=	null;
	/** @var string (SQL:Date) */
	public $lastResetTime				=	null;
	/** @var int */
	public $resetCount					=	null;
	/** @var string (SQL:Date) */
	public $lastupdatedate				=	null;
	/** @var string */
	public $activation					=	null;
	/** @var string */
	public $params						=	null;

	/** @var string */
	protected $_cmsUserTable			=	'#__users';
	/** @var string */
	protected $_cmsUserTableKey			=	'id';
	/** @var string */
	protected $_cmsUserTableUsername	=	'username';
	/** @var string */
	protected $_cmsUserTableEmail		=	'email';
	/** @var string */
	protected $_cmsUserTableGid			=	'gid';

	/**
	 * CMS User object
	 * @var \JUser
	 */
	protected $_cmsUser					=	null;
	/**
	 * CB user table row
	 * @var ComprofilerTable
	 */
	protected $_comprofilerUser			=	null;
	/**
	 * CB Tabs
	 * @var cbTabs
	 */
	protected $_cbTabs					=	null;

	/**
	 * Fields from Cms User table (this array is initialized in constructor calling function _reinitNonComprofileVars())
	 * @var array
	 */
	protected $_nonComprofilerVars		 =	array( 'name', 'username', 'email', 'password', 'params', 'block', 'sendEmail', 'gids', 'registerDate', 'activation', 'lastvisitDate', 'lastResetTime', 'resetCount' );

	/**
	 * Fields from Cms User table that can be bound from frontend when a user is saveSafely in frontend (additional safety measure)
	 * @var array
	 */
	protected $_frontendNonComprofilerVars =	array( 'name', 'username', 'email', 'password', 'params' );

	/**
	 *	Constructor to set table and key field
	 *	Can be overloaded/supplemented by the child class
	 *
	 *	@param  DatabaseDriverInterface  $db     CB Database object
	 *	@param  string                   $table  Name of the table in the db schema relating to child class
	 *	@param  string|array             $key    Name of the primary key field in the table
	 */
	public function __construct( DatabaseDriverInterface $db = null, $table = null, $key = null )
	{
		parent::__construct( $db, $table, $key );
		$this->_cmsUserTableGid					=	null;
		$this->_reinitNonComprofileVars();
	}

	/**
	 * Resets public properties
	 *
	 * @param  mixed  $value  The value to set all properties to, default is null
	 */
	function reset( $value = null )
	{
		parent::reset( $value );
		$this->_reinitNonComprofileVars();
	}

	/**
	 * Initializes non-comprofiler vars for CMS users table
	 *
	 * J2.5.5 introduced 2 new columns 'lastResetTime' and 'resetCount' to the CMS users table, but forgot to introduce it to JUser (Joomla bug #28703). JUser thus gets those only in J2.5.6.
	 * This function attempts to get the real database columns names that get loaded into JUser (and into $this UserTable object) into $this->_nonComprofilerVars
	 *
	 * @since 1.8.1
	 *
	 * @return void
	 */
	protected function _reinitNonComprofileVars() {
		static $cache				=	array();

		if ( ! $cache ) {
			if ( $this->_cmsUser ) {
				$obj				=	$this->_cmsUser;
			} else {
				global $_CB_framework;
				$obj				=	$_CB_framework->_getCmsUserObject( $this->id );
			}

			$tableBased				=	false;

			// Try getting the users table column names:
			if ( is_callable( array( $obj, 'getTable' ) ) ) {
				/** @var \JTableUser $jUserTable */
				$jUserTable			=	$obj->getTable();
				if ( is_callable( array( $jUserTable, 'getFields' ) ) ) {
					// Get the fields of the table instead of the variables of the user object itself:
					// (Joomla 2.5 does anyway call that function on reset() when it load()s the user table object, and then caches the result, so it is fast)
					$obj			=	$jUserTable->getFields();
					$tableBased		=	true;
				}
			}

			// Sets the keys for non-private variables based on:
			if ( $tableBased ) {
				// based on table:
				$cache				=	array_keys( $obj );
			} else {
				// based on object:
				foreach ( $obj as $k => $v ) {
					if ( $k[0] != '_' ) {
						$cache[] 	=	$k;
					}
				}
			}
			// Now also adds the private CMS/CB variables gid and gids:
			foreach ( array( 'gid', 'gids' ) as $k ) {
				if ( ! in_array( $k, $cache ) ) {
					$cache[]		=	$k;
				}
			}
		}
		// Reset the list of not comprofiler table columns completely:
		$this->_nonComprofilerVars	=	$cache;
	}

	/**
	 *	Loads a row from the database into $this object by primary key
	 *
	 * @param  int|array  $keys   [Optional]: Primary key value or array of primary keys to match. If not specified, the value of current key is used
	 * @return boolean            Result from the database operation
	 *
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function load( $keys = null )
	{
		$cmsTableRefs	=	$this->getCmsTableReferences();

		$cmsTableRefs[$this->_cmsUserTableKey]	=	'u';
		$where	=	$this->getSafeWhereStatements( $keys, $this->getCmsTableReferences(), 'c' );

		if ( empty( $where ) ) {
			return false;
		}

		//BB fix : resets default values to all object variables, because NULL SQL fields do not overide existing variables !	TODO: May be removed for 2.0!
		$primaryKeys					=	array_keys( $this->getPrimaryKeysTypes() );
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			// Added check for _cbTabs here compared to parent::load:
			if ( ( ! in_array( $name, $primaryKeys ) ) && ( $name != '_cbTabs' ) && ($name != "_db") && ($name != "_tbl") && ($name != "_tbl_key") && ( substr( $name, 0 , 10 ) != "_history__" ) ) {
				$this->$name = $value;
			}
		}
		//end of BB fix.

		$this->reset();

		/*
			$query = "SELECT *"
			. "\n FROM " . $this->_tbl . " c, " . $this->_cmsUserTable . " u"
			. "\n WHERE c." . $this->_tbl_key . " = u." . $this->_cmsUserTableKey
			. " AND c." . $this->_tbl_key . " = " . (int) $oid
			;
			$this->_db->setQuery( $query );

			// the following is needed for being able to edit a backend user in CB from CMS which is not yet synchronized with CB:
		*/
		$query					=	'SELECT c.*, u.*'			// don't use * as in case the left join is null, the second loaded id would overwrite the first id with null
			. "\n FROM " . $this->_cmsUserTable . ' AS u'
			. "\n LEFT JOIN " . $this->_tbl . ' AS c ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
			// . " WHERE u." . $this->_cmsUserTableKey . ' = ' . (int) $oid
			. "\n WHERE " . implode( ' AND ', $where )
		;
		$this->_db->setQuery( $query );

		$arr					=	$this->_db->loadAssoc( );

		if ( empty( $arr ) ) {
			// We didn't find an entry in the CMS users table, let's try in ComprofilerTable:
			$cmsTableRefs[$this->_tbl_key]	=	'c';
			$where	=	$this->getSafeWhereStatements( $keys, $this->getCmsTableReferences(), 'c' );

			$query				=	'SELECT u.*, c.*'			// don't use * as in case the left join is null, the second loaded id would overwrite the first id with null
				. "\n FROM " . $this->_tbl . ' AS c'
				. "\n LEFT JOIN " . $this->_cmsUserTable . ' AS u ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
				// . " WHERE c." . $this->_tbl_key . ' = ' . (int) $oid
				. "\n WHERE " . implode( ' AND ', $where )
			;
			$this->_db->setQuery( $query );

			$arr				=	$this->_db->loadAssoc( );
		}
		if ( ! empty( $arr ) ) {
			$this->bindThisUserFromDbArray( $arr, $this->{$this->_tbl_key} );
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Returns array with keys of CMS table containing $tableReferenceKey
	 * (useful for $this->getSafeWhereStatements())
	 *
	 * @param  string  $tableReferenceKey  [optional] default = 'u'
	 * @return array
	 */
	protected function getCmsTableReferences( $tableReferenceKey = 'u' )
	{
		static $cmsTableReferences	=	array();

		if ( empty( $cmsTableReferences ) ) {
			foreach ( $this->_nonComprofilerVars as $key ) {
				$cmsTableReferences[$key]	=	$tableReferenceKey;
			}
		}
		return $cmsTableReferences;
	}

	/**
	 * Copy the named array or object content into this object as vars
	 * All $arr values are filled in vars of object
	 * @access private
	 *
	 * @param  array               $arr    The input array
	 * @param  int                 $oid    id
	 */
	public function bindThisUserFromDbArray( $arr, $oid = null  ) {
		foreach ( $arr as $kk => $v ) {
			$this->$kk		=	$v;
		}
		if ( $oid ) {
			// in case the left join is null, the second loaded id will be NULL and override id:
			$k					=	$this->_tbl_key;
			$this->$k			=	(int) $oid;
		}
		$this->afterBindFromDatabase();
	}

	/**
	 * This function should be called just after binding the UserTable object from database
	 * to load the gids
	 * and to fix the CMS database storage bugs.
	 * It should be avoided externally, but is used by cb.lists.php
	 */
	public function afterBindFromDatabase( ) {
		$gids			=	array_values( (array) \JFactory::getAcl()->getGroupsByUser( $this->id, false ) );

		foreach ( $gids as $k => $v ) {
			$gids[$k]	=	(string) $v;
		}

		$this->gids		=	$gids;
	}

	/**
	 * Loads a list of UserTable into an existing array if they are not already in it
	 * (indexed by key of this table)
	 * @since 1.4 (experimental)
	 *
	 * @param  array    $usersIds      array of id to load
	 * @param  array    $objectsArray  IN/OUT   (int) id => $class  (e.g. UserTable) with method bindThisUserFromDbArray
	 * @param  string   $class
	 */
	public function loadUsersMatchingIdIntoList( $usersIds, &$objectsArray, $class ) {
		// avoids re-loading already loaded ids:
		$usersIds			=	array_diff( $usersIds, array_keys( $objectsArray ) );

		$idsCount			=	count( $usersIds );
		if ( $idsCount > 0 ) {

			// in case the left join is null, the second loaded u.id will be NULL and override id:
			$query			=	'SELECT *, u.' . $this->_cmsUserTableKey
				. "\n FROM " . $this->_cmsUserTable . ' AS u'
				. "\n LEFT JOIN " . $this->_tbl . ' AS c ON c.' . $this->_tbl_key . ' = u.' . $this->_cmsUserTableKey
				. " WHERE u." . $this->_cmsUserTableKey . ( $idsCount == 1 ? ' = ' . (int) end( $usersIds ) : ' IN (' . implode( ',', cbArrayToInts( $usersIds ) ) . ')' );
			$this->_db->setQuery( $query );
			$resultsArray = $this->_db->loadAssocList( $this->_cmsUserTableKey );

			if ( is_array($resultsArray) ) {
				foreach ( $resultsArray as $k => $value ) {
					$objectsArray[(int) $k]	=	new $class( $this->_db );			// self (CBuser class has method below too)
					/** @var self[]|CBuser[] $objectsArray */
					$objectsArray[(int) $k]->bindThisUserFromDbArray( $value );
				}
			}
			unset( $resultsArray );
		}
	}

	/**
	 *	Loads user username from database
	 *
	 *	@param  string   $username
	 *	@return boolean    TRUE: success, FALSE: error in database access
	 */
	public function loadByUsername( $username ) {
		return $this->load( array( $this->_cmsUserTableUsername => $username ) );
	}

	/**
	 *	Loads user username from database
	 *
	 *	@param  string   $email
	 *	@return boolean    TRUE: success, FALSE: error in database access
	 */
	public function loadByEmail( $email ) {
		return $this->load( array( $this->_cmsUserTableEmail => $email ) );
	}

	public function bindSafely( &$array, $ui, $reason, &$oldUserComplete ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		// Some basic sanitizations and securitizations:

		$this->id						=	(int) $this->id;

		if ( $ui == 1 ) {
			if ( $this->id ) {
				// Front-end edit user: no changes in gids and confirmed/approved states
				$this->gids				=	$oldUserComplete->gids;
				$this->block			=	(int) $oldUserComplete->block;
				$this->sendEmail		=	(int) $oldUserComplete->sendEmail;
				$this->confirmed		=	(int) $oldUserComplete->confirmed;
				$this->approved			=	(int) $oldUserComplete->approved;
			} else {
				// Front-end user registration: handle this here, so it is available to all plugins:
				$this->gids				=	array( (int) $_CB_framework->getCfg( 'new_usertype' ) );

				if ( $ueConfig['reg_admin_approval'] == 0) {
					$this->approved		=	1;
				} else {
					$this->approved		=	0;
					$this->block		=	1;
				}
				if ( $ueConfig['reg_confirmation'] == 0 ) {
					$this->confirmed	=	1;
				} else {
					$this->confirmed	=	0;
					$this->block		=	1;
				}
				if ( ( $this->confirmed == 1 ) && ( $this->approved == 1 ) ) {
					$this->block		=	0;
				} else {
					$this->block		=	1;
				}
				$this->sendEmail		=	0;

			}
			// Nb.: Backend user edit and new user are handled in core plugin CBfield_userparams field handler class
		}

		// By default, don't touch the hashed password, unless a new password is set by the saveTabsContents binding:
		$this->password					=	null;

		$this->_original_email			=	$this->email;						// needed for checkSafely()

		// Process the fields in form by CB field plugins:

		$_PLUGINS->loadPluginGroup('user');

		$this->_cbTabs					=	new cbTabs( 0, $ui, null, false );
		$this->_cbTabs->saveTabsContents( $this, $array, $reason );
		$errors							=	$_PLUGINS->getErrorMSG( false );
		if ( count( $errors ) > 0 ) {
			$this->_error				=	$errors;
			return false;
		}

		// Now do CMS-specific stuff, specially bugs-workarounds:

		$postCopy						=	array();
		if ( $ui == 1 ) {
			$vars						=	$this->_frontendNonComprofilerVars;
		} else {
			$vars						=	$this->_nonComprofilerVars;
		}
		foreach ( $vars as $k ) {
			if ( isset( $this->$k ) ) {
				$postCopy[$k]			=	$this->$k;
			}
		}
		if ( isset( $postCopy['password'] ) ) {
			$postCopy['verifyPass']		=	$postCopy['password'];			// Mambo and Joomla 1.0 has it in password2 and checks it in bind() !
			$postCopy['password2']		=	$postCopy['password'];			// Joomla 1.5 has it in password2 and checks it in bind() !
		}

		$this->_mapUsers();
		$row							=&	$this->_cmsUser;

		$pwd							=	$this->password;						// maybe cleartext at that stage.
		if ( $pwd == '' ) {
			$pwd						=	null;									// empty: don't update/change
			$this->password				=	null;
		}

		$rowBindResult					=	$row->bind( $postCopy );				// in Joomla 1.5, this modifies $postCopy and hashes password !
		if ( ! $rowBindResult ) {
			$this->_error				=	array( stripslashes( $row->getError() ) );

			return false;
		}

		$row->password					=	$pwd;									//  restore cleartext password at this stage.
		return true;
	}

	protected function checkSafely() {
		if ( $this->_cmsUser === null ) {
			$this->_mapUsers();			//TODO: Not even sure if this is still needed, since it was used in this function for working around a bug in Joomla 1.0 and below only.
		}
		return true;
	}

	/**
	 * Copy the named array or object content into this object as vars
	 * only existing vars of object are filled.
	 * When undefined in array, object variables are kept.
	 *
	 * WARNING: DOES addslashes / escape BY DEFAULT
	 *
	 * Can be overridden or overloaded.
	 *
	 * @param  array|object  $array         The input array or object
	 * @param  string        $ignore        Fields to ignore
	 * @param  string        $prefix        Prefix for the array keys
	 * @return boolean                      TRUE: ok, FALSE: error on array binding
	 */
	public function bind( $array, $ignore='', $prefix = null )
	{
		$bind					=	parent::bind( $array, $ignore, $prefix );

		if ( $bind ) {
			if ( ( $this->gids !== null ) && is_string( $this->gids ) && ( strlen( $this->gids ) > 0 ) ) {
				if ( ( $this->gids[0] === '{' ) || ( $this->gids[0] === '[' ) ) {
					$gids		=	json_decode( $this->gids );
				} else {
					$gids		=	explode( '|*|', $this->gids );
				}

				$this->gids		=	cbToArrayOfInt( $gids );
			}
		}

		return $bind;
	}

	/**
	 * If table key (id) is NULL : inserts new rows
	 * otherwise updates existing row in the database tables
	 *
	 * Can be overridden or overloaded by the child classes
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 *
	 * @throws \RuntimeException
	 */
	public function store( $updateNulls = false ) {
		global $_CB_framework, $ueConfig;

		$this->id									=	(int) $this->id;

		$isNew										=	( $this->id == 0 );

		$oldUsername								=	null;
		$oldGids									=	array();
		$oldBlock									=	null;

		if ( ! $isNew ) {
			// get actual username to update sessions in case:
			$sql			=	'SELECT ' . $this->_db->NameQuote( $this->_cmsUserTableUsername )
				.	', '	. $this->_db->NameQuote( 'block' )
				.	' FROM ' . $this->_db->NameQuote( $this->_cmsUserTable ) . ' WHERE ' . $this->_db->NameQuote( $this->_cmsUserTableKey ) . ' = ' . (int) $this->user_id;
			$this->_db->setQuery( $sql );
			$oldEntry								=	null;
			if ( $this->_db->loadObject( $oldEntry ) ) {
				/** @var \JUser $oldEntry */
				$oldUsername						=	$oldEntry->username;

				$gids								=	array_values( (array) \JFactory::getAcl()->getGroupsByUser( $this->id, false ) );

				foreach ( $gids as $k => $v ) {
					$gids[$k]						=	(string) $v;
				}

				$oldGids							=	$gids;

				$oldBlock							=	$oldEntry->block;
			}
		}

		if ( ( ! $isNew ) && ( $this->confirmed == 0 ) && ( $this->cbactivation == '' ) && ( $ueConfig['reg_confirmation'] != 0 ) ) {
			$this->_setActivationCode();
		}

		// creates CMS and CB objects:
		$this->_mapUsers();

		// remove the previous email set in bindSafely() and needed for checkSafely():
		unset( $this->_original_email );

		// stores first into CMS to get id of user if new:
		$this->_cmsUser->groups					=	$this->gids;

		$result									=	$this->_cmsUser->save();
		if ( ! $result ) {
			$this->_error						=	$this->_cmsUser->getError();
			if ( class_exists( 'JText' ) ) {
				$this->_error					=	\JText::_( $this->_error );
			}
		}

		if ( $result ) {
			// synchronize id and user_id:
			if ( $isNew ) {
				$this->id						=	$this->_cmsUser->id;
				$this->_comprofilerUser->id		=	$this->_cmsUser->id;

				if ( ( $this->confirmed == 0 ) && ( $this->cbactivation == '' ) && ( $ueConfig['reg_confirmation'] != 0 ) ) {
					$this->_setActivationCode();
				}
			}

			// stores CB user into comprofiler: if new, inserts, otherwise updates:
			if ( $this->user_id == 0 ) {
				$this->user_id						=	$this->_cmsUser->id;
				$this->_comprofilerUser->user_id	=	$this->user_id;
				$result								=	$this->_comprofilerUser->storeNew( $updateNulls );
			} else {
				$result								=	$this->_comprofilerUser->store( $updateNulls );
			}
			if ( ! $result ) {
				$this->_error						=	$this->_comprofilerUser->getError();
			}
		}
		if ( $result ) {
			// update the ACL:
			$query	=	'SELECT m.id AS aro_id, a.group_id FROM #__user_usergroup_map AS a'
					.	"\n INNER JOIN #__usergroups AS m ON m.id= a.group_id"
					.	"\n WHERE a.user_id = " . (int) $this->id;
			$this->_db->setQuery( $query );
			$aro_group							=	null;
			$result								=	$this->_db->loadObject( $aro_group );
			/** @var \StdClass $aro_group */

			if ( $result && ( ! $isNew ) && ( ( $oldUsername != $this->username ) || self::_ArraysEquivalent( $oldGids, $this->gids ) || ( ( $oldBlock == 0 ) && ( $this->block == 1 ) ) ) ) {
				// Update current sessions state if there is a change in gid or in username:
				if ( $this->block == 0 ) {
					$query	=	'UPDATE #__session '
							.	"\n SET username = " . $this->_db->Quote( $this->username )
							.	"\n WHERE userid = " . (int) $this->id;
					$this->_db->setQuery( $query );
					$result				=	$this->_db->query();

					// This is needed for instant adding of groups to logged-in user (fixing bug #3581):
					$session					=	\JFactory::getSession();
					$jUser						=	$session->get( 'user' );

					if ( $jUser->id == $this->id ) {
						\JAccess::clearStatics();
						$session->set( 'user', new \JUser( (int) $this->id ) );
					}
				} else {
					// logout user now that user login has been blocked:
					if ( $_CB_framework->myId() == $this->id ) {
						$_CB_framework->logout();
					}
					$this->_db->setQuery( "DELETE FROM #__session WHERE userid = " . (int) $this->id );			//TBD: check if this is enough for J 1.5
					$result				=	$this->_db->query();
				}
			}
			if ( ! $result ) {
				$this->_error					=	$this->_db->stderr();
				return false;
			}
		}
		return $result;
	}

	/**
	 * Are all of the int values of array $a1 in array $a2 and the other way around too (means arrays contain same integer values) ?
	 *
	 * @param  array    $a1
	 * @param  array    $a2
	 * @return boolean
	 */
	protected static function _ArraysEquivalent( $a1, $a2 ) {
		cbArrayToInts( $a1 );
		cbArrayToInts( $a2 );
		return self::_allValuesOfArrayInArray( $a1, $a2 ) && self::_allValuesOfArrayInArray( $a2, $a1 );
	}

	/**
	 * Are all of the values of array $a1 in array $a2 ?
	 *
	 * @param  array    $a1
	 * @param  array    $a2
	 * @return boolean
	 */
	protected static function _allValuesOfArrayInArray( $a1, $a2 ) {
		foreach ( $a1  as $v ) {
			if ( ! in_array( $v, $a2 ) ) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Store an array of values to user object
	 * Used only in banUser function in FE: TODO: Change usage in banUser ?
	 *
	 * @param $values
	 * @param bool $triggers
	 * @return bool
	 */
	public function storeDatabaseValues( $values, $triggers = true ) {
		global $_CB_framework, $_PLUGINS;

		if ( $this->id && is_array( $values ) && $values ) {
			$ui								=	$_CB_framework->getUi();

			$userVars						=	array_keys( get_object_vars( $this ) );

			$user							=	new UserTable( $this->_db );
			$oldUserComplete				=	new UserTable( $this->_db );

			foreach ( $userVars as $k ) {
				if ( substr( $k, 0, 1 ) != '_' ) {
					$user->set( $k, $this->get( $k ) );
					$oldUserComplete->set( $k, $this->get( $k ) );
				}
			}

			foreach ( $values as $name => $value ) {
				if ( in_array( $name, $userVars ) ) {
					$user->set( $name, $value );
				}
			}

			if ( $triggers ) {
				if ( $ui == 1 ) {
					$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$user, &$user, &$oldUserComplete, &$oldUserComplete ) );
				} elseif ( $ui == 2 ) {
					$_PLUGINS->trigger( 'onBeforeUpdateUser', array( &$user, &$user, &$oldUserComplete ) );
				}
			}

			if ( isset( $values['password'] ) ) {
				$clearTextPassword			=	$user->get( 'password' );

				$user->set( 'password', $this->hashAndSaltPassword( $clearTextPassword ) );
			} else {
				$clearTextPassword			=	null;

				$user->set( 'password', null );
			}

			$return							=	$user->store();

			if ( $clearTextPassword ) {
				$user->set( 'password', $clearTextPassword );
			}

			if ( $triggers ) {
				if ( $return ) {
					if ( $ui == 1 ) {
						$_PLUGINS->trigger( 'onAfterUserUpdate', array( &$user, &$user, $oldUserComplete ) );
					} elseif ( $ui == 2 ) {
						$_PLUGINS->trigger( 'onAfterUpdateUser', array( &$user, &$user, $oldUserComplete ) );
					}
				}
			}

			$error							=	$user->getError();

			if ( $error ) {
				$this->set( '_error', $error );
			}

			unset( $user, $oldUserComplete );

			return $return;
		}

		return false;
	}

	/**
	 * Store a single value to user object
	 *
	 * @param $name
	 * @param $value
	 * @param bool $triggers
	 * @return bool
	 */
	public function storeDatabaseValue( $name, $value, $triggers = true ) {
		return $this->storeDatabaseValues( array( $name => $value ), $triggers );
	}

	/**
	 * Updates only in database $this->block
	 *
	 * @param bool $triggers
	 * @return bool
	 */
	public function storeBlock( $triggers = true ) {
		if ( $this->id ) {
			return $this->storeDatabaseValue( 'block', (int) $this->block, $triggers );
		}
		return false;
	}

	/**
	 * Updates only in database the cleartext $this->password
	 *
	 * @param bool $triggers
	 * @return bool
	 */
	public function storePassword( $triggers = true ) {
		if ( $this->id ) {
			return $this->storeDatabaseValue( 'password', $this->password, $triggers );
		}
		return false;
	}

	/**
	 * Updates only in database $this->approved
	 *
	 * @param bool $triggers
	 * @return bool
	 */
	public function storeApproved( $triggers = true ) {
		if ( $this->id ) {
			return $this->storeDatabaseValue( 'approved', (int) $this->approved, $triggers );
		}
		return false;
	}

	/**
	 * Updates only in database $this->approved
	 *
	 * @param bool $triggers
	 * @return bool
	 */
	public function storeConfirmed( $triggers = true ) {
		if ( $this->id ) {
			return $this->storeDatabaseValue( 'confirmed', (int) $this->confirmed, $triggers );
		}
		return false;
	}

	/**
	 * Saves a new or existing CB+CMS user
	 * WARNINGS:
	 * - You must verify authorization of user to perform this (user checkCBpermissions() )
	 * - You must $this->load() existing user first
	 *
	 * @param  array   $array   Raw unfiltered input, typically $_POST
	 * @param  int     $ui      1 = Front-end (limitted rights), 2 = Backend (almost unlimitted), 0 = automated (full)
	 * @param  string  $reason  'edit' or 'register'
	 * @return boolean
	 */
	public function saveSafely( &$array, $ui, $reason ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		// Get current user state and store it into $oldUserComplete:

		$oldUserComplete						=	new UserTable( $this->_db );
		foreach ( array_keys( get_object_vars( $this ) ) as $k ) {
			if( substr( $k, 0, 1 ) != '_' ) {		// ignore internal vars
				$oldUserComplete->$k			=	$this->$k;
			}
		}
		if ( $oldUserComplete->gids === null ) {
			$oldUserComplete->gids				=	array();
		}


		// 1) Process and validate the fields in form by CB field plugins:
		// 2) Bind the fields to CMS User:
		$bindResults							=	$this->bindSafely( $array, $ui, $reason, $oldUserComplete );

		if ( $bindResults ) {
			// It's ok to use raw fields below as we've already validated in bindSafely with saveTabContents

			// Check if username is missing:
			if ( $this->username == '' ) {
				// We don't have a username! Lets try to find one based off configured fallback:
				$fallbackField					=	( isset( $ueConfig['usernamefallback'] ) && $ueConfig['usernamefallback'] ? $ueConfig['usernamefallback'] : 'name' );

				// Lets see if our fallback exists and that it's a valid string that has a value:
				if ( isset( $this->$fallbackField ) && is_string( $this->$fallbackField ) && ( $this->$fallbackField != '' ) ) {
					$this->username				=	$this->$fallbackField;
					$this->_cmsUser->username	=	$this->username;
				}

				// Check if we have a username now:
				if ( ( $this->username == '' ) && ( $this->email != '' ) ) {
					// Oh no! We still don't have one! Force to email as backup:
					$this->username				=	$this->email;
					$this->_cmsUser->username	=	$this->username;
				}

				// Ok, one more try; lets see if we have a username now:
				if ( ( $this->username == '' ) && ( $this->name != '' ) ) {
					// What in the world! We still don't have one! Force to name as backup:
					$this->username				=	$this->name;
					$this->_cmsUser->username	=	$this->username;
				}

				// Now lets see if we finally have a username:
				if ( $this->username != '' ) {
					// We do! Awesome! Now lets format it so it'll validate in Joomla by removing disallowed characters, all duplicate spacing, and replacing spaces with underscore:
					$this->username				=	preg_replace( '/[<>\\\\"%();&\']+/', '', trim( $this->username ) );
					$this->_cmsUser->username	=	$this->username;
				}
			}

			// Check if name is missing:
			if ( $this->name == '' ) {
				// Yup, it's missing; lets force it to username as backup:
				$this->name						=	$this->username;
				$this->_cmsUser->name			=	$this->name;
			}

			if ( ! $this->checkSafely() ) {
				$bindResults					=	false;
			}
		}

		// For new registrations or backend user creations, set registration date and password if neeeded:
		$isNew									=	( ! $this->id );
		$newCBuser								=	( $oldUserComplete->user_id == null );

		if ( $isNew ) {
			$this->registerDate					=	$this->_db->getUtcDateTime();
		}

		if ( $bindResults ) {
			if ( $isNew ) {
				if ( $this->password == null ) {
					$this->setRandomPassword();
					$ueConfig['emailpass']		=	1;		// set this global to 1 to force password to be sent to new users.
				}
			}

			// In backend only: if group has been changed and where original group was a Super Admin: check if there is at least a super-admin left:
			if ( $ui == 2 ) {
				$myGids							=	$_CB_framework->acl->get_groups_below_me( null, true );
				$i_am_super_admin				=	Application::MyUser()->isSuperAdmin();

				if ( ! $isNew ) {

					// Joomla-ACL checks:
					if ( $i_am_super_admin && ( $_CB_framework->myId() == $this->id ) ) {
						// Check that a fool Super User does not block himself:
						if ( $this->block && ! $oldUserComplete->block ) {
							$this->_error	=	'Super Users can not block themselves';
							return false;
						}
						// Check that a fool Super User does not demote himself from Super-User rights:
						if ( $this->gids != $oldUserComplete->gids ) {
							$staysSuperUser		=	Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids,'core.admin', null );
							if ( ! $staysSuperUser ) {
								$this->_error	=	'You cannot demote yourself from your Super User permission';
								return false;
							}
						}
					}
					// Check that a non-Super User/non-admin does not demote an admin or a Super user:
					if ( $this->gids != $oldUserComplete->gids ) {
						if ( ( ! $i_am_super_admin )
							&& ! ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.admin', 'com_comprofiler' )
								|| ( Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' )
									&& Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' )
									&& Application::MyUser()->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' ) ) ) )
						{
							// I am not a Super User and not an Users administrator:
							$userIsSuperUser		=	Application::User( (int) $this->id )->isSuperAdmin();
							// User is super-user: Check if he stays so:
							if ( $userIsSuperUser ) {
								$staysSuperUser		=	Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids, 'core.admin', null );
								if ( ! $staysSuperUser ) {
									$this->_error	=	'You cannot remove a Super User permission. Only Super Users can do that.';
									return false;
								}
							}
							$userCanAdminUsers	=	( Application::User( (int) $this->id )->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_users' ) || Application::User( (int) $this->id )->isAuthorizedToPerformActionOnAsset( 'core.manage', 'com_comprofiler' ) )
								&& Application::User( (int) $this->id )->isAuthorizedToPerformActionOnAsset( 'core.edit', 'com_users' )
								&& Application::User( (int) $this->id )->isAuthorizedToPerformActionOnAsset( 'core.edit.state', 'com_users' );
							// User is users-administrator: check if he can stay so:
							if ( $userCanAdminUsers ) {
								$staysUserAdmin	=	( Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids, 'core.manage', 'com_users' ) || Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids, 'core.manage', null ) )
									&& Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids, 'core.edit', 'com_users' )
									&& Application::CmsPermissions()->checkGroupsForActionOnAsset( $this->gids, 'core.edit.state', 'com_users' );
								if ( ! $staysUserAdmin ) {
									$this->_error	=	'An users manager cannot be demoted by a non-administrator';
									return false;
								}
							}
						}
					}

				}
				// Security check to avoid creating/editing user to higher level than himself: CB response to artf4529.
				if ( ( ! $i_am_super_admin ) && ( $this->gids != $oldUserComplete->gids ) ) {
					// Does user try to edit a user that has higher groups ?
					if ( count( array_diff( $this->gids, $myGids ) ) != 0 ) {
						$this->_error				=	'Unauthorized attempt to change an user at higher level than allowed !';
						return false;
					}
					// Does the user try to demote higher levels ?
					if ( array_diff( $this->gids, $myGids ) != array_diff( $oldUserComplete->gids, $myGids ) ) {
						$this->_error				=	'Unauthorized attempt to change higher groups of an user than allowed !';
						return false;
					}
				}
			}

		}

		if ( $reason == 'edit' ) {
			if ( $ui == 1 ) {
				$_PLUGINS->trigger( 'onBeforeUserUpdate', array( &$this, &$this, &$oldUserComplete, &$oldUserComplete ) );
			} elseif ( $ui == 2 ) {
				if ( $isNew || $newCBuser ) {
					$_PLUGINS->trigger( 'onBeforeNewUser', array( &$this, &$this, false ) );
				} else {
					$_PLUGINS->trigger( 'onBeforeUpdateUser', array( &$this, &$this, &$oldUserComplete ) );
				}
			}
		} elseif ( $reason == 'register' ) {
			$_PLUGINS->trigger( 'onBeforeUserRegistration', array( &$this, &$this ) );
		}
		$beforeResult							=	! $_PLUGINS->is_errors();
		if ( ! $beforeResult ) {
			$this->_error						=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
		}

		// Saves tab plugins:

		// on edits, user params and block/email/approved/confirmed are done in cb.core predefined fields.
		// So now calls this and more (CBtabs are already created in $this->bindSafely() ).
		$pluginTabsResult						=	true;
		if ( $reason == 'edit' ) {
			$this->_cbTabs->savePluginTabs( $this, $array );
			$pluginTabsResult					=	! $_PLUGINS->is_errors();
			if ( ! $pluginTabsResult ) {
				$this->_error					=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
			}
		}

		$clearTextPassword						=	$this->password;

		if ( $bindResults && $beforeResult && $pluginTabsResult ) {
			// Hashes password for CMS storage:

			if ( $clearTextPassword ) {
				$hashedPassword					=	$this->hashAndSaltPassword( $clearTextPassword );
				$this->password					=	$hashedPassword;
			}

			// Stores user if it's a new user:

			if ( $isNew ) {
				if ( ! $this->store() ) {
					return false;
				}
			}

			// Restores cleartext password for the saveRegistrationPluginTabs:

			$this->password						=	$clearTextPassword;

			if ( $isNew ) {
				// Sets the instance of user, to avoid reload from database, and loss of the cleartext password.
				CBuser::setUserGetCBUserInstance( $this );
			}
		}

		if ( $reason == 'register' ) {
			// call here since we got to have a user id:
			$registerResults					=	array();
			$registerResults['tabs']			=	$this->_cbTabs->saveRegistrationPluginTabs( $this, $array );
			if ( $_PLUGINS->is_errors() ) {

				if ( $bindResults && $beforeResult && $pluginTabsResult ) {
					$plugins_error				=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
					if ( $isNew ) {
						// if it was a new user, and plugin gave error, revert the creation:
						$this->delete();
					}
					$this->_error				=	$plugins_error;
				} else {
					$this->_error				=	$_PLUGINS->getErrorMSG( false );			// $_PLUGIN collects all error messages, incl. previous ones.
				}
				$pluginTabsResult				=	false;
			}
		}

		if ( $bindResults && $beforeResult && $pluginTabsResult ) {
			$this->_cbTabs->commitTabsContents( $this, $array, $reason );
			$commit_errors						=	$_PLUGINS->getErrorMSG( false );

			if ( count( $commit_errors ) > 0 ) {
				$this->_error					=	$commit_errors;
				$bindResults					=	false;
			}
		}

		if ( ! ( $bindResults && $beforeResult && $pluginTabsResult ) ) {
			$this->_cbTabs->rollbackTabsContents( $this, $array, $reason );
			// Normal error exit point:
			$_PLUGINS->trigger( 'onSaveUserError', array( &$this, $this->_error, $reason ) );
			if ( is_array( $this->_error ) ) {
				$this->_error						=	implode( '<br />', $this->_error );
			}
			return false;
		}

		// Stores the user (again if it's a new as the plugins might have changed the user record):
		if ( $clearTextPassword ) {
			$this->password						=	$hashedPassword;
		}
		if ( ! $this->store() ) {
			return false;
		}

		// Restores cleartext password for the onAfter and activation events:

		$this->password							=	$clearTextPassword;

		// Triggers onAfter and activateUser events:

		if ( $reason == 'edit' ) {
			if ( $ui == 1 ) {
				$_PLUGINS->trigger( 'onAfterUserUpdate', array( &$this, &$this, $oldUserComplete ) );
			} elseif ( $ui == 2 ) {
				if ( $isNew || $newCBuser ) {
					if ( $isNew ) {
						$ueConfig['emailpass']	=	1;		// set this global to 1 to force password to be sent to new users.
					}
					$_PLUGINS->trigger( 'onAfterNewUser', array( &$this, &$this, false, true ) );
					if ( $this->block == 0 && $this->approved == 1 && $this->confirmed ) {
						activateUser( $this, 2, 'NewUser', false, $isNew );
					}
				} else {
					if ( ( ! ( ( $oldUserComplete->approved == 1 || $oldUserComplete->approved == 2 ) && $oldUserComplete->confirmed ) )
						&& ($this->approved == 1 && $this->confirmed ) )
					{
						// first time a just registered and confirmed user got approved in backend through save user:
						if( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == "1" ) && ( $this->password == '' ) ) {
							// generate the password is auto-generated and not set by the admin at this occasion:
							$this->setRandomPassword();
							$pwd			=	$this->hashAndSaltPassword( $this->password );
							$this->_db->setQuery( "UPDATE #__users SET password=" . $this->_db->Quote($pwd) . " WHERE id = " . (int) $this->id );
							$this->_db->query();
						}
					}
					$_PLUGINS->trigger( 'onAfterUpdateUser', array( &$this, &$this, $oldUserComplete ) );
					if ( ( ! ( ( $oldUserComplete->approved == 1 || $oldUserComplete->approved == 2 ) && $oldUserComplete->confirmed ) )
						&& ($this->approved == 1 && $this->confirmed ) )
					{
						// first time a just registered and confirmed user got approved in backend through save user:
						activateUser( $this, 2, 'UpdateUser', false );
					}

				}
			}
		} elseif ( $reason == 'register' ) {
			$registerResults['after']			=	$_PLUGINS->trigger( 'onAfterUserRegistration', array( &$this, &$this, true ) );
			$registerResults['ok']				=	true;
			return $registerResults;
		}
		return true;
	}

	/**
	 * Deletes this record (no checks)
	 *
	 * @param  int   $oid         Key id of row to delete (otherwise it's the one of $this)
	 * @param  bool  $cbUserOnly  True: delete CB user only, False: delete CB and CMS user
	 * @return boolean
	 */
	public function delete( $oid = null, $cbUserOnly = false ) {
		global $_CB_framework, $_PLUGINS;

		$k						=	$this->_tbl_key;

		if ( $oid ) {
			$this->$k			=	(int) $oid;
		}

		$_PLUGINS->loadPluginGroup( 'user' );

		$_PLUGINS->trigger( 'onBeforeDeleteUser', array( $this ) );

		if ( $_PLUGINS->is_errors() ) {
			$this->setError( $_PLUGINS->getErrorMSG() );

			return false;
		} else {
			deleteAvatar( $this->avatar );

			$reports			=	new UserReportTable();

			$reports->deleteUserReports( $this->id );

			$views				=	new UserViewTable();

			$views->deleteUserViews( $this->id );

			if ( ! $cbUserOnly ) {
				$cmsUser		=	$_CB_framework->_getCmsUserObject( $this->id );

				try {
					$cmsUser->delete( $this->id );
				} catch ( \RuntimeException $e ) {
					$this->setError( $e->getMessage() );

					return false;
				}
			}

			if ( ! parent::delete( $oid ) ) {
				return false;
			}

			$query				=	'DELETE'
								.	"\n FROM " . $this->_db->NameQuote( '#__session' )
								.	"\n WHERE " . $this->_db->NameQuote( 'userid' ) . " = " . (int) $this->id;
			$this->_db->setQuery( $query );
			$this->_db->query();

			$_PLUGINS->trigger( 'onAfterDeleteUser', array( $this, true ) );
		}

		return true;
	}

	public function checkin( $oid = null ) {
		$this->_mapUsers();					// TODO: Not sure if this is even needed anymore as the checkin function doesn't exist anymore for JUSers, so old code got removed
		return true;
	}

	protected function _mapUsers() {
		global $_CB_framework;

		if ( $this->_cmsUser === null ) {
			$this->_cmsUser							=	$_CB_framework->_getCmsUserObject( $this->id );
		}
		if ( $this->_comprofilerUser === null ) {
			$this->_comprofilerUser				=	new ComprofilerTable( $this->_db );
		}

		foreach ( get_object_vars( $this ) as $name => $value ) {
			if ( $name[0] != '_' ) {
				if ( in_array( $name, $this->_nonComprofilerVars ) ) {
					$this->_cmsUser->$name			=	$value;
				} else {
					$this->_comprofilerUser->$name	=	$value;
				}
			}
		}

		$this->_cmsUser->id							=	$this->id;
		$this->_comprofilerUser->id				=	$this->id;
		$this->_comprofilerUser->user_id			=	$this->id;
	}

	/**
	 * Gets a random password in clear-text
	 *
	 */
	public function getRandomPassword( $length = 12 ) {
		jimport( 'joomla.plugin.helper' );

		$password	=	\JUserHelper::genRandomPassword( $length );

		return $password;
	}

	/**
	 * Sets a random password in clear-text into $this->password
	 *
	 */
	public function setRandomPassword( $length = 12 ) {
		$this->password		=	$this->getRandomPassword( $length );
	}

	/**
	 * Generate the hashed/salted/encoded password for the database
	 * and to check the password at login:
	 * if $row provided, it is checking the existing password (and update if needed)
	 * if not provided, it will generate a new hashed password
	 *
	 * @param  string  $passwd  cleartext
	 * @return string           salted/hashed password
	 */
	public function hashAndSaltPassword( $passwd ) {
		$cmsUser	=	\JUser::getInstance();

		$data		=	array( 'password' => $passwd );

		$cmsUser->bind( $data );

		return $cmsUser->password;
	}

	/**
	 * Generate the hashed/salted/encoded password for the database
	 * and to check the password at login:
	 * if $row provided, it is checking the existing password (and update if needed)
	 * if not provided, it will generate a new hashed password
	 *
	 * @param  string   $passwd  cleartext
	 * @return boolean           TRUE/FALSE on password check
	 */
	public function verifyPassword( $passwd ) {
		global $_CB_framework;

		jimport( 'joomla.user.authentication' );

		$authenticate			=	\JAuthentication::getInstance();

		// We're just checking the password so we need to make sure two step authentication is off:
		if ( checkJversion( '3.2+' ) ) {
			/** @noinspection PhpIncludeInspection */
			require_once ( $_CB_framework->getCfg( 'absolute_path' ) . '/administrator/components/com_users/models/user.php' );

			$userModel			=	new \UsersModelUser;

			$twoStep			=	$userModel->getOtpConfig( 0 );
			$twoStep->model		=	'none';

			$options			=	array( 'otp_config' => $twoStep );
		} else {
			$options			=	array();
		}

		$response				=	$authenticate->authenticate( array( 'username' => $this->username, 'password' => $passwd ), $options );

		if ( $response->status === \JAuthentication::STATUS_SUCCESS ) {
			return true;
		} else {
			return false;
		}
	}

	public function _setActivationCode( ) {
		global $_CB_framework;

		$randomHash						=	md5( cbMakeRandomString() );
		$scrambleSeed					=	(int) hexdec(substr( md5 ( $_CB_framework->getCfg( 'secret' ) . $_CB_framework->getCfg( 'db' ) ), 0, 7));
		$scrambledId					=	$scrambleSeed ^ ( (int) $this->id );
		$this->cbactivation				=	'reg' . $randomHash . sprintf( '%08x', $scrambledId );
		// for CMS compatibility (and JFusion compatibility):
		$this->activation				=	$randomHash;
	}

	public function checkActivationCode( $confirmcode ) {
		return ( $this->cbactivation === $confirmcode );
	}

	public function removeActivationCode( ) {
		$query	=	'UPDATE '	. $this->_db->NameQuote( '#__comprofiler' )
			.	"\n SET "	. $this->_db->NameQuote( 'cbactivation' ) . ' = ' . $this->_db->Quote( '' )
			.	"\n WHERE "	. $this->_db->NameQuote( 'id' ) . ' = ' . (int) $this->id;
		$this->_db->setQuery( $query );
		if ( $this->_db->query() ) {
			$this->cbactivation			=	'';

			$query	=	'UPDATE '	. $this->_db->NameQuote( $this->_cmsUserTable )
				.	"\n SET "	. $this->_db->NameQuote( 'activation' ) . ' = ' . $this->_db->Quote( '' )
				.	"\n WHERE "	. $this->_db->NameQuote( 'id' ) . ' = ' . (int) $this->id;
			$this->_db->setQuery( $query );
			if ( $this->_db->query() ) {
				$this->activation		=	'';
			}
		} else {
			global $_CB_framework;
			if ( $_CB_framework->getUi() != 0 ) {
				trigger_error( 'SQL-unblock2 error: ' . $this->_db->stderr(true), E_USER_WARNING );
			}
		}
	}

	/**
	 * Gets user_id out of the activation code. WARNING: do not trust the user id until full activation code is checked.
	 *
	 * @static
	 * @param  string    $confirmcode
	 * @return int|null
	 */
	static public function getUserIdFromActivationCode( $confirmcode ) {
		global $_CB_framework;

		$lengthConfirmcode		=	strlen( $confirmcode );

		if ($lengthConfirmcode == ( 3+32+8 ) ) {
			$scrambleSeed		=	(int) hexdec(substr( md5 ( $_CB_framework->getCfg( 'secret' ) . $_CB_framework->getCfg( 'db' ) ), 0, 7));
			$unscrambledId		=	$scrambleSeed ^ ( (int) hexdec(substr( $confirmcode, 3+32 ) ) );

			return $unscrambledId;
		}

		return null;
	}

	/**
	 * Changes the confirmation state of a user
	 *
	 * @param  int     $state     0: Pending, 1: Confirmed
	 * @param  string  $messages  The messages returned by activateUser when approved
	 * @return bool
	 */
	public function confirmUser( $state, &$messages = null ) {
		global $_CB_framework, $ueConfig, $_PLUGINS;

		if ( $this->confirmed == $state ) {
			return true;
		}

		if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == 1 ) && ( $state == 1 ) && ( $this->approved == 1 ) ) {
			$this->setRandomPassword();
		}

		$_PLUGINS->trigger( 'onBeforeUserConfirm', array( &$this, &$state ) );

		if ( $_PLUGINS->is_errors() ) {
			$this->setError( $_PLUGINS->getErrorMSG( false ) );

			return false;
		}

		$this->confirmed		=	(int) $state;

		if ( $this->storeConfirmed( false ) ) {
			if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == 1 ) && ( $state == 1 ) && ( $this->approved == 1 ) ) {
				$this->storePassword( false );
			}

			$_PLUGINS->trigger( 'onAfterUserConfirm', array( $this, $state ) );

			if ( $state == 1 ) {
				$messages		=	activateUser( $this, $_CB_framework->getUi(), 'UserConfirmation', ( $_CB_framework->getUi() != 2 ) );
			}

			return true;
		}

		return false;
	}

	/**
	 * Changes the approval state of a user
	 *
	 * @param  int     $state     0: Pending, 1: Approved, 2: Rejected
	 * @param  string  $messages  The messages returned by activateUser when approved or reason for approval rejection
	 * @return bool
	 */
	public function approveUser( $state, &$messages = null )
	{
		global $_CB_framework, $ueConfig, $_PLUGINS;

		if ( $this->approved == $state ) {
			return true;
		}

		if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == 1 ) && ( $state == 1 ) ) {
			$this->setRandomPassword();
		}

		$_PLUGINS->trigger( 'onBeforeUserApproval', array( &$this, &$state ) );

		if ( $_PLUGINS->is_errors() ) {
			$this->setError( $_PLUGINS->getErrorMSG( false ) );

			return false;
		}

		$this->approved				=	(int) $state;

		if ( $this->storeApproved( false ) ) {
			if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['emailpass'] == 1 ) && ( $state == 1 ) ) {
				$this->storePassword( false );
			}

			$_PLUGINS->trigger( 'onAfterUserApproval', array( $this, $state ) );

			if ( $state == 1 ) {
				$messages			=	activateUser( $this, $_CB_framework->getUi(), 'UserApproval', false );
			} elseif ( $state == 2 ) {
				$cbNotification		=	new cbNotification();
				$savedLanguage		=	CBTxt::setLanguage( $this->getUserLanguage() );

				$cbNotification->sendFromSystem( (int) $this->id, CBTxt::T( 'UE_REG_REJECT_SUB', 'Your sign up request has been rejected!' ), CBTxt::T( 'UE_USERREJECT_MSG', 'Your sign up at [sitename] has been rejected for the following reason: [reason]', array( '[sitename]' => $_CB_framework->getCfg( 'sitename' ), '[reason]' => $messages ) ) );

				CBTxt::setLanguage( $savedLanguage );
			}

			return true;
		}

		return false;
	}

	/**
	 * Changes the block state of a user
	 *
	 * @param  int   $state  0: Unblocked, 1: Blocked
	 * @return bool
	 */
	public function blockUser( $state )
	{
		global $_PLUGINS;

		if ( $this->block == $state ) {
			return true;
		}

		$_PLUGINS->trigger( 'onBeforeUserBlocking', array( &$this, &$state ) );

		if ( $_PLUGINS->is_errors() ) {
			$this->setError( $_PLUGINS->getErrorMSG( false ) );

			return false;
		}

		$this->block	=	(int) $state;

		if ( $this->storeBlock( false ) ) {
			$_PLUGINS->trigger( 'onAfterUserBlocking', array( $this, $state ) );

			return true;
		}

		return false;
	}

	/**
	 * Changes the ban state of a user
	 *
	 * @param  int              $state   0: Unbanned, 1: Banned, 2: Pending
	 * @param  null|UserTable  $by      The user that is banning or unbanning
	 * @param  null|string     $reason  The reason for the ban or unban
	 * @return bool
	 */
	public function banUser( $state, $by = null, $reason = null )
	{
		global $_CB_framework, $ueConfig, $_PLUGINS;

		if ( $this->banned == $state ) {
			return true;
		}

		if ( ! $by ) {
			$by							=	CBuser::getMyUserDataInstance();
		}

		$values							=	array();

		$_PLUGINS->trigger( 'onBeforeUserBan', array( &$this, &$state, &$by, &$reason ) );

		if ( $_PLUGINS->is_errors() ) {
			$this->setError( $_PLUGINS->getErrorMSG( false ) );

			return false;
		}

		$values['banned']				=	(int) $state;

		if ( $reason ) {
			$values['bannedreason']		=	$reason;
		}

		if ( $state == 0 ) {
			$values['unbannedby']		=	(int) $by->id;
			$values['unbanneddate']		=	$_CB_framework->getUTCDate();
		} elseif ( $state == 1 ) {
			$values['bannedby']			=	(int) $by->id;
			$values['banneddate']		=	$_CB_framework->getUTCDate();
		}

		if ( $this->storeDatabaseValues( $values, false ) ) {
			$_PLUGINS->trigger( 'onAfterUserBan', array( $this, $state, $by, $reason ) );

			$cbNotification				=	new cbNotification();
			$savedLanguage				=	CBTxt::setLanguage( $this->getUserLanguage() );

			if ( $state == 0 ) {
				$cbNotification->sendFromSystem( (int) $this->id, CBTxt::T( 'UE_UNBANUSER_SUB', 'User Profile Unbanned' ), CBTxt::T( 'UE_UNBANUSER_MSG', 'Your user profile was unbanned by an administrator. Your profile is now visible to all users again.' ) );
			} elseif ( $state == 1 ) {
				$cbNotification->sendFromSystem( (int) $this->id, CBTxt::T( 'UE_BANUSER_SUB', 'User Profile Banned.' ), CBTxt::T( 'UE_BANUSER_MSG', 'Your user profile was banned by an administrator. Please log in and review why it was banned.' ) );
			} elseif ( $state == 2 ) {
				if ( isset( $ueConfig['emailpass'] ) && ( $ueConfig['moderatorEmail'] == 1 ) && ( $_CB_framework->getUi() != 2 ) ) {
					$cbNotification->sendToModerators( CBTxt::T( 'UE_UNBANUSERREQUEST_SUB', 'Unban Request Pending Review' ), CBTxt::T( 'UE_UNBANUSERREQUEST_MSG', 'A user has submitted a request to unban their profile. Please log in and take the appropriate action.' ) );
				}
			}

			CBTxt::setLanguage( $savedLanguage );

			return true;
		}

		return false;
	}

	/**
	 * Gets the language of user (or empty string if language is not set)
	 *
	 * @return string  The language of $this user
	 */
	public function getUserLanguage( )
	{
		return \JFactory::getUser( (int) $this->id )
			->getParam('language', '' );
	}

	/**
	 * Toggles confirmation state of a user
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML users backend
	 *
	 * @param  int  $value
	 * @return bool
	 */
	public function toggleUserConfirm( $value )
	{
		return $this->confirmUser( $value );
	}

	/**
	 * Toggles approval state of a user
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML users backend
	 *
	 * @param  int  $value
	 * @return bool
	 */
	public function toggleUserApproval( $value )
	{
		return $this->approveUser( $value );
	}

	/**
	 * Toggles block state of a user
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML users backend
	 *
	 * @param  int  $value
	 * @return bool
	 */
	public function toggleUserBlock( $value )
	{
		return $this->blockUser( $value );
	}

	/**
	 * Toggles ban state of a user
	 * Used by Backend XML only
	 * @deprecated Do not use directly, only for XML users backend
	 *
	 * @param  int  $value
	 * @return bool
	 */
	public function toggleUserBan( $value )
	{
		return $this->banUser( $value );
	}
}
