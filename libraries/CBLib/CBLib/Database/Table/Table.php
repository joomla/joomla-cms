<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 08.06.13 17:29 $
* @package CBLib\Database
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Database\Table;

use CBLib\Application\Application;
use CBLib\Database\DatabaseDriverInterface;
use CBLib\Input\Get;
use CBLib\Language\CBTxt;

defined('CBLIB') or die();

/**
 * CBLib\Database\Table Class implementation
 * 
 */
class Table implements TableInterface
{
	/**
	 * Table name in database
	 * @var string
	 */
	protected $_tbl;

	/**
	 * Primary key(s) of table
	 * @var string|array
	 */
	protected $_tbl_key;

	/**
	 * Database object:
	 * @var DatabaseDriverInterface
	 */
	protected $_db;

	/**
	 * Latest error related to the entry
	 * @var string
	 * @deprecated 2.0 : Will become protected in 2.1. Use GetError() or SetError() instead.
	 */
	public $_error		=	'';

	/**
	 *	Constructor (allows to set non-standard table and key field)
	 *	Can be overloaded/supplemented by the child class
	 *
	 *	@param  DatabaseDriverInterface  $db     [optional] CB Database object
	 *	@param  string                   $table  [optional] Name of the table in the db schema relating to child class
	 *	@param  string|array             $key    [optional] Name of the primary key field in the table
	 */
	public function __construct( DatabaseDriverInterface $db = null, $table = null, $key = null )
	{
		if ( $db ) {
			$this->_db		=	$db;
		} else {
			$this->_db		=	Application::Database();
		}

		if ( $table ) {
			$this->_tbl		=	$table;
		}

		if ( $key ) {
			$this->_tbl_key	=	$key;
		}
	}

	/**
	 * Magic Method to return an array of serialize-able variables
	 *
	 * @return array
	 */
	public function __sleep()
	{
		return $this->getPublicProperties();
	}

	/**
	 * Tells if this Table has the $feature (e.g. 'ordering', 'checkout')
	 * any override must return parent::hasFeature( $feature, $forField ) instead of false.
	 *
	 * @param  string  $feature   Feature to check
	 * @param  string  $forField  [optional] Field of Table with that feature (if a feature, e.g. ordering, supports multiple fields)
	 * @return boolean            True: has the feature, False: has it not
	 */
	public function hasFeature( /** @noinspection PhpUnusedParameterInspection */ $feature, $forField = null )
	{
		return false;
	}

	/**
	 * Loads a row from the database into $this object by primary key or specific (typed) keys
	 * E.g. load->( array( 'name' => 'Smith', 'age' => 25 ) ).
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
		$where	=	$this->getSafeWhereStatements( $keys );

		if ( empty( $where ) ) {
			return false;
		}

		//BB fix : resets default values to all object variables, because NULL SQL fields do not overide existing variables !	TODO: May be removed for 2.0!
		$primaryKeys					=	array_keys( $this->getPrimaryKeysTypes() );
		$class_vars = get_class_vars(get_class($this));
		foreach ($class_vars as $name => $value) {
			if ( ( ! in_array( $name, $primaryKeys ) ) && ($name != "_db") && ($name != "_tbl") && ($name != "_tbl_key") && ( substr( $name, 0 , 10 ) != "_history__" ) ) {
				$this->$name = $value;
			}
		}
		//end of BB fix.

		$this->reset();

		$query	=	"SELECT *"
				.	"\n FROM "  . $this->_db->NameQuote( $this->_tbl )
				.	"\n WHERE " . implode( ' AND ', $where );

		$this->_db->setQuery( $query );

		return $this->_db->loadObject( $this );
	}

	/**
	 * If a table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 * If table has a single primary key, updates the primary key in $this with the new value
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean  $updateNulls  TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 *
	 * @throws \RuntimeException
	 */
	public function store( $updateNulls = false )
	{
		$primaryKeys		=	array_keys( $this->getPrimaryKeysTypes() );

		if ( $this->hasPrimaryKey() ) {
			$ok				=	$this->_db->updateObject( $this->_tbl, $this, $primaryKeys, $updateNulls );
		} else {
			if ( count( $primaryKeys ) == 1 ) {
				$primaryKeys =	$primaryKeys[0];
			}
			$ok				=	$this->_db->insertObject( $this->_tbl, $this, $primaryKeys );
		}

		if ( ! $ok ) {
			$this->_error	=	strtolower(get_class($this))."::store failed: " . $this->_db->getErrorMsg();
		}
		return $ok;
	}

	/**
	 * Resets public properties
	 *
	 * @param  mixed  $value  The value to set all properties to, default is null
	 * @return void
	 */
	public function reset( $value=null )
	{
		$keys			=	$this->getPublicProperties();
		foreach ( $keys as $k ) {
			$this->$k	=	$value;
		}
	}

	/**
	 * Gets the value of the class variable
	 *
	 * @param  string        $var      The name of the class variable
	 * @param  mixed         $default  The value to return if no value is found
	 * @param  string|array  $type     [optional] Default: null: GetterInterface::COMMAND. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return mixed                   The value of the class var (or null if no var of that name exists)
	 */
	public function get( $var, $default = null, $type = null )
	{
		if ( ! isset( $this->$var ) ) {
			return $default;
		}

		if ( $type === null ) {
			return $this->$var;
		}

		return Get::clean( $this->$var, $type );
	}

	/**
	 * Sets the new value of the class variable
	 *
	 * @param  string  $var    The name of the class variable
	 * @param  mixed   $value  The new value to assign to the variable
	 */
	public function set( $var, $value )
	{
		$this->$var		=	$value;
	}

	/**
	 * Check if a parameters path exists.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key )
	{
		return ( ( substr( $key, 0, 1 ) != '_' ) && in_array( $key, array_keys( get_class_vars( get_class( $this ) ) ) ) );
	}

	/**
	 * Returns an array of public properties names
	 *
	 * @return array
	 */
	public function getPublicProperties()
	{
		static $keys			=	null;

		if ( $keys === null ) {
			$keys				=	array();
			foreach ( array_keys( get_class_vars( get_class( $this ) ) ) as $k ) {
				if ( substr( $k, 0, 1 ) != '_' ) {
					$keys[]		=	$k;
				}
			}
		}
		return $keys;
	}

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before delete()
	 *
	 * @param  int  $oid  key index (only int supported here)
	 * @return boolean
	 */
	public function canDelete( /** @noinspection PhpUnusedParameterInspection */ $oid = null )
	{
		return true;
	}

	/**
	 * Deletes this record (no checks)
	 * canDelete should be called first to check if there are no orphan dependencies left.
	 *
	 * @param  int      $oid   Key id of row to delete (otherwise it's the one of $this) (only int supported here)
	 * @return boolean         TRUE if OK, FALSE if error
	 */
	public function delete( $oid = null )
	{
		$where	=	$this->getSafeWhereStatements( $oid );

		if ( empty( $where ) ) {
			return false;
		}

		$query				=	"DELETE FROM "	. $this->_db->NameQuote( $this->_tbl )
			.	"\n WHERE "		. implode( ' AND ', $where )
		;
		$this->_db->setQuery( $query );

		if ($this->_db->query()) {
			return true;
		} else {
			$this->_error	=	$this->_db->getErrorMsg();
			return false;
		}
	}

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before copy()
	 *
	 * @param  null|TableInterface  $object  The object being copied to otherwise $this
	 * @return boolean                       True: Can Copy, False: Cannot Copy
	 */
	public function canCopy( /** @noinspection PhpUnusedParameterInspection */ $object = null )
	{
		return true;
	}

	/**
	 * Copies this record (no checks)
	 * canCopy should be called first to check if a copy is possible.
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null )
	{
		if ( $object === null ) {
			$object			=	clone $this;
		}

		$primaryKeys		=	array_keys( $this->getPrimaryKeysTypes() );

		if ( $this->hasPrimaryKey() && count( $primaryKeys ) == 1 ) {
			$pk				=	$primaryKeys[0];
			$object->$pk	=	null;

			if ( $object->store() ) {
				return $object;
			} else {
				return false;
			}
		} else {
			$object->_error	=	get_class( $object ) . "::copy() failed: no primary key or multiple primary keys";

			return false;
		}
	}

	/**
	 * Loads an array of typed objects of a given class (same class as current object by default)
	 * which inherit from this class.
	 *
	 * @param  string  $class          [optional] class name
	 * @param  string  $key            [optional] key name in db to use as key of array
	 * @param  array   $additionalVars [optional] array of string additional key names to add as vars to object
	 * @return static[]|array          Array of objects of the same class (empty array if no objects)
	 */
	public function loadTrueObjects( $class = null, $key = "", $additionalVars = array() )
	{
		$objectsArray = array();
		$resultsArray = $this->_db->loadAssocList( $key );
		if ( is_array($resultsArray) ) {
			if ( $class == null ) {
				$class = get_class($this);
			}
			foreach ( $resultsArray as $k => $value ) {
				$objectsArray[$k]	=	new $class( $this->_db );
				// mosBindArrayToObject( $value, $objectsArray[$k], null, null, false );
				/** @var self[] $objectsArray */
				$objectsArray[$k]->bind( $value, null, null, false );
				foreach ( $additionalVars as $index ) {
					if ( array_key_exists( $index, $value ) ) {
						$objectsArray[$k]->$index = $value[$index];
					}
				}
			}
		}
		return $objectsArray;
	}

	/**
	 *	Check values before store method  (override if needed)
	 *
	 *	@return boolean  TRUE if the object is safe for saving
	 */
	public function check( )
	{
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
	public function bind( $array, $ignore='', $prefix = null  )
	{
		if ( is_array( $array ) || is_object( $array ) ) {
			$ignore						=	' ' . $ignore . ' ';
			foreach ( array_keys( get_object_vars( $this ) ) as $k ) {
				if( substr( $k, 0, 1 ) != '_' ) {
					if ( strpos( $ignore, ' ' . $k . ' ') === false) {
						$ak				=	$prefix . $k;
						if ( is_array( $array ) && isset( $array[$ak] ) ) {
							$this->$k	=	$array[$ak];
						} elseif ( isset( $array->$ak ) ) {
							$this->$k	=	$array->$ak;
						}
					}
				}
			}
		} else {
			$this->_error				=	get_class( $this ) . "::bind failed: not an array.";
			return false;
		}
		return true;
	}

	/**
	 * Method to get the Database Driver Interface object corresponding to $this table.
	 *
	 * @return  DatabaseDriverInterface  The internal database driver object.
	 */
	public function getDbo()
	{
		return $this->_db;
	}

	/**
	 * Gets the last error message
	 *
	 *@return string  Returns the error message
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Sets the error message
	 *
	 * @param  string  $message  The error message
	 * @return                   $this Table object for chaining
	 */
	public function setError( $message )
	{
		$this->_error	=	$message;

		return $this;
	}

	/**
	 * After store() this function may be called to get a result information message to display.
	 * Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore( )
	{
		return null;		// Override
	}

	/**
	 * Gets the name of the table
	 *
	 * @return string
	 */
	public function getTableName( )
	{
		return $this->_tbl;
	}

	/**
	 * Gets the primary key(s) column names.
	 * If you need type of the key too, use getPrimaryKeysTypes() instead.
	 *
	 * @param  boolean  $multiple  [optional ] FALSE (default): gets only the first primary key column name as a string, TRUE: gets a name-keyed array of the key types ('string' or 'int')
	 * @return string|array
	 */
	public function getKeyName( $multiple = false )
	{
		$keys		=	array_keys( $this->getPrimaryKeysTypes() );

		if ( ! $multiple ) {
			// Just get the first keyName:
			$keys	=	$keys[0];
		}

		return $keys;
	}

	/**
	 * Returns the primary keys as an array( 'keyNanme' => typedValue ) where typedValue can 'string' or 'int'.
	 *
	 * @return array
	 */
	public function getPrimaryKeysTypes( )
	{
		if ( is_string( $this->_tbl_key ) ) {
			return array( $this->_tbl_key => 'string' );
		}
		return (array) $this->_tbl_key;
	}

	/**
	 * Validate that the primary key has been set.
	 *
	 * @return  boolean  True if the primary key(s) have been set.
	 */
	protected function hasPrimaryKey()
	{
		if ( is_string( $this->_tbl_key ) ) {
			return ! empty( $this->{$this->_tbl_key} );
		} else {
			foreach ( $this->_tbl_key as $key => $val )
			{
				if ( empty( $this->$key ) ) {
					return false;
				}
			}
			return true;
		}
	}

	/**
	 * Computes a safe WHERE statements as array to implode with ' AND ' or returns FALSE if none.
	 *
	 * @param  int|string|array  $keys                   Key-value to use for primary key condition, or array of key => value pairs to match
	 * @param  array             $tableReferences        Table references, e.g. array( $this->_tbl => 'm' ). (Must be SQL-safe)
	 * @param  string            $defaultTableReference  Default table reference, e.g. 'm' (Must be SQL-safe)
	 * @return array|boolean                             Check for ! empty( $where ) before using the resulting array!
	 *
	 * @throws \UnexpectedValueException
	 * @throws \InvalidArgumentException
	 */
	protected function getSafeWhereStatements( $keys, $tableReferences = array(), $defaultTableReference = '' )
	{
		// Determine the keys to use into $keys:
		$primaryKeysTypes				=	$this->getPrimaryKeysTypes();
		$primaryKeys					=	array_keys( $primaryKeysTypes );

		if ( empty( $keys ) ) {
			$keys						=	array();

			// If empty, use the value of the current key
			foreach ( $primaryKeys as $key ) {
				if ( empty( $this->$key ) ) {
					// If empty primary key there's is no need to load/delete anything:
					return false;
				}
				$keys[$key]			=	$this->$key;
			}
		} elseif ( ! is_array( $keys ) ) {
			if (  count( $this->getPrimaryKeysTypes() ) != 1 ) {
				throw new \InvalidArgumentException( 'Table has multiple primary keys specified (or none), and only one primary key value provided in load().' );
			}
			$keys						=	array( $primaryKeys[0] => $keys);
		}

		// Determine the WHERE array:
		$properties						=	$this->getPublicProperties();

		$where							=	array();
		foreach ( $keys as $whereField => $whereValue ) {

			if ( ! in_array( $whereField, $properties ) ) {
				throw new \UnexpectedValueException( sprintf( 'Missing where-field %s of load() in class %s.', $whereField, get_class( $this ) ) );
			}

			if ( ( isset( $primaryKeysTypes[$whereField] ) && ( $primaryKeysTypes[$whereField] == 'int' ) ) || is_int( $whereValue ) ) {
				$safeWhereValue			=	(int) $whereValue;
			}
			else {
				$safeWhereValue			=	$this->_db->Quote( $whereValue );
			}

			$tableRefPrefix			=	isset( $tableReferences[$whereField] ) ? $tableReferences[$whereField] . '.' : ( $defaultTableReference ? $defaultTableReference . '.' : '' );
			$where[]					=	$tableRefPrefix . $this->_db->NameQuote( $whereField ) . ' = ' . $safeWhereValue;
		}

		return $where;
	}

	/**
	 * Cleans junk of html editors that's needed for clean translation
	 *
	 * Temporary in 2.0 / since + deprecated 2.0
	 *
	 * @param  string  $text
	 * @return string
	 */
	protected function cleanEditorsTranslationJunk( $text ) {
		$matches					=	null;
		if ( preg_match( '/^\s*<p>([^<]+)<\/p>\s*$/i', $text, $matches ) ) {
			if ( trim( $matches[1] ) != CBTxt::T( trim( $matches[1] ) ) ) {
				$text				=	trim( $matches[1] );
			}
		}
		return $text;
	}
}
