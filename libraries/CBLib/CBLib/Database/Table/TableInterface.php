<?php
/**
 * CBLib, Community Builder Library(TM)
 *
 * @version       $Id: 5/9/14 3:26 PM $
 * @package       ${NAMESPACE}
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */
namespace CBLib\Database\Table;

use CBLib\Database\DatabaseDriverInterface;
use CBLib\Registry\GetterInterface;
use CBLib\Registry\ParamsInterface;
use CBLib\Registry\SetterInterface;


/**
 * CBLib\Database\Table Class implementation
 *
 */
interface TableInterface extends GetterInterface, SetterInterface /* Always check for future compatibility with ParamsInterface */
{
	/**
	 * Tells if this Table has the $feature (e.g. 'ordering', 'checkout')
	 * any override must return parent::hasFeature( $feature, $forField ) instead of false.
	 *
	 * @param  string $feature  Feature to check
	 * @param  string $forField [optional] Field of Table with that feature (if a feature, e.g. ordering, supports multiple fields)
	 * @return boolean            True: has the feature, False: has it not
	 */
	public function hasFeature( $feature, $forField = null );

	/**
	 * Loads a row from the database into $this object by primary key or specific (typed) keys
	 * E.g. load->( array( 'name' => 'Smith', 'age' => 25 ) ).
	 *
	 * @param  int|array $keys [Optional]: Primary key value or array of primary keys to match. If not specified, the value of current key is used
	 * @return boolean            Result from the database operation
	 *
	 * @throws  \InvalidArgumentException
	 * @throws  \RuntimeException
	 * @throws  \UnexpectedValueException
	 */
	public function load( $keys = null );

	/**
	 * If a table key (id) is NULL : inserts a new row
	 * otherwise updates existing row in the database table
	 * If table has a single primary key, updates the primary key in $this with the new value
	 *
	 * Can be overridden or overloaded by the child class
	 *
	 * @param  boolean $updateNulls TRUE: null object variables are also updated, FALSE: not.
	 * @return boolean                TRUE if successful otherwise FALSE
	 */
	public function store( $updateNulls = false );

	/**
	 * After store() this function may be called to get a result information message to display.
	 * Override if it is needed.
	 *
	 * @return string|null  STRING to display or NULL to not display any information message (Default: NULL)
	 */
	public function cbResultOfStore();

	/**
	 * Resets public properties
	 *
	 * @param  mixed $value The value to set all properties to, default is null
	 * @return void
	 */
	public function reset( $value = null );

	/**
	 * Gets the value of the class variable
	 *
	 * @param  string        $var      The name of the class variable
	 * @param  mixed         $default  The value to return if no value is found
	 * @param  string|array  $type     [optional] Default: null: GetterInterface::COMMAND. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return mixed                   The value of the class var (or null if no var of that name exists)
	 */
	public function get( $var, $default = null, $type = null );

	/**
	 * Sets the new value of the class variable
	 *
	 * @param  string $var    The name of the class variable
	 * @param  mixed  $value  The new value to assign to the variable
	 */
	public function set( $var, $value );

	/**
	 * Returns an array of public properties names
	 *
	 * @return array
	 */
	public function getPublicProperties();

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before delete()
	 *
	 * @param  int $oid key index (only int supported here)
	 * @return boolean
	 */
	public function canDelete( $oid = null );

	/**
	 * Deletes this record (no checks)
	 * canDelete should be called first to check if there are no orphan dependencies left.
	 *
	 * @param  int $oid Key id of row to delete (otherwise it's the one of $this) (only int supported here)
	 * @return boolean         TRUE if OK, FALSE if error
	 */
	public function delete( $oid = null );

	/**
	 * Generic check for whether dependencies exist for this object in the db schema
	 * Should be overridden if checks need to be done before copy()
	 *
	 * @param  null|TableInterface  $object The object being copied to otherwise $this
	 * @return boolean                      True: Can Copy, False: Cannot Copy
	 */
	public function canCopy( $object = null );

	/**
	 * Copies this record (no checks)
	 * canCopy should be called first to check if a copy is possible.
	 *
	 * @param  null|TableInterface|self  $object  The object being copied otherwise create new object and add $this
	 * @return self|boolean                       OBJECT: The new object copied successfully, FALSE: Failed to copy
	 */
	public function copy( $object = null );

	/**
	 * Loads an array of typed objects of a given class (same class as current object by default)
	 * which inherit from this class.
	 *
	 * @param  string $class          [optional] class name
	 * @param  string $key            [optional] key name in db to use as key of array
	 * @param  array  $additionalVars [optional] array of string additional key names to add as vars to object
	 * @return array                   of objects of the same class (empty array if no objects)
	 */
	public function loadTrueObjects( $class = null, $key = "", $additionalVars = array() );

	/**
	 *    Check values before store method  (override if needed)
	 *
	 * @return boolean  TRUE if the object is safe for saving
	 */
	public function check();

	/**
	 * Copy the named array or object content into this object as vars
	 * only existing vars of object are filled.
	 * When undefined in array, object variables are kept.
	 *
	 * WARNING: DOES addslashes / escape BY DEFAULT
	 *
	 * Can be overridden or overloaded.
	 *
	 * @param  array|object $array        The input array or object
	 * @param  string       $ignore       Fields to ignore
	 * @param  string       $prefix       Prefix for the array keys
	 * @return boolean                      TRUE: ok, FALSE: error on array binding
	 */
	public function bind( $array, $ignore = '', $prefix = null );

	/**
	 * Method to get the Database Driver Interface object corresponding to $this table.
	 *
	 * @return  DatabaseDriverInterface  The internal database driver object.
	 */
	public function getDbo();

	/**
	 * Gets the name of the table
	 *
	 * @return string
	 */
	public function getTableName();

	/**
	 * Gets the primary key(s) column names.
	 * If you need type of the key too, use getPrimaryKeysTypes() instead.
	 *
	 * @param  boolean $multiple [optional ] FALSE (default): gets only the first primary key column name as a string, TRUE: gets a name-keyed array of the key types ('string' or 'int')
	 * @return string|array
	 */
	public function getKeyName( $multiple = false );

	/**
	 * Returns the primary keys as an array( 'keyNanme' => typedValue ) where typedValue can 'string' or 'int'.
	 *
	 * @return array
	 */
	public function getPrimaryKeysTypes();

	/**
	 * Gets the last error message
	 *
	 * @return string  Returns the error message
	 */
	public function getError();

	/**
	 * Sets the error message
	 *
	 * @param  string $message The error message
	 * @return                   $this Table object for chaining
	 */
	public function setError( $message );
}