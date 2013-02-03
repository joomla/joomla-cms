<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

$params = JComponentHelper::getParams('com_media');
// Make sure the user is authorized to view this page
$user = JFactory::getUser();
$asset = JRequest::getCmd('asset');
$author = JRequest::getCmd('author');
if (!$asset or
		!$user->authorise('core.edit', $asset)
	&&	!$user->authorise('core.create', $asset)
	&& 	count($user->getAuthorisedCategories($asset, 'core.create')) == 0
	&&	!($user->id==$author && $user->authorise('core.edit.own', $asset)))
{
	return JError::raiseWarning(403, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Set the path definitions
define('COM_MEDIA_BASE',	JPATH_ROOT.'/'.$params->get('image_path', 'images'));
define('COM_MEDIA_BASEURL', JURI::root().'/'.$params->get('image_path', 'images'));

$lang = JFactory::getLanguage();
	$lang->load('com_media', JPATH_ADMINISTRATOR, null, false, false)
	||	$lang->load('com_media', JPATH_SITE, null, false, false)
	||	$lang->load('com_media', JPATH_ADMINISTRATOR, $lang->getDefault(), false, false);

// Load the admin HTML view
require_once JPATH_COMPONENT_ADMINISTRATOR.'/helpers/media.php';

// Require the base controller
require_once JPATH_COMPONENT.'/controller.php';

// Make sure the user is authorized to view this page
$user	= JFactory::getUser();
$app	= JFactory::getApplication();
$cmd	= JRequest::getCmd('task', null);

if (strpos($cmd, '.') != false) {
	// We have a defined controller/task pair -- lets split them out
	list($controllerName, $task) = explode('.', $cmd);

	// Define the controller name and path
	$controllerName	= strtolower($controllerName);
	$controllerPath	= JPATH_COMPONENT_ADMINISTRATOR.'/controllers/'.$controllerName.'.php';

	// If the controller file path exists, include it ... else lets die with a 500 error
	if (file_exists($controllerPath)) {
		require_once $controllerPath;
	}
	else {
		JError::raiseError(500, JText::_('JERROR_INVALID_CONTROLLER'));
	}
}
else {
	// Base controller, just set the task :)
	$controllerName = null;
	$task = $cmd;
}

// Set the name for the controller and instantiate it
$controllerClass = 'MediaController'.ucfirst($controllerName);

if (class_exists($controllerClass)) {
	$controller = new $controllerClass();
}
else {
	JError::raiseError(500, JText::_('JERROR_INVALID_CONTROLLER_CLASS'));
}

// Set the model and view paths to the administrator folders
$controller->addViewPath(JPATH_COMPONENT_ADMINISTRATOR.'/views');
$controller->addModelPath(JPATH_COMPONENT_ADMINISTRATOR.'/models');

// Perform the Request task
$controller->execute($task);

// Redirect if set by the controller
$controller->redirect();
