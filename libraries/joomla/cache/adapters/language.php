<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/


/**
 * Class to support language file caching
 *
 * @package		Joomla.Framework
 * @subpackage	Cache
 * @since		1.1
 */
class JCacheLanguage extends JCache
{
	/**
	 * Constructor
	 *
	 * @access protected
	 * @param array $options options
	 */
	function _construct($options)  {
		parent::_construct($options);
	}

	/**
	 * Calls a cacheable method (or not if there is already a cache for it) to read a language file
	 *
	 * Arguments of this method are read with func_get_args. So it doesn't appear
	 * in the function definition. Synopsis :
	 * call('languageName', languageObject, $arg1, $arg2, ...)
	 * (arg1, arg2... are arguments of 'functionName')
	 *
	 * @return mixed result of the function/method
	 * @access public
	 */
	function load()
	{
		$array = func_get_args();

		$lang = $array[0];
		unset( $array[0] );
		$obj = $array[1];
		unset( $array[1]);

		return $this->loadId( $lang, $obj, $array, serialize( $array ) );
	}

	/**
	 * Calls a cacheable method (or not if there is already a cache for it) to read a language file
	 * and specify a specific id
	 *
	 * @access public
	 * @param string Language used
	 * @param object JLanguage object
	 * @param array  Argument of the function
	 * @param id	 Cache id
	 * @return mixed result of the function/method
	 */
	function loadId( $lang, $obj, $arguments, $id )
	{

		$id = $this->generateId($id); // Generate a cache id

		$data = $this->get( $id, $this->_defaultGroup, !$this->_validateCache );
		if ($data !== false) {
			$result = unserialize( $data );
		} else {
			$result = call_user_func_array( array( &$obj, '_load' ), $arguments );
			$this->save( serialize( $result ), $id, $this->_defaultGroup );
		}
		return $result;
	}
}
?>