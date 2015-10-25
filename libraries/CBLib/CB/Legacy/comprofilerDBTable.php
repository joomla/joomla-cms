<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/20/14 1:13 AM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Database\DatabaseDriverInterface;
use CBLib\Database\Table\TableInterface;

defined('CBLIB') or die();

/**
 * comprofilerDBTable Class implementation
 * Here for potential backwards compatibility reason only
 * Implemented as a proxy-class to CheckedOrderedTable using proxy-target class moscomprofilerLegacyAdapterTable
 * for getting dynamic properties of the object instead of class properties
 * @see \moscomprofilerLegacyAdapterTable
 *
 * @deprecated 2.0 Use \CBLib\Database\Table\Table and its descendants instead
 * @see \CBLib\Database\Table\Table
 * @see \CBLib\Database\Table\CheckedOrderedTable
 */
class comprofilerDBTable  /* proxy-extends CheckedOrderedTable because function get() incompatibility of CBSubs 3.0.0 GPL only */
{
	private $_table;

	/**
	 * Table name in database
	 * @var string
	 */
	public $_tbl;

	/**
	 * Primary key(s) of table
	 * @var string|array
	 */
	public $_tbl_key;

	/**
	 * Database object:
	 * @var CBdatabase
	 */
	public $_db;

	/**
	 * Latest error related to the entry
	 * @var string
	 * @deprecated 2.0 : Will become protected in 2.1. Use GetError() or SetError() instead.
	 */
	public $_error		=	'';

	/**
	 *	Constructor to set table and key field
	 *	Can be overloaded/supplemented by the child class
	 *
	 *	@param  string      $table  Name of the table in the db schema relating to child class
	 *	@param  string      $key    Name of the primary key field in the table
	 *	@param  CBdatabase  $db     CB Database object
	 */
	public function comprofilerDBTable( $table, $key, $db )
	{
		/** @noinspection PhpDeprecationInspection */
		$this->_table	=	new moscomprofilerLegacyAdapterTable( $db, $table, $key );

		$this->_db		=	$db;
		$this->_tbl		=	$table;
		$this->_tbl_key	=	$key;

		$properties		=	$this->getPublicProperties();
		foreach ( $properties as $k ) {
			// We alias the slave table variables by reference, so that they always reflect $this ones:
			$this->_table->$k	=&	$this->$k;
		}
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
		return $this->_table->load( $keys );
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
		return $this->_table->store( $updateNulls );
	}

	/**
	 * Resets public properties
	 *
	 * @param  mixed  $value  The value to set all properties to, default is null
	 * @return void
	 */
	public function reset( $value=null )
	{
		$this->_table->reset( $value );
		$keys			=	$this->getPublicProperties();
		foreach ( $keys as $k ) {
			$this->$k	=	$value;
		}
	}

	/**
	 * Gets the value of the class variable
	 *
	 * @param  string  $var  The name of the class variable
	 * @return mixed         The value of the class var (or null if no var of that name exists)
	 */
	public function get( $var )
	{
		if ( isset( $this->$var ) ) {
			return $this->$var;
		} else {
			return null;
		}
	}

	/**
	 * Sets the new value of the class variable
	 *
	 * @param  string  $var     The name of the class variable
	 * @param  mixed   $newVal  The new value to assign to the variable
	 */
	public function set( $var, $newVal )
	{
		$this->$var		=	$newVal;
	}

	/**
	 * Returns an array of public properties names
	 *
	 * @return array
	 */
	public function getPublicProperties()
	{
		// return $this->_table->getPublicProperties();

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
	public function canDelete( $oid = null )
	{
		return $this->_table->canDelete( $oid );
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
		return $this->_table->delete( $oid );
	}

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before copy()
	 *
	 * @param  null|TableInterface|self  $object  The object being copied to otherwise $this
	 * @return boolean                            True: Can Copy, False: Cannot Copy
	 */
	public function canCopy( $object = null )
	{
		return $this->_table->canCopy( $object );
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
		return $this->_table->copy( $object );
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
	public function & loadTrueObjects( $class = null, $key = "", $additionalVars = null )
	{
		if ( $class == null ) {
			$class	=	get_class($this);
		}

		$return		=	$this->_table->loadTrueObjects( $class, $key, $additionalVars );

		return $return;
	}

	/**
	 *	Check values before store method  (override if needed)
	 *
	 *	@return boolean  TRUE if the object is safe for saving
	 */
	public function check( )
	{
		return $this->_table->check();
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
	 * @param  boolean       $checkSlashes  TRUE: if magic_quotes are ON, remove slashes (TRUE BY DEFAULT !); Deprecated, but left for extend B/C
	 * @return boolean                      TRUE: ok, FALSE: error on array binding
	 */
	public function bind( $array, $ignore='', $prefix = null, /** @noinspection PhpUnusedParameterInspection */ $checkSlashes = true  )
	{
		return $this->_table->bind( $array, $ignore, $prefix );
	}

	/**
	 * Method to get the Database Driver Interface object corresponding to $this table.
	 *
	 * @return  DatabaseDriverInterface  The internal database driver object.
	 */
	public function getDbo()
	{
		return $this->_table->getDbo();
	}

	/**
	 * Gets the last error message
	 *
	 *@return string  Returns the error message
	 */
	public function getError()
	{
		if ( $this->_error ) {
			return $this->_error;
		}

		return $this->_table->getError();
	}

	/**
	 * Sets the error message
	 *
	 * @param  string  $message  The error message
	 * @return                   $this Table object for chaining
	 */
	public function setError( $message )
	{
		return $this->_table->setError( $message );
	}

	/**
	 * After store() this function may be called to get a result information message to display.
	 * Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore( )
	{
		return $this->_table->cbResultOfStore();
	}

	/**
	 * Gets the name of the table
	 *
	 * @return string
	 */
	public function getTableName( )
	{
		return $this->_table->getTableName();
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
		return $this->_table->getKeyName( $multiple );
	}

	/**
	 * Returns the primary keys as an array( 'keyNanme' => typedValue ) where typedValue can 'string' or 'int'.
	 *
	 * @return array
	 */
	public function getPrimaryKeysTypes( )
	{
		return $this->_table->getPrimaryKeysTypes();
	}


	/**
	 * Tells if this Table has the $feature
	 * Special Features: 'ordered'
	 *
	 * @param  string  $feature   Feature to check
	 * @param  string  $forField  [optional] Field of Table with that feature
	 * @return boolean
	 */
	public function hasFeature( $feature, $forField = null )
	{
		return $this->_table->hasFeature( $feature, $forField );
	}

	/**
	 * ORDERING feature: Move the entry into the direction corresponding to a given ordering
	 *
	 * @param  int     $direction  Direction to move the entry: +1 at same ordering than next object, -1 at same as previous, 0 keep at same
	 * @param  string  $where      This is expected to be a valid (and safe!) SQL expression (e.g. to reorder within a category)
	 * @param  string  $ordering   Ordering column name
	 * @return void
	 *
	 * @throws \UnexpectedValueException
	 * @throws \Exception
	 */
	public function move( $direction, $where = '', $ordering = 'ordering' )
	{
		$this->_table->move( $direction, $where, $ordering );
	}

	/**
	 * ORDERING feature: Compacts the ordering sequence of the selected records
	 *
	 * @param  string  $where     Additional where query to limit ordering to a particular subset of records
	 * @param  array   $cIds      Ids of table key ids which should preserve their position (in addition of the negative positions)
	 * @param  string  $ordering  Name of ordering column in table
	 * @return boolean            TRUE success, FALSE failed, with error of database updated.
	 *
	 * @throws \UnexpectedValueException
	 */
	public function updateOrder( $where = '' , $cIds = null, $ordering = 'ordering' )
	{
		return $this->_table->updateOrder( $where, $cIds, $ordering );
	}

	/**
	 * CHECKOUT feature: Tests if item is checked out
	 *
	 * @param  int $userId User-id
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function isCheckedOut( $userId = 0 )
	{
		return $this->_table->isCheckedOut( $userId );
	}

	/**
	 * CHECKOUT feature: Checkout object from database
	 *
	 * @param  int      $who
	 * @param  int      $oid
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function checkout( $who, $oid = null )
	{
		return $this->_table->checkout( $who, $oid );
	}

	/**
	 * CHECKOUT feature: Check-in object to database
	 *
	 * @param  int      $oid
	 * @return boolean
	 *
	 * @throws \UnexpectedValueException
	 */
	public function checkin( $oid = null )
	{
		return $this->_table->checkin( $oid );
	}
}
