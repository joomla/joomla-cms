<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Registration
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
 * HTML View class for the Registration component
 *
 * @author		David Gal <david.gal@joomla.org>
 * @package Joomla
 * @subpackage Registration
 * @since 1.0
 */
class RegistrationViewRegistration extends JView
{
	function __construct()
	{
		$this->setViewName('registration');
		$this->setTemplatePath(dirname(__FILE__).DS.'tmpl');
	}

	function displayPasswordForm()
	{
		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );

		$this->_loadTemplate('lostpass');
	}

	function displayRegisterForm()
	{
		require_once( JPATH_SITE .'/includes/HTML_toolbar.php' );

		$this->_loadTemplate('register');
	}

	function displayMessage()
	{
		$this->_loadTemplate('message');
	}



}
?>