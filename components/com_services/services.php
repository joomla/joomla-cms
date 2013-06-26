<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_services
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Sessions
jimport('joomla.session.session');

// Load classes
JLoader::registerPrefix('Services', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

if ($controllerTask = $app->input->get('controller'))
{
	// Checking for new MVC controller
	$array = explode(".", $controllerTask);
}
else
{
	// Checking for old MVC task
	$task = $app->input->get('task');
	$array = explode(".", $task);
}

// Get the controller name
if (empty($array[1]))
{
	$activity = 'display';
}
elseif ($array[1] == 'apply')
{
	$activity = 'save';
}
else
{
	$activity = $array[1];
}

// Create the controller
if ($array[0] == 'config')
{
	// For Config
	$classname  = 'ServicesControllerConfig' . ucfirst($activity);
}
else
{
	$app->enqueueMessage(JText::_('COM_SERVICES_ERROR_CONTROLLER_NOT_FOUND'), 'error');

	return;

}

$controller = new $classname;

// Perform the Request task
$controller->execute();
