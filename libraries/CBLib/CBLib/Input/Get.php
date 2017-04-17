<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/24/13 7:36 PM $
* @package CBLib\Input
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Input;

use CBLib\Registry\GetterInterface;

defined('CBLIB') or die();

/**
 * CBLib\Input\Get Class implementation
 * 
 */
class Get {
	/**
	 * Cleaning input method
	 *
	 * @param   array                  $input         Input array
	 * @param   string|string[]        $key           Name of index or array of names of indexes, each with name or input-name-encoded array selection, e.g. a.b.c
	 * @param   mixed|GetterInterface  $default       Default value, or, if instanceof GetterInterface, parent GetterInterface for the default value
	 * @param   string|array|null      $type          [optional] Const int GetterInterface::COMMAND|GetterInterface::INT|... or array( const ) or array( $key => const )
	 * @param   bool                   $stripSlashes  [optional] Strips slashes
	 *
	 * @return  string|array|bool|float|int|null|mixed|number
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function get( $input, $key, $default, $type = null, $stripSlashes = false )
	{
		if ( is_array( $key ) ) {
			$va			=	array();
			foreach ( $key as $k ) {
				$va[$k]	=	static::get( $input, $k, is_array( $default ) ? $default[$k] : $default, is_array( $type ) ? $type[$k] : $type, $stripSlashes );
			}
			return $va;
		}

		if ( isset( $input[$key] ) ) {

			if ( is_array( $input[$key] ) ) {
				if ( is_array( $type ) || $type === null || $type === GetterInterface::RAW ) {

					$ret			=	array();
					foreach ( array_keys( $input[$key] ) as $k ) {
						$kType      =   ( ( ! is_array( $type ) ) && in_array( $type, array( null, GetterInterface::RAW ), true ) ? $type : ( isset( $type[$k] ) ? $type[$k] : ( isset( $type[0] ) ? $type[0] : false ) ) );
						if ( $kType !== false ) {
							$ret[$k] =	static::get( $input[$key], $k,
								is_array( $default ) ? ( isset( $default[$k] ) ? $default[$k] : null ) : $default,
								$kType, $stripSlashes );
						}
					}
					return $ret;

				} else {
					return $default;
				}
			} else {

				$v			=	$input[$key];

				if ( $stripSlashes ) {
					$v		=	static::stripslashesArray( $v );
				}

				if ( $type === null ) {
					return $v;
				} else {
					return static::clean( $v, $type );
				}
			}

		} elseif ( count( $subkeys = explode( '.', $key ) ) > 1 ) {

			$subParams          =&  $input;

			for ( $i = 0, $n = count( $subkeys ) - 1; $i < $n; $i++ ) {
				if ( ! isset( $subParams[$subkeys[$i]] ) ) {
					return $default;
				}

				if ( ! is_array( $subParams ) ) {
					// Throw exception as strings have ArrayAccess in php 5.5 (but is fatal error in PHP 5.3 and 5.4, and this is not the wanted behavior:
					// (string not translated, as this programming error could be very early in the process):
					throw new \InvalidArgumentException(
						strtr(
							   '[METHOD] trying to use non-array as array with key "[KEY]" at sub-key "[SUBKEY]"',
							   array( '[METHOD]' => __CLASS__ . '::' . __FUNCTION__, '[KEY]' => $key, '[SUBKEY]' => $subkeys[$i]
							 )
						)
					);
				}

				$subParams      =&  $subParams[$subkeys[$i]];
			}

			return static::get( $subParams, $subkeys[$i], $default, $type, $stripSlashes );

		}

		if ( $default instanceof GetterInterface ) {
			return $default->get( $key, null, $type, $stripSlashes );
		}

		return $default;
	}

	/**
	 * Get sub-input array
	 *
	 * @param   array   $input  Input array
	 * @param   string  $key    Name of index or input-name-encoded array selection, e.g. a.b.c
	 * @return  array|boolean   Array of sub-input or boolean FALSE if not existing or not an array
	 */
	public static function & subTree( &$input, $key )
	{
		$subkeys			=	explode( '.', $key );

		$subParams          =&  $input;

		for ( $i = 0, $n = count( $subkeys ); $i < $n; $i++ ) {
			if ( ! isset( $subParams[$subkeys[$i]] ) ) {
				$subParams[$subkeys[$i]]	=	array();
			}
			$subParams      =&  $subParams[$subkeys[$i]];
		}

		return $subParams;
	}

	/**
	 * Sets a value to an input
	 *
	 * @param   array   $input  Input array
	 * @param   string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @param   string  $value  The value of the parameter
	 * @return  void
	 */
	public static function set( &$input, $key, $value )
	{
		$subkeys        =   explode( '.', $key );

		$subParams      =&  $input;

		for ( $i = 0, $n = count( $subkeys ) - 1; $i < $n; $i++ ) {
			if ( ! isset( $subParams[$subkeys[$i]] ) ) {
				$subParams[$subkeys[$i]] = array();
			}

			$subParams  =&  $subParams[$subkeys[$i]];
		}

		$subParams[$subkeys[$i]]    =   $value;
		unset( $subParams );
	}

	/**
	 * Unsets an input
	 *
	 * @param   array   $input  Input array
	 * @param   string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @return  void
	 */
	public static function unsetKey( &$input, $key )
	{
		$subkeys        =   explode( '.', $key );

		$subParams      =&  $input;

		for ( $i = 0, $n = count( $subkeys ) - 1; $i < $n; $i++ ) {
			if ( ! isset( $subParams[$subkeys[$i]] ) ) {
				return;
			}

			$subParams  =&  $subParams[$subkeys[$i]];
		}

		unset( $subParams[$subkeys[$i]] );
		unset( $subParams );
	}

	/**
	 * Check if an input is set.
	 *
	 * @param   array   $input  Input array
	 * @param   string  $key    The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public static function has( $input, $key )
	{
		if ( isset( $input[$key] ) ) {
			return true;
		} elseif ( count( $subKeys = explode( '.', $key ) ) > 1 ) {
			$subParams          =&  $input;

			for ( $i = 0, $n = count( $subKeys ); $i < $n; $i++ ) {
				if ( ! isset( $subParams[$subKeys[$i]] ) ) {
					unset( $subParams );
					return false;
				}
				$subParams      =&  $subParams[$subKeys[$i]];
			}
			unset( $subParams );
			return true;
		}
		return false;
	}

	/**
	 * Cleans $value for $type
	 *
	 * @param  string|int|float|mixed  $value
	 * @param  string                  $type   Const int GetterInterface::COMMAND|GetterInterface::INT|...
	 * @return string|int|float|boolean|mixed
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function clean( $value, $type )
	{
		switch ( $type ) {
			case GetterInterface::COMMAND:
			case null:
				return preg_replace( '/[^A-Za-z0-9_\.-]/', '', $value );
			case GetterInterface::INT:
				return @ (int) $value;
			case GetterInterface::UINT:
				return @ abs( (int) $value );
			case GetterInterface::NUMERIC:
				return (string) preg_replace( '/^(\d*).*$/', '\1', $value );
			case GetterInterface::FLOAT:
				return (float) $value;
			case GetterInterface::BOOLEAN:
				return (bool) $value;
			case GetterInterface::STRING:
				return InjectionsFilter::filter( $value, 'text' );
			case GetterInterface::HTML:
				return InjectionsFilter::filter( $value, 'html' );
			case GetterInterface::BASE64:
				return (string) preg_replace( '/[^A-Z0-9\/+=]/i', '', $value );
				break;
			case GetterInterface::RAW:
				return $value;
			default:
				throw new \InvalidArgumentException( sprintf( 'Unknown Get::get type "%s"', preg_replace( '/[^A-Za-z0-9_\.-]/', '', $type ) ) );
		}
	}

	/**
	 * Typecasts an $array to an array of ints
	 *
	 * @param  array  $array  Input array
	 * @return array          Output array
	 */
	public static function arrayToIntegers( $array )
	{
		return array_map(
			function ( $v )
			{
				return (int) $v;
			},
			$array
		);
	}

	/**
	 * Internale function to stripslashes of string or array
	 * @param  string|array  $value  With slashes
	 * @return string|array          Without slashes
	 *
	 * @throws \InvalidArgumentException
	 */
	protected static function stripslashesArray( $value )
	{
		if ( is_string( $value ) ) {
			return stripslashes( $value );
		}

		if ( is_array( $value ) ) {
			foreach ( $value as &$v ) {
				$v		=	self::stripslashesArray( $v );
			}

			return $value;
		}

		throw new \InvalidArgumentException( __FUNCTION__ . ' has unexpected value type ' . gettype( $value ) );
	}
}
