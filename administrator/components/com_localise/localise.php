<?php
/**
* @version 1.5
* @package com_localise
* @author Ifan Evans
* @copyright Copyright (C) 2007 Ifan Evans. All rights reserved.
* @license GNU/GPL
* @bugs - please report to post@ffenest.co.uk
*/

// no direct access
defined( '_JEXEC' ) or die();

// include files
require_once( JPATH_COMPONENT.DS.'controller.php' );
require_once( JPATH_COMPONENT.DS.'helper.php' );

// Make sure the user is authorized to view this page
$user 	= & JFactory::getUser();
$acl 	= & JFactory::getACL();
$acl->addACL( 'com_localise', 'manage', 'users', 'super administrator' );
if (!$user->authorize( 'com_localise', 'manage' )) {
	$mainframe->redirect( 'index.php', JText::_('ALERTNOTAUTH') );
}

// run the controller
$controller = new TranslationsController( array('default_task' => 'languages') );
$controller->execute( JRequest::getVar( 'task' ) );
$controller->redirect();

?>