<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/29/14 5:09 PM $
* @package CBLib\Registry
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Registry;

use CBLib\Input\Get;

defined('CBLIB') or die();

/**
 * CBLib\Registry\Parameters Class implementation
 * 
 */
abstract class ParametersStore implements ParamsInterface
{
	/**
	 * The parsed params
	 * @var  array  The parsed params
	 */
	protected $params   	=   array();

	/**
	 * Parent Getter for default values
	 * @var GetterInterface
	 */
	protected $parent   	=   null;

	/**
	 * Other namespaces storage
	 *
	 * @var ParamsInterface[]
	 */
	protected $namespaces	=	array();

	/**
	 * Default type for get() method (null = raw, or GetterInterface::COMMAND
	 * @var string|null
	 */
	protected $defaultGetType	=	null;

	/**
	 * The source is GPC (Get Post Cookies) with escapings and needs stripslashes
	 * Will be deprecated with PHP 5.4 minimum
	 * @var boolean
	 */
	protected $srcGpc           =   false;

	/**
	 * Gets a clean param value
	 *
	 * @param  string|string[]        $key      Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param  mixed|GetterInterface  $default  [optional] Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param  string|array           $type     [optional] default: null: raw. Or const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @return string|array
	 *
	 * @throws \InvalidArgumentException        If namespace doesn't exist
	 */
	public function get( $key, $default = null, $type = null )
	{
		if ( is_array( $key ) ) {
			$va			=	array();
			foreach ( $key as $k ) {
				$va[$k]	=	$this->get( $k, is_array( $default ) ? $default[$k] : $default, is_array( $type ) ? $type[$k] : $type );
			}
			return $va;
		}

		// Check for namespaced get( 'namespace/key' ):
		if ( strpos( $key, '/' ) !== false )
		{
			list( $namespace, $subKey )		=	explode( '/', $key, 2 );

			return $this->getNamespaceRegistry( $namespace )
				->get( $subKey, $default, $type );
		}

		// Check in parent if not existing:
		if ( $this->parent && ! $this->hasInThis( $key ) )
		{
			return $this->parent->get( $key, $default, $type );
		}

		// Get value in this Parameters:
		return Get::get( $this->params, $key, $default, $type === null ? $this->defaultGetType : $type, $this->srcGpc );
	}

	/**
	 * Get sub-parameters
	 *
	 * @param   string           $key  Name of index or name-encoded parameters array selection, e.g. a.b.c
	 * @return  ParamsInterface        Sub-Parameters or empty array() added to tree if not existing
	 */
	public function subTree( $key )
	{
		$subParametersArray	=	Get::subTree( $this->params, $key );

		/** @var self $subTree */
		$subTree			=	new static();
		$subTree->setAsReferenceToArray( $subParametersArray );

		return $subTree;
	}

	/**
	 * Sets $this Parameters to reflect $array Read+Write
	 *
	 * @param  $array  Array by reference
	 */
	protected function setAsReferenceToArray( &$array )
	{
		$this->params		=&	$array;
	}

	/**
	 * Sets a value to a param
	 *
	 * @param  string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @param  string  $value  The value of the parameter
	 * @return void
	 *
	 * @throws \InvalidArgumentException  If $key has a namespace/ in it.
	 */
	public function set( $key, $value )
	{
		// Check for namespaced set( 'namespace/key' ) which we do not allow:
		if ( strpos( $key, '/' ) !== false )
		{
			throw new \InvalidArgumentException( 'Invalid domain-based key given to ' . __CLASS__ . '::' .  __FUNCTION__ );
		}

		Get::set( $this->params, $key, $value );
	}

	/**
	 * Check if a parameters path exists.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function has( $key )
	{
		// Check for namespaced get( 'namespace/key' ):
		if ( strpos( $key, '/' ) !== false )
		{
			list( $namespace, $subKey )		=	explode( '/', $key, 2 );

			return $this->getNamespaceRegistry( $namespace )
				->has( $subKey );
		}

		return $this->hasInThis( $key )
		|| ( $this->parent && $this->parent->has( $key ) );
	}

	/**
	 * Check if a parameters path exists without checking parents.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function hasInThis( $key )
	{
		return Get::has( $this->params, $key );
	}

	/**
	 * Un-Sets a param
	 *
	 * @param  string  $key    The name of the param
	 * @return void
	 */
	public function unsetEntry( $key )
	{
		unset( $this->params[$key] );
	}

	/**
	 * Sets a default value to param if not already assigned
	 *
	 * @param  string $key    The name of the parameter
	 * @param  string $value  The default value of the parameter to set if not already set
	 * @return void
	 */
	public function def( $key, $value )
	{
		$this->set( $key, $this->get( $key, $value ) );
	}

	/**
	 * Transforms the existing params to a JSON string
	 *
	 * @return string
	 */
	public function asJson()
	{
		return json_encode( $this->asArray() );
	}

	/**
	 * Returns an array of all current params
	 *
	 * @return array
	 */
	public function asArray( )
	{
		return $this->params;
	}

	/**
	 * Empties the Parameters
	 *
	 * @return  static  $this for chaining with ->load()
	 */
	public function reset( )
	{
		$this->params    =   array();
		return $this;
	}

	/**
	 * Adds loading of a associative array of values, or an hierarchical object, or a JSON string into the params
	 * Does not reset the Parameters, to reset, chain with ->reset()->load()
	 *
	 * @param   string|array|object|ParamsInterface  $jsonStringOrArrayOrObjectOrParameters  Associative array of values or Object to load
	 * @return  void
	 */
	public function load( $jsonStringOrArrayOrObjectOrParameters )
	{
		if ( is_string( $jsonStringOrArrayOrObjectOrParameters ) ) {
			// Parse JSON string:
			$jsonStringOrArrayOrObjectOrParameters  =   $this->parse( $jsonStringOrArrayOrObjectOrParameters );
		} elseif ( $jsonStringOrArrayOrObjectOrParameters instanceof ParamsInterface ) {
			// Get Array from Parameters:
			$jsonStringOrArrayOrObjectOrParameters  =   $jsonStringOrArrayOrObjectOrParameters->asArray();
		}
		// Bind Array of Object:
		$this->bindData( $this->params, $jsonStringOrArrayOrObjectOrParameters );
	}


	/**
	 * Sets the parent of $this
	 *
	 * @param   HierarchyInterface  $parent  The parent of this object
	 * @return  void
	 */
	public function setParent( HierarchyInterface $parent )
	{
		$this->parent	=	$parent;
	}

	/**
	 * Gets the parent of $this
	 *
	 * @return  HierarchyInterface  The parent of this object
	 */
	public function getParent( )
	{
		return $this->parent;
	}

	/**
	 * Sets the namespace $name of $this Parameters to be $registry
	 *
	 * @param  string           $name      Namespace of the registry
	 * @param  ParamsInterface  $registry  The corresponding registry
	 * @return self                        For chaining
	 */
	public function setNamespaceRegistry( $name, ParamsInterface $registry )
	{
		$this->namespaces[$name]	=	$registry;

		return $this;
	}

	/**
	 * Gets the namespaced Parameters of $this
	 *
	 * @param  string           $name  Namespace of the parameters
	 * @return ParamsInterface         The corresponding parameters
	 */
	public function getNamespaceRegistry( $name )
	{
		if ( $this->hasNamespaceRegistry( $name ) ) {
			return $this->namespaces[$name];
		}

		return new static();
	}

	/**
	 * Checks if namespaced $name Parameters exist
	 *
	 * @param  string   $name  Namespace of the parameters
	 * @return boolean         True: exists
	 */
	public function hasNamespaceRegistry( $name )
	{
		return isset( $this->namespaces[$name] );
	}

	/**
	 * \Serializable Interface implementation:
	 * Gets a String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.serialize.php
	 *
	 * @return string the string representation of the object or null
	 */
	public function serialize()
	{
		return $this->asJson();
	}

	/**
	 * \Serializable Interface implementation:
	 * Constructs the object from the serialized String representation of object
	 *
	 * @link http://php.net/manual/en/serializable.unserialize.php
	 *
	 * @param  string  $serialized  The string representation of the object.
	 * @return void
	 */
	public function unserialize( $serialized )
	{
		$this->reset()->load( $serialized );
	}

	/**
	 * Magic function that is useful to automatically transform to JSON string when needed
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->asJson();
	}

	/**
	 * Countable Interface implementation
	 *
	 * @param  int  $mode  COUNT_NORMAL or COUNT_RECURSIVE
	 * @return int
	 */
	public function count( $mode = COUNT_NORMAL )
	{
		return count( $this->asArray(), $mode );
	}

	/**
	 * Traversable Interface implementation
	 *
	 * @return \Traversable
	 */
	public function getIterator( )
	{
		return new ParametersIterator( $this->asArray(), 0, $this );
	}

	/**
	 * ArrayAccess Interface implementation: Sets an index
	 *
	 * @param  string  $offset
	 * @param  mixed   $value
	 * @return void
	 */
	public function offsetSet( $offset, $value )
	{
		if ( is_null( $offset ) ) {
			$this->params[] = $value;
		} else {
			$this->set( $offset, $value );
		}
	}

	/**
	 * ArrayAccess Interface implementation: Checks if an index isset
	 *
	 * @param  string  $offset
	 * @return bool
	 */
	public function offsetExists( $offset )
	{
		return $this->has( $offset );
	}

	/**
	 * ArrayAccess Interface implementation: Unsets an index
	 * @deprecated Temporary for migration
	 *
	 * @param  string  $offset
	 * @return void
	 */
	public function offsetUnset( $offset )
	{
		$this->unsetEntry( $offset );
	}

	/**
	 * ArrayAccess Interface implementation: Gets an index value
	 *
	 * @param  string  $offset
	 * @return mixed
	 *
	 * @throws \UnexpectedValueException
	 */
	public function offsetGet( $offset )
	{
		if ( ! isset($this->params[$offset]) ) {
			return $this->get( $offset );
		}

		if ( is_string( $this->params[$offset] ) ) {
			return $this->get( $offset );
		}

		if ( is_array( $this->params[$offset] ) ) {
			return $this->subTree( $offset );
		}

		if ( is_int( $this->params[$offset] ) ) {
			return $this->get( $offset, null, GetterInterface::INT );
		}

		if ( is_float( $this->params[$offset] ) ) {
			return $this->get( $offset, null, GetterInterface::FLOAT );
		}

		if ( is_object( $this->params[$offset] ) ) {
			return $this->params[$offset];
		}

		throw new \UnexpectedValueException( __CLASS__ . '::' . __FUNCTION__ . ': Unexpected type for array offset getter: ' . gettype( $this->params[$offset] ) );
	}

	/**
	 * Parse an JSON (PHP >=5.2) string or an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @param  string|array  $txt  The json or ini string or nested array of values
	 * @return array
	 */
	protected function parse( $txt )
	{
		if ( is_string( $txt ) && strlen( $txt ) > 0 ) {
			if ( ( $txt[0] === '{' ) || ( $txt[0] === '[' ) ) {
				return json_decode( $txt, true );
			}

			// Old INI way to read legacy params only:
			$lines								=	explode( "\n", $txt );
			$array								=	array();

			foreach ( $lines as $line ) {
				// ignore comments:
				if ( $line && ( $line[0] == ';' ) ) {
					continue;
				}

				$line							=	trim( $line );

				if ( $line == '' ) {
					continue;
				}

				if ( $line && ( $line[0] == '[' ) && ( $line[strlen($line)-1] == ']' ) ) {
					continue;
				}

				if ( false !== ( $pos = strpos( $line, '=' ) ) ) {
					$property					=	trim( substr( $line, 0, $pos ) );

					if ( ( substr( $property, 0, 1 ) == '"' ) && ( substr( $property, -1 ) == '"' ) ) {
						$property				=	stripcslashes( substr( $property, 1, ( count( $property ) - 2 ) ) );
					}

					$value						=	trim( substr( $line, ( $pos + 1 ) ) );

					if ( ( substr( $value, 0, 1 ) == '"' ) && ( substr( $value, -1 ) == '"' ) ) {
						$value					=	stripcslashes( substr( $value, 1, ( count( $value ) - 2 ) ) );
					}

					$value						=	str_replace( array( '\n', '\r', '\\\\' ), array( "\n", "\r", '\\' ), $value );
					$separator					=	strpos( $value, '|**|' );

					if ( $separator !== false ) {
						if ( $separator === 0 ) {
							// indexed array:
							$parts				=	explode( '|**|', substr( $value, 4 ) );
							$r					=	array();

							foreach ( $parts as $pv ) {
								$p				=	explode( '=', $pv, 2 );

								if ( isset( $p[1] ) ) {
									$r[$p[0]]	=	$p[1];
								}
							}

							$value				=	$r;
						} else {
							// non-indexed array:
							$value				=	explode( '|*|', $value );
						}
					}

					$array[$property]			=	$value;
				}
			}

			return $array;
		}

		if ( is_array( $txt ) ) {
			return $txt;
		}

		return array();
	}

	/**
	 * Method to recursively bind data to a parent object.
	 *
	 * @param   array         $parent  Parent array to which to add the data values.
	 * @param   array|object  $data    Array or object of data to bind to parent object.
	 * @return  void
	 */
	protected function bindData( &$parent, $data )
	{
		if ( is_object( $data ) ) {
			$data               =   get_object_vars( $data );
		}

		foreach ( (array) $data as $k => $v ) {
			if ( ( is_array( $v ) && ! isset( $v[0] ) ) || is_object( $v ) ) {
				if ( ! isset( $parent[$k] ) ) {
					$parent[$k]     =   array();
				}
				$this->bindData( $parent[$k], $v );
			} else {
				$parent[$k]     =   $v;
			}
		}
	}
}
