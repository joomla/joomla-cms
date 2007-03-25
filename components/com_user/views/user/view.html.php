<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the WebLinks component
 *
 * @static
 * @package		Joomla
 * @subpackage	Weblinks
 * @since 1.0
 */
class UserViewUser extends JView
{
	function display( $tpl = null)
	{
		global $mainframe;
		
		if($this->getLayout() == 'form') {
			$this->_displayForm($tpl);
			return;
		}
		
		$user =& JFactory::getUser();

		// Set pathway information		
		$this->assignRef('user'   , $user);
		
		parent::display($tpl);
	}
	
	function _displayForm($tpl = null)
	{
		global $mainframe;
		
		$user =& JFactory::getUser();
		
		// Get the paramaters of the active menu item
		$menu = &JMenu::getInstance();
		$item = $menu->getActive();
		
		// Set page title
		$mainframe->setPageTitle( $item->name );

		// check to see if Frontend User Params have been enabled
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$check = $usersConfig->get('frontend_userparams');
		
		if ($check == '1' || $check == 1 || $check == NULL) 
		{
			$params = $user->getParameters();
			if( $user->authorize( 'mydetails', 'manage' ) ){
				$params->loadSetupFile(JPATH_ADMINISTRATOR . '/components/com_users/users.xml');
			} else if ( $user->authorize( 'mydetails', 'author' ) ){
				$params->loadSetupFile(JPATH_ADMINISTRATOR . '/components/com_users/users_author.xml');
			} else if ( $user->authorize( 'mydetails', 'registered' ) ){
				$params->loadSetupFile(JPATH_ADMINISTRATOR . '/components/com_users/users_registered.xml');
			}
		}

		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);
		
		parent::display($tpl);
	}
}
?>
