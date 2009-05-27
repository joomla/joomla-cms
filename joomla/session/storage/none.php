<?php
/**
 * @version		$Id:none.php 6961 2007-03-15 16:06:53Z tcp $
 * @package		Joomla.Framework
 * @subpackage	Session
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
* File session handler for PHP
*
 * @package		Joomla.Framework
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
	function register()
	{
		//let php handle the session storage
	}
}
