<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!JAcl::authorise('core', 'users.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Import library dependencies
jimport('joomla.application.component.controller');
jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');
require_once dirname(__FILE__).DS.'controller.php';

// Determine the request protocol
$protocol = JRequest::getWord('protocol');

// Get task command from the request
$cmd = JRequest::getVar('task', null);

// If it was a multiple option post get the selected option
if (is_array($cmd)) {
	$cmd = array_pop(array_keys($cmd));
}

// Filter the command and instantiate the appropriate controller
$cmd = JFilterInput::clean($cmd,'cmd');
if (strpos($cmd, '.') != false) {
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);

	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerFile = ($protocol) ? $controllerName.'.'.$protocol : $controllerName;
	$controllerPath	= JPATH_COMPONENT.DS.'controllers'.DS.$controllerFile.'.php';

	// If the controller file path exists, include it ... else lets die with a 500 error
	if (file_exists($controllerPath)) {
		require_once $controllerPath;
	}
	else {
		JError::raiseError(500, 'Invalid Controller');
	}
}
else {
	// Base controller, just set the task :)
	$controllerName = null;
	$task = $cmd;
}

// Set the name for the controller and instantiate it
$controllerClass = 'UserController'.ucfirst($controllerName);

if (class_exists($controllerClass)) {
	$controller = new $controllerClass();
}
else {
	JError::raiseError(500, 'Invalid Controller Class');
}

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
