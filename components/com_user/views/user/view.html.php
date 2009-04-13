<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Weblinks
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.application.component.view');

/**
 * HTML View class for the Users component
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
		$mainframe = JFactory::getApplication();

		$layout	= $this->getLayout();
		if( $layout == 'form') {
			$this->_displayForm($tpl);
			return;
		}

		if ( $layout == 'login' ) {
			parent::display($tpl);
			return;
		}

		$user =& JFactory::getUser();

		// Get the page/component configuration
		$params = &$mainframe->getParams();

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'User Details' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'User Details' ));
		}
		$document	= &JFactory::getDocument();
		$document->setTitle( $params->get( 'page_title' ) );

		// Set pathway information
		$this->assignRef('user'   , $user);
		$this->assignRef('params',		$params);

		parent::display($tpl);
	}

	function _displayForm($tpl = null)
	{
		$mainframe = JFactory::getApplication();

		$user	=& JFactory::getUser();
		$params	= &$mainframe->getParams();

		// check to see if Frontend User Params have been enabled
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$check = $usersConfig->get('frontend_userparams');

		if ($check == '1' || $check == 1 || $check == NULL)
		{
			if($user->authorize( 'com_user', 'edit' )) {
				$params		= $user->getParameters(true);
			}
		}
		$params->merge( $params );
		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		if (is_object( $menu )) {
			$menu_params = new JParameter( $menu->params );
			if (!$menu_params->get( 'page_title')) {
				$params->set('page_title',	JText::_( 'Edit Your Details' ));
			}
		} else {
			$params->set('page_title',	JText::_( 'Edit Your Details' ));
		}
		$document	= &JFactory::getDocument();
		$document->setTitle( $params->get( 'page_title' ) );

		$this->assignRef('user'  , $user);
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
