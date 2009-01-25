<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Cache
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Joomla! Cache callback type object
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheCallback extends JCache
{
	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * Since arguments to this function are read with func_get_args you can pass any number of arguments to this method
	 * as long as the first argument passed is the callback definition.
	 *
	 * The callback definition can be in several forms:
	 * 	- Standard PHP Callback array <http://php.net/callback> [recommended]
	 * 	- Function name as a string eg. 'foo' for function foo()
	 * 	- Static method name as a string eg. 'MyClass::myMethod' for method myMethod() of class MyClass
	 *
	 * @access	public
	 * @return	mixed	Result of the callback
	 * @since	1.5
	 */
	public function call()
	{
		// Get callback and arguments
		$args		= func_get_args();
		$callback	= array_shift($args);

		return $this->get( $callback, $args );
	}

	/**
	 * Executes a cacheable callback if not found in cache else returns cached output and result
	 *
	 * @access	public
	 * @param	mixed	Callback or string shorthand for a callback
	 * @param	array	Callback arguments
	 * @return	mixed	Result of the callback
	 * @since	1.5
	 */
	public function get( $callback, $args, $id=false )
	{
		// Normalize callback
		if (is_callable( $callback )) {
			// We have a standard php callback array -- do nothing
		} elseif (strstr( $callback, '::' )) {
			// This is shorthand for a static method callback classname::methodname
			list( $class, $method ) = explode( '::', $callback );
			$callback = array( trim($class), trim($method) );
		} else {
			throw new JException('Callback not supported', 0, E_WARNING, $callback, true);
		}

		if (!$id) {
			// Generate an ID
			$id = $this->_makeId($callback, $args);
		}

		// Get the storage handler and get callback cache data by id and group
		$data = parent::get($id);
		if ($data !== false) {
			$cached = unserialize( $data );
			$output = $cached['output'];
			$result = $cached['result'];
		} else {
			ob_start();
			ob_implicit_flush( false );

			$result = call_user_func_array($callback, $args);
			$output = ob_get_contents();

			ob_end_clean();

			$cached = array();
			$cached['output'] = $output;
			$cached['result'] = $result;
			// Store the cache data
			$this->store(serialize($cached), $id);
		}

		echo $output;
		return $result;
	}

	/**
	 * Generate a callback cache id
	 *
	 * @access	private
	 * @param	callback	$callback	Callback to cache
	 * @param	array		$args	Arguments to the callback method to cache
	 * @return	string	MD5 Hash : function cache id
	 * @since	1.5
	 */
	protected function _makeId($callback, $args)
	{
		if(is_array($callback) && is_object($callback[0])) {
			$vars = get_object_vars($callback[0]);
			$vars[] = strtolower(get_class($callback[0]));
			$callback[0] = $vars;
		}
		return md5(serialize(array($callback, $args)));
	}
}
