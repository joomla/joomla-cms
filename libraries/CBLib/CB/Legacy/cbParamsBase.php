<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 6/16/14 4:25 PM $
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/


defined('CBLIB') or die();

/**
 * cbParamsBase Class implementation
 * Parameters handler
 * @deprecated 2.0 Use CBLib\Registry\Registry instead
 * @see \CBLib\Registry\Registry
 */
class cbParamsBase
{
	/**
	 * The objects for the associations
	 * @var cbObject
	 */
	public $_params		=	null;
	/**
	 * The raw params string
	 * @var string
	 */
	public $_raw		=	null;

	/**
	 * Constructor (keep old constructor name for
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string  $paramsValues  The raw parms text
	 */
	public function __construct( $paramsValues )
	{
		$this->_params	=	$this->parse( $paramsValues );
		$this->_raw		=	$paramsValues;
	}

	/**
	 * Loads from the plugins database
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string   $element  The plugin element name
	 * @return boolean            true: could load, false: query error.
	 */
	public function loadFromDB( $element )
	{
		global $_CB_database;

		$_CB_database->setQuery( 'SELECT ' . $_CB_database->NameQuote( 'params' ) . ' FROM ' . $_CB_database->NameQuote( '#__comprofiler_plugin' )
			. ' WHERE ' . $_CB_database->NameQuote( 'element' ) . ' = ' . $_CB_database->Quote( $element ) );
		$text = $_CB_database->loadResult();

		$this->_params = $this->parse( $text );

		$this->_raw = $text;

		return ( $text !== null );
	}

	/**
	 * Transforms the existing params to a ini string
	 * @since 1.2.1
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @return string
	 */
	public function toIniString()
	{
		$txt			=	array();
		foreach ( get_object_vars( $this->_params ) as $k => $v ) {

			/** Workaround PHP bug https://bugs.php.net/bug.php?id=66961 : */
			$value		=	$v;

			if ( strstr( $value, "\n" ) ) {
				$value	=	str_replace( array( "\\", "\n", "\r" ), array( "\\\\", '\\n', '\\r'  ) , $value );
			}

			$txt[]		=	$k . '=' . $value;
		}

		return implode( "\n", $txt );
	}

	/**
	 * Returns an array of all current params
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @return array
	 */
	public function toParamsArray( )
	{
		/** Workaround PHP bug https://bugs.php.net/bug.php?id=66961 : */
		return $this->dereferenceArrayValues( get_object_vars( $this->_params ) );
	}

	/**
	 * Sets a value to a param
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string  $key    The name of the param
	 * @param  string  $value  The value of the parameter
	 * @return string  The set value
	 */
	public function set( $key, $value='' )
	{
		$this->_params->$key = $value;
		return $value;
	}

	/**
	 * Un-Sets a param
	 * @since 1.2.1CBLib\Registry\Registry
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string  $key    The name of the param
	 */
	public function unsetParam( $key )
	{
		unset( $this->_params->$key );
	}

	/**
	 * Sets a default value to param if not alreay assigned
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string  $key    The name of the param
	 * @param  string  $value  The value of the parameter
	 * @return string  The set value
	 */
	public function def( $key, $value = '' )
	{
		/** @noinspection PhpDeprecationInspection */
		return $this->set( $key, $this->get( $key, $value ) );
	}

	/**
	 * Gets a param value
	 * @deprecated 2.0 Use CBLib\Registry\Registry instead
	 *
	 * @param  string  $key      The name of the param
	 * @param  mixed   $default  The default value if not found (if array(), the return will be an array too)
	 * @return string|array
	 *
	 * @throws LogicException
	 */
	public function get( $key, $default = null )
	{
		if ( isset( $this->_params->$key ) ) {
			if ( is_array( $default ) && ! is_array( $this->_params->$key ) ) {
				if ( is_string( $this->_params->$key ) ) {
					if ( strpos( $this->_params->$key, '|**|' ) === 0 ) {
						// indexed array:
						$parts				=	explode( '|**|', substr( $this->_params->$key, 4 ) );
						$r					=	array();
						foreach ( $parts as $v ) {
							$p				=	explode( '=', $v, 2 );
							if ( isset( $p[1] ) ) {
								$r[$p[0]]	=	$p[1];
							}
						}
						return $r;
					} else {
						// non-indexed array:
						return explode( '|*|', $this->_params->$key );
					}
				} elseif ( is_object( $this->_params->$key ) ) {
					// Convert new-style-saved sub-params to old-style numerically-indexed array:
					/** Workaround PHP bug https://bugs.php.net/bug.php?id=66961 : */
					return $this->dereferenceArrayValues( get_object_vars( $this->_params->$key ) );
				} else {
					throw new LogicException( 'Impossible cbParamsBase::get() part reached' );
				}
			} else {
				return $this->_params->$key;
			}
		} else {
			$isArray		=	strpos( $key, '[' );
			if ( $isArray ) {
				// case of indexed arrays:
				$index	=	substr( $key, $isArray + 1, strpos( $key, ']' ) - $isArray -1 );
				/** @noinspection PhpDeprecationInspection */
				$arrayString =	$this->get( substr( $key, 0, $isArray ) );
				if ( is_array( $arrayString ) ) {
					if ( isset( $arrayString[$index] ) ) {
						return $arrayString[$index];
					}
				} else {
					if ( $arrayString && ( strpos( $arrayString, '|**|' ) === 0 ) ) {
						$parts	=	explode( '|**|', substr( $arrayString, 4 ) );
						foreach ( $parts as $v ) {
							$p	=	explode( '=', $v, 2 );
							if ( $p[0] == $index ) {
								if ( isset( $p[1] ) ) {
									return $p[1];
								}
							}
						}
					}
				}
			}
			return $default;
		}
	}

	/**
	 * Dereferences (e.g. get_object_vars()) array $in => &$v to return array $out => $v
	 * Needed Workaround PHP bug https://bugs.php.net/bug.php?id=66961
	 *
	 * @param  array  $in
	 * @return array
	 */
	private function dereferenceArrayValues( array $in )
	{
		$out			=	array();

		foreach ( $in as $k => $v ) {
			$out[$k]	=	$v;
		}

		return $out;
	}

	/**
	 * Parse an JSON (PHP >=5.2) string or an .ini string, based on phpDocumentor phpDocumentor_parse_ini_file function
	 *
	 * @param  mixed    $txt               The ini string (or, deprecated as works only for ini: array of lines)
	 * @param  boolean  $process_sections  Add an associative index for each section [in brackets]
	 * @param  boolean  $asArray           Returns an array instead of an object
	 * @return object|array
	 */
	protected function parse( $txt, $process_sections = false, $asArray = false )
	{
		if (is_string( $txt )) {
			if ( isset( $txt[0] ) && ( $txt[0] === '{' ) ) {
				// JSON encoding: requires PHP 5.2, and used in Joomla 1.6+:
				return json_decode( $txt, $asArray );
			}
			// ini string: rest of function is for INI string processing:
			$lines = explode( "\n", $txt );
		} else if (is_array( $txt )) {
			$lines = $txt;
		} else {
			$lines = array();
		}
		$obj = $asArray ? array() : new cbObject();

		$sec_name = '';
		$unparsed = 0;
		if (!$lines) {
			return $obj;
		}
		foreach ($lines as $line) {
			// ignore comments
			if ($line && $line[0] == ';') {
				continue;
			}
			$line = trim( $line );

			if ($line == '') {
				continue;
			}
			if ($line && $line[0] == '[' && $line[strlen($line) - 1] == ']') {
				$sec_name = substr( $line, 1, strlen($line) - 2 );
				if ($process_sections) {
					if ($asArray) {
						$obj[$sec_name] = array();
					} else {
						$obj->$sec_name = new cbObject();
					}
				}
			} else {
				if ( false !== ( $pos = strpos( $line, '=' ) ) ) {
					$property = trim( substr( $line, 0, $pos ) );

					if (substr($property, 0, 1) == '"' && substr($property, -1) == '"') {
						$property = stripcslashes(substr($property,1,count($property) - 2));
					}
					$value = trim( substr( $line, $pos + 1 ) );
					if ($value == 'false') {
						$value = false;
					}
					if ($value == 'true') {
						$value = true;
					}
					if (substr( $value, 0, 1 ) == '"' && substr( $value, -1 ) == '"') {
						$value = stripcslashes( substr( $value, 1, count( $value ) - 2 ) );
					}

					if ($process_sections) {
						$value = str_replace( array( '\n', '\r', '\\\\' ), array( "\n", "\r", '\\' ), $value );
						if ($sec_name != '') {
							if ($asArray) {
								$obj[$sec_name][$property] = $value;
							} else {
								$obj->$sec_name->$property = $value;
							}
						} else {
							if ($asArray) {
								$obj[$property] = $value;
							} else {
								$obj->$property = $value;
							}
						}
					} else {
						$value = str_replace( array( '\n', '\r', '\\\\' ), array( "\n", "\r", '\\' ), $value );
						if ($asArray) {
							$obj[$property] = $value;
						} else {
							$obj->$property = $value;
						}
					}
				} else {
					if ($line && trim($line[0]) == ';') {
						continue;
					}
					if ($process_sections) {
						$property = '__invalid' . $unparsed++ . '__';
						if ($process_sections) {
							if ($sec_name != '') {
								if ($asArray) {
									$obj[$sec_name][$property] = trim($line);
								} else {
									$obj->$sec_name->$property = trim($line);
								}
							} else {
								if ($asArray) {
									$obj[$property] = trim($line);
								} else {
									$obj->$property = trim($line);
								}
							}
						} else {
							if ($asArray) {
								$obj[$property] = trim($line);
							} else {
								$obj->$property = trim($line);
							}
						}
					}
				}
			}
		}
		return $obj;
	}
}
