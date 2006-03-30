<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Users
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// load the html view class
require_once( JApplicationHelper::getPath( 'front_html' ) );

// Initialize variables
$user			= & $mainframe->getUser();
$menu			= JMenu::getInstance();
$menu			= $menu->getItem( $Itemid );
$params			= new JParameter( $menu->params );
$loginImage		= null;
$logoutImage	= null;

// Set some default page parameters if not set
$params->def( 'page_title', 				1 );
$params->def( 'header_login', 				$menu->name );
$params->def( 'header_logout', 				$menu->name );
$params->def( 'pageclass_sfx', 				'' );
$params->def( 'back_button', 				$mainframe->getCfg( 'back_button' ) );
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
$params->def( 'registration', 				$mainframe->getCfg( 'allowUserRegistration' ) );

// Build login image if enabled
if ( $params->get( 'image_login' ) != -1 ) {
	$image = 'images/stories/'. $params->get( 'image_login' );
	$loginImage = '<img src="'. $image  .'" align="'. $params->get( 'image_login_align' ) .'" hspace="10" alt="" />';
}
// Build logout image if enabled
if ( $params->get( 'image_logout' ) != -1 ) {
	$image = 'images/stories/'. $params->get( 'image_logout' );
	$logoutImage = '<img src="'. $image .'" align="'. $params->get( 'image_logout_align' ) .'" hspace="10" alt="" />';
}

// Get some page variables
$breadcrumbs = & $mainframe->getPathway();
$document	 = & $mainframe->getDocument();

if ( $user->get('id') ) {
	$title = JText::_( 'Logout');
	
	// pathway item
	$breadcrumbs->setItemName(1, $title );
	// Set page title
	$document->setTitle( $title );

	JViewLoginHTML::logoutpage( $params, $logoutImage );
} else {
	$title = JText::_( 'Login');
	
	// pathway item
	$breadcrumbs->setItemName(1, $title );
	// Set page title
	$document->setTitle( $title );

	JViewLoginHTML::loginpage( $params, $loginImage );
}
?>