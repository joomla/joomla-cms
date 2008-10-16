<?php

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Make sure the user is authorized to view the component
$auth =& JFactory::getACL();

$auth->addACL('com_contactdirectory', 'manage contacts', 'users', 'super administrator');
$auth->addACL('com_contactdirectory', 'manage contacts', 'users', 'administrator');
$auth->addACL('com_contactdirectory', 'manage contacts', 'users', 'manager');
$auth->addACL('com_contactdirectory', 'manage fields', 'users', 'super administrator');
$auth->addACL('com_contactdirectory', 'manage fields', 'users', 'administrator');

// Require specific controller if requested
if($controller = JRequest::getVar('controller','contact')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	}else {
		JError::raiseError( 500, 'Invalid Controller' );
	}
}

// Create the controller
$controllerClass	= 'ContactdirectoryController'.ucfirst($controller);
if (class_exists( $controllerClass )) {
	$controller = new $controllerClass();
}
else {
	JError::raiseError(500, 'Invalid Controller Class');
}

// Perform the Request task
$controller->execute( JRequest::getVar( 'task' ) );

// Redirect if set by the controller
$controller->redirect();

?>