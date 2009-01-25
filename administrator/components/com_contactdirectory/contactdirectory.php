<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	ContactDirectory
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require specific controller if requested
if ($controller = JRequest::getVar('controller','contact')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controller.'.php';
	if (file_exists($path)) {
		require_once $path;
	}else {
		JError::raiseError(500, 'Invalid Controller');
	}
}

// Create the controller
$controllerClass	= 'ContactdirectoryController'.ucfirst($controller);
if (class_exists($controllerClass)) {
	$controller = new $controllerClass();
}
else {
	JError::raiseError(500, 'Invalid Controller Class');
}

// Perform the Request task
$controller->execute(JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();
