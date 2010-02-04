<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Massmail
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$params = &JComponentHelper::getParams('com_media');
$ranks = array('publisher', 'editor', 'author', 'registered');
$acl = & JFactory::getACL();
for($i = 0; $i < $params->get('allowed_media_usergroup', 3); $i++)
{
	$acl->addACL('com_media', 'popup', 'users', $ranks[$i]);
}
// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
$app	= &JFactory::getApplication();
if (!$user->authorize('com_media', 'popup')) {
	$app->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Set the path definitions
define('COM_MEDIA_BASE',	JPATH_ROOT.DS.$params->get('image_path', 'images'));
define('COM_MEDIA_BASEURL', JURI::root(true).'/'.$params->get('image_path', 'images'));

// Load the admin HTML view
require_once JPATH_COMPONENT_ADMINISTRATOR.DS.'helpers'.DS.'media.php';

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$cmd = JRequest::getCmd('task', null);
if (strpos($cmd, '.') != false)
{
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);

	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerPath	= JPATH_COMPONENT_ADMINISTRATOR.DS.'controllers'.DS.$controllerName.'.php';

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

// Set the model and view paths to the administrator folders
$controller->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'views');
$controller->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.DS.'models');

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
