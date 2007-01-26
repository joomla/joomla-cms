<?php
/**
* @version		$Id: view.php 6138 2007-01-02 03:44:18Z eddiea $
* @package		Joomla
* @subpackage	Login
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

jimport( 'joomla.application.component.view');

/**
 * User component login view class
 *
 * @package		Joomla
 * @subpackage	Users
 * @since	1.0
 */
class UserViewLogin extends JView
{
	function display($tpl = null)
	{
		global $mainframe, $Itemid, $option;

		// Initialize variables
		$document	=& JFactory::getDocument();
		$user		=& JFactory::getUser();
		$pathway	=& $mainframe->getPathway();

		$menu		=& JSiteHelper::getActiveMenuItem();
		$params		=& JSiteHelper::getMenuParams();
		
		$type = (!$user->get('guest')) ? 'logout' : 'login';

		// Set some default page parameters if not set
		$params->def( 'page_title', 				1 );
		$params->def( 'header_login', 				$menu->name );
		$params->def( 'header_logout', 				$menu->name );
		$params->def( 'pageclass_sfx', 				'' );
		$params->def( 'login', 						'index.php' );
		$params->def( 'logout', 					'index.php' );
		$params->def( 'description_login', 			1 );
		$params->def( 'description_logout', 		1 );
		$params->def( 'description_login_text', 	JText::_( 'LOGIN_DESCRIPTION' ) );
		$params->def( 'description_logout_text',	JText::_( 'LOGOUT_DESCRIPTION' ) );
		$params->def( 'image_login', 				'key.jpg' );
		$params->def( 'image_logout', 				'key.jpg' );
		$params->def( 'image_login_align', 			'right' );
		$params->def( 'image_logout_align', 		'right' );
		$usersConfig = &JComponentHelper::getParams( 'com_users' );
		$params->def( 'registration', 				$usersConfig->get( 'allowUserRegistration' ) );

		if ( !$user->get('guest') )
		{
			$title = JText::_( 'Logout');

			// pathway item
			$pathway->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );
		}
		else
		{
			$title = JText::_( 'Login');

			// pathway item
			$pathway->setItemName(1, $title );
			// Set page title
			$document->setTitle( $title );
		}
	
		// Build login image if enabled
		if ( $params->get( 'image_'.$type ) != -1 ) {
			$image = 'images/stories/'. $params->get( 'image_'.$type );
			$image = '<img src="'. $image  .'" align="'. $params->get( 'image_'.$type.'_align' ) .'" hspace="10" alt="" />';
		}
		
		$errors =& JError::getErrors();
		
		$this->assign('image', $image);
		$this->assign('type', $type);
		$this->assignRef('params', $params);

		parent::display($tpl);
	}
}
?>