<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 10/23/13 5:49 PM $
* @package CBLib\Registry
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Registry;

use CBLib\Database\Table\TableInterface;

defined('CBLIB') or die();

/**
 * CBLib\Registry\Registry Class implementation
 * 
 */
class Registry extends ParametersStore implements RegistryInterface
{
	/**
	 * Default type for get() method (null = raw, or GetterInterface::COMMAND
	 * @var string|null
	 */
	protected $defaultGetType	=	null;

	/**
	 * Storage
	 * @var object|TableInterface
	 */
	protected $storage			=   null;

	/**
	 * @var \comprofilerDBTable
	 * @deprecated Here only temporarily for CB 2.0 (CBSubs 3.0.0 backwards-compat-only)
	 */
	public $_tbl				=	null;

	/**
	 * Constructor
	 *
	 * @param   string|array|object|Registry  $paramsValues  Associative array of values, Object, or raw string of params to load
	 */
	public function __construct( $paramsValues = null )
	{
		$this->load( $paramsValues );
	}

	/**
	 * Gets a param value
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
		$value		=	parent::get( $key, $default, $type );

		// cbParamsBase B/C compatibility trick:
		if ( is_array( $default ) && ( ! is_array( $value ) ) ) {
			$value	=	explode( '|*|', $value );
		}

		return $value;
	}

	/**
	 * Sets the parent of $this
	 *
	 * @param   object  $storage  The parent of this object
	 * @return  void
	 */
	public function setStorage( $storage )
	{
		$this->storage	=	$storage;

		if ( $storage instanceof TableInterface ) {
			/** @noinspection PhpDeprecationInspection */
			$this->_tbl	=	$storage->getTableName();
		} elseif ( isset( $storage->_tbl ) ) {
			/** @noinspection PhpDeprecationInspection */
			$this->_tbl	=	$storage->_tbl;
		}
	}

	/**
	 * Gets the parent of $this
	 *
	 * @return  object  The parent of this object
	 */
	public function getStorage( )
	{
		return $this->storage;
	}

	/**
	 * Legacy functions for cbParamsBase compatibility as we replace them in CB 2.0 core. So plugins remain compatible:
	 * TODO Remove in CB 3.0.
	 */

	/**
	 * Transforms the existing params to a ini string
	 * Warning: These are CB ini strings!
	 *
	 * @deprecated 2.0 We do not need INI strings anymore for registries, use asJson() instead
	 *
	 * @return string
	 */
	public function toIniString()
	{
		$txt		=	array();
		foreach ( $this->asArray() as $k => $v ) {
			$v		=	str_replace( array( "\\", "\n", "\r" ), array( "\\\\", '\\n', '\\r'  ) , $v );
			$txt[]	=	$k . '=' . $v;
		}
		return implode( "\n", $txt );
	}

	/**
	 * Returns an array of all current params
	 * @deprecated 2.0 Use CBLib\Registry\Registry::asArray() instead
	 * @see \CBLib\Registry\Registry::asArray()
	 *
	 * @return array
	 */
	public function toParamsArray( )
	{
		return $this->asArray();
	}
}
