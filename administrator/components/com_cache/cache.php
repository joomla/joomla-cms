<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load classes
JLoader::registerPrefix('Cache', JPATH_COMPONENT);

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
	$activity = 'display';
}
else
{
	$activity = $tasks[0];
}

// Create the controller
if ($activity == 'display')
{
	$classname = 'JControllerDisplay';
}
else
{
	$classname  = 'CacheControllerCache' . ucfirst($activity);
}

$controller = new $classname;

$controller->prefix = 'Cache';

if (!$app->input->get('view'))
{
	$app->input->set('view', 'cache');
}

// Perform the Request task
$controller->execute();
