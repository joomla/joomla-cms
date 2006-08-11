<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Login
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/**
 * Login component HTML view class
 *
 * @package Joomla
 * @subpackage Users
 * @since	1.0
 */
class LoginView
{
	/**
	 * Method to show the login page
	 *
	 * @static
	 * @access	public
	 * @param	object	$params	Page parameters
	 * @param	string	$image	Display image
	 * @return	void
	 * @since	1.0
	 */
	function showLogin( &$params, $image )
	{
		$return = $params->get('login');

		$errors =& JError::getErrors();
		
		if(JError::isError($errors[0])) {
			echo '<div class="system-error">';
			echo '<span>ERROR</span><br />';
			echo  $errors[0]->getMessage();
			echo  '</div>';
			array_shift($errors);
		}
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'login.php');
	}

	/**
	 * Method to show the logout page
	 *
	 * @static
	 * @access	public
	 * @param	object	$params	Page parameters
	 * @param	string	$image	Display image
	 * @return	void
	 * @since	1.0
	 */
	function showLogout( &$params, $image )
	{
		$return = $params->get('logout');
		
		require(dirname(__FILE__).DS.'tmpl'.DS.'logout.php');
	}
}
?>