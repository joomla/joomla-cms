<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/**
 * Class to support function caching
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.5
 */
class JCacheFunction extends JCache
{
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param array $options options
	 */
	function _construct($options) {
		parent::_construct($options);
	}

	/**
	 * Calls a cacheable function or method (or not if there is already a cache for it)
	 *
	 * Arguments of this method are read with func_get_args. So it doesn't appear
	 * in the function definition. Synopsis :
	 * call('functionName', $arg1, $arg2, ...)
	 * (arg1, arg2... are arguments of 'functionName')
	 *
	 * @access public
	 * @return mixed result of the function/method
	 */
	function call()
	{
		$array = func_get_args();
		$function = $array[0];
		unset( $array[0] );
		return $this->callId( $function, $array, serialize( $array ) );
	}

	/**
	 * Calls a cacheable function or method (or not if there is already a cache for it)
	 * and specify a specific id
	 *
	 * @access public
	 * @param string Function to call
	 * @param array  Argument of the function
	 * @param id	 Cache id
	 * @return mixed result of the function/method
	 */
	function callId( $target, $arguments, $id )
	{
		$id = $this->generateId($id); // Generate a cache id

		$data = $this->get( $id, $this->_defaultGroup, !$this->_validateCache );

		if ($data !== false)
		{
			$array = unserialize( $data );
			$output = $array['output'];
			$result = $array['result'];
		}
		else
		{
			ob_start();
			ob_implicit_flush( false );

			//$target = array_shift($arguments);
			if (strstr( $target, '::' )) { // classname::staticMethod
				list( $class, $method ) = explode( '::', $target );
				$result = call_user_func_array( array( trim($class), trim($method) ), $arguments );
			} else if (strstr( $target, '->' )) { // object->method
				// use a stupid name ($objet_123456789 because) of problems when the object
				// name is the same as this var name
				list( $object_123456789, $method ) = explode('->', $target);
				global $$object_123456789;
				$result = call_user_func_array( array( $$object_123456789, $method ), $arguments );
			} else { // function
				$result = call_user_func_array( $target, $arguments );
			}

			$output = ob_get_contents();
			ob_end_clean();

			$array['output'] = $output;
			$array['result'] = $result;
			$this->save( serialize( $array ), $id, $this->_defaultGroup );
		}

		echo $output;
		return $result;
	}
}
?>