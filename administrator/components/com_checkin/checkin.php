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

if (empty($tasks[0]))
{
	$activity = 'display';
	$classname = 'JControllerDisplay';
}
elseif (empty($tasks[1]))
{
	$activity = $tasks[0];
	$classname = 'JController' . ucfirst($activity);
}
elseif (!empty($tasks[1]))
{
	$group = $tasks[0];
	$activity = $tasks[1];
	$classname = 'CheckinController' . $group . ucfirst($activity);
}

$controller = new $classname;

$controller->prefix = 'Checkin';

if (!$app->input->get('view'))
{
	$app->input->set('view', 'checkin');
}

// Perform the Request task
$controller->execute();
