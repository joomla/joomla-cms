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

jimport( 'joomla.application.view');

/**
 * Login component HTML view class
 *
 * @package Joomla
 * @subpackage Users
 * @since	1.0
 */
class LoginViewLogin extends JView
{
	function __construct()
	{
		$this->setViewName('login');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}
	
	function display()
	{
		$user =& JFactory::getUser();
		
		if ( $user->get('id') ) {
			$this->_displayLogoutForm();
		} else {
			$this->_displayLoginForm();
		}
	}
	
	function _displayLoginForm( )
	{
		$errors =& JError::getErrors();
		
		if(JError::isError($errors[0])) {
			echo '<div class="system-error">';
			echo '<span>ERROR</span><br />';
			echo  $errors[0]->getMessage();
			echo  '</div>';
			array_shift($errors);
		}
		
		// Build login image if enabled
		if ( $this->params->get( 'image_login' ) != -1 ) {
			$image = 'images/stories/'. $this->params->get( 'image_login' );
			$this->image = '<img src="'. $image  .'" align="'. $this->params->get( 'image_login_align' ) .'" hspace="10" alt="" />';
		}
		
		$this->_loadTemplate('login');
	}

	function _displayLogoutForm( )
	{
		// Build logout image if enabled
		if ( $this->params->get( 'image_logout' ) != -1 ) {
			$image = 'images/stories/'. $this->params->get( 'image_logout' );
			$this->image = '<img src="'. $image .'" align="'. $this->params->get( 'image_logout_align' ) .'" hspace="10" alt="" />';
		}
		
		$this->_loadTemplate('logout');
	}
}
?>