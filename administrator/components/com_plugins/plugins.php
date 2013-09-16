<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_plugins'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Load classes
JLoader::registerPrefix('Plugins', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

if ($controllerTask = $app->input->get('controller'))
{
	// Checking for new MVC controller
	$tasks = explode('.', $controllerTask);
}
else
{
	// Checking for old MVC task
	$task = $app->input->get('task');
	$tasks = explode('.', $task);
}

if (empty($tasks[0]))
{
	$activity = 'Display';
	$location = 'Core';
}
else
{
	$location = ucfirst(strtolower($tasks[0]));
	$activity = ucfirst(strtolower($tasks[1]));
}

// Create the controller
if ($activity == 'Display')
{
	$classname = 'JControllerDisplay';
}
elseif ($location == 'Core')
{
	$classname = 'JController' . $activity;
}
else
{
	$vName = $app->input->get('view');
	$classname  = 'PluginsController' . ucfirst($vName) . $activity;
}

$controller = new $classname;

$controller->prefix = 'Plugins';

if(!empty($tasks[2]))
{
	$controller->option = $tasks[2];
}

if (!$app->input->get('view'))
{
	$app->input->set('view', 'plugins');
}

// Perform the Request task
$controller->execute();
