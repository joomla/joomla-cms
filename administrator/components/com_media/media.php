<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!JAcl::authorise('core', 'media.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

$params =& JComponentHelper::getParams('com_media');

// Load the admin HTML view
require_once JPATH_COMPONENT.DS.'helpers'.DS.'media.php';

// Set the path definitions
$view = JRequest::getCmd('view',null);
$popup_upload = JRequest::getCmd('pop_up',null);
$path = "file_path";
if(substr(strtolower($view),0,6) == "images" || $popup_upload == 1) $path = "image_path";
define('COM_MEDIA_BASE',	JPATH_ROOT.DS.$params->get($path, 'images'));
define('COM_MEDIA_BASEURL',	JURI::root().$params->get($path, 'images'));

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$cmd = JRequest::getCmd('task', null);

if (strpos($cmd, '.') != false)
{
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);

	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerPath	= JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php';

	// If the controller file path exists, include it ... else lets die with a 500 error
	if (file_exists($controllerPath)) {
		require_once $controllerPath;
	} else {
		JError::raiseError(500, 'Invalid Controller');
	}
}
else
{
	// Base controller, just set the task :)
	$controllerName = null;
	$task = $cmd;
}

// Set the name for the controller and instantiate it
$controllerClass = 'MediaController'.ucfirst($controllerName);
if (class_exists($controllerClass)) {
	$controller = new $controllerClass();
} else {
	JError::raiseError(500, 'Invalid Controller Class');
}

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
