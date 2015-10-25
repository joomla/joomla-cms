<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 8/29/14 5:07 PM $
* @package CBLib\Registry
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Registry;

defined('CBLIB') or die();

interface ParamsInterface extends GetterInterface, SetterInterface, HierarchyInterface, NamespaceInterface, \ArrayAccess, \Countable, \IteratorAggregate, \Serializable
{
	/**
	 * Check if a registry path exists without checking parents.
	 *
	 * @param   string  $key  The name of the param or sub-param, e.g. a.b.c
	 * @return  boolean
	 */
	public function hasInThis( $key );

	/**
	 * Get sub-Registry
	 *
	 * @param   string  $key  Name of index or name-encoded registry array selection, e.g. a.b.c
	 * @return  static        Sub-Registry or empty array() added to tree if not existing
	 */
	public function subTree( $key );

	/**
	 * Un-Sets a param
	 *
	 * @param  string  $key    The name of the param
	 * @return void
	 */
	public function unsetEntry( $key );

	/**
	 * Sets a default value to param if not already assigned
	 *
	 * @param  string $key    The name of the parameter
	 * @param  string $value  The default value of the parameter to set if not already set
	 * @return void
	 */
	public function def( $key, $value );

	/**
	 * Transforms the existing params to a JSON string
	 *
	 * @return string
	 */
	public function asJson();

	/**
	 * Returns an array of all current params
	 *
	 * @return array
	 */
	public function asArray( );

	/**
	 * Empties the Registry
	 *
	 * @return static  $this for chaining with ->load()
	 */
	public function reset( );

	/**
	 * Adds loading of a associative array of values, or an hierarchical object, or a JSON string into the params
	 * Does not reset the Registry, to reset, chain with ->reset()->load()
	 *
	 * @param   string|array|object|Registry $jsonStringOrArrayOrObjectOrRegistry  Associative array of values or Object to load
	 * @return  void
	 */
	public function load( $jsonStringOrArrayOrObjectOrRegistry );
}
