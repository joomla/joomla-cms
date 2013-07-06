<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Sessions
jimport('joomla.session.session');

// Load classes
JLoader::registerPrefix('Config', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

if ($controllerTask = $app->input->get('controller'))
	// Checking for new MVC controller
	$array = explode(".", $controllerTask);
else
{
	// Checking for old MVC task
	$task = $app->input->get('task');
	$array = explode(".", $task);
}

if (empty($array[1]))
	$activity = 'display';
elseif ($array[1] == 'apply')
	$activity = 'save';
else $activity = $array[1];

// Create the controller
// if ($array[0]=='application')
	// For Application
	$classname  = 'ConfigControllerApplication' . ucfirst($activity);// only for applications
//if ($array[0] == 'component') - not worked
$componentRequired = $app->input->get('component');
if(!empty($componentRequired))
	// For Component
	$classname  = 'ConfigControllerComponent' . ucfirst($activity); // if task=component.* etc

$controller = new $classname;

// Perform the Request task
$controller->execute();
