<?php
/**
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 * @package     Joomla.Platform
 * @subpackage  Session
 */

defined('JPATH_PLATFORM') or die;

/**
* File session handler for PHP
*
 * @package		Joomla.Platform
 * @subpackage	Session
 * @since		1.5
* @see http://www.php.net/manual/en/function.session-set-save-handler.php
 */
class JSessionStorageNone extends JSessionStorage
{
	/**
	* Register the functions of this class with PHP's session handler
	*
	* @access public
	* @param array $options optional parameters
	*/
	function register($options = array())
	{
		//let php handle the session storage
	}
}
