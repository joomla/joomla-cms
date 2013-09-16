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
	$activity = 'Display';
	$classname = 'JControllerDisplay';
}
elseif (empty($tasks[1]))
{
	$activity = $tasks[0];
	$classname = 'JController' . ucfirst($activity);
}
elseif (!empty($tasks[1]))
{
	$location = ucfirst(strtolower($tasks[0]));
	$activity = ucfirst(strtolower($tasks[1]));
	$classname = 'CacheController' . $location . $activity;
}

$controller = new $classname;

$controller->prefix = 'Cache';

if (!$app->input->get('view'))
{
	$app->input->set('view', 'cache');
}

if(!empty($tasks[2]))
{
	$controller->option = strtolower($tasks[2]);
}

// Perform the Request task
$controller->execute();
