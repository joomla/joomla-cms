<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Sessions
jimport('joomla.session.session');

// Load classes
JLoader::registerPrefix('Checkin', JPATH_COMPONENT);

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

if (empty($tasks[1]))
{
	$activity = 'display';
}
elseif ($tasks[1] == 'apply')
{
	$activity = 'checkin';
}
else
{
	$activity = $tasks[1];
}
// Create the controller
if ($activity == 'display')
{
	$classname = 'JControllerDisplay';
}
else
{
	$classname  = 'CheckinControllerCheckin' . ucfirst($activity);
}
$controller = new $classname;
$controller->prefix = 'Checkin';

if (!$app->input->get('view'))
{
	$app->input->set('view', 'checkin');
}
var_dump($controller);
// Perform the Request task
$controller->execute();
