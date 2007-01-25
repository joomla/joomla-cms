<?php
/**
* @version		$Id: output.php 6138 2007-01-02 03:44:18Z eddiea $
* @package		Joomla.Framework
* @subpackage	Cache
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Joomla! Cache callback type object
 *
 * @author		Louis Landry <louis.landry@joomla.org>
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
	function call()
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
	function get( $callback, $args )
	{
		// Generate an ID
		$id = $this->_makeId($callback, $args);

		// Get the storage handler and get callback cache data by id and group
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			$data = $handler->get($id, 'callback', (isset($this->_options['checkTime']))? $this->_options['checkTime'] : true);
		}

		if ($data !== false) {
			$cached = unserialize( $data );
			$output = $cached['output'];
			$result = $cached['result'];
		} else {
			ob_start();
			ob_implicit_flush( false );

			// Now we need to determine the callback type and execute the callback accordingly
			if (is_array( $callback )) {
				// We have a standard php callback array -- easy
				$result = call_user_func_array( $callback, $args );
			} elseif (strstr( $callback, '::' )) {
				// This is shorthand for a static method callback classname::methodname
				list( $class, $method ) = explode( '::', $callback );
				$result = call_user_func_array( array( trim($class), trim($method) ), $args );
			} elseif (strstr( $callback, '->' )) {
				/*
				 * This is a really not so smart way of doing this... we provide this for backward compatability but this
				 * WILL!!! disappear in a future version.  If you are using this syntax change your code to use the standard
				 * PHP callback array syntax: <http://php.net/callback>
				 *
				 * We have to use some silly global notation to pull it off and this is very unreliable
				 */
				list( $object_123456789, $method ) = explode('->', $callback);
				global $$object_123456789;
				$result = call_user_func_array( array( $$object_123456789, $method ), $args );
			} else {
				// We have just a standard function -- easy
				$result = call_user_func_array( $callback, $args );
			}

			$output = ob_get_contents();
			ob_end_clean();

			$cached = array();
			$cached['output'] = $output;
			$cached['result'] = $result;
			// Store the cache data
			$this->_store($id, 'callback', $cached);
		}

		echo $output;
		return $result;
	}

	/**
	 * Store the cached data by id and group
	 *
	 * @access	public
	 * @param	string	$id		The cache data id
	 * @param	string	$group	The cache data group
	 * @param	mixed	$data	The data to store
	 * @return	boolean	True if cache stored
	 * @since	1.5
	 */
	function _store($id, $group, $data)
	{
		// Get the storage handler and store the cached data
		$handler =& $this->_getStorageHandler();
		if (!JError::isError($handler)) {
			return $handler->store($id, $group, serialize($data));
		}
		return false;
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
	function _makeId($callback, $args)
	{
		/*
		 * @todo	We need to serialize the callback data as well...
		 */
		return md5(serialize($args));
	}
}
?>