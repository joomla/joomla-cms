<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die('Restricted access');

/**
 * Helper class for controllers
 *
 * @package     Joomla.Libraries
 * @subpackage  controller
 * @since       3.2
*/
class JControllerHelper
{

	/*
	 * Method to parse a controller from a url
	 * Defaults to the base controllers and passes an array of options.
	 *      $options[0] is the location of the controller which defaults to the core libraries (referenced as 'j'
	 *      and then the named folder within the component entry point file.
	 *      $options[1] is the name of the controller file,
	 *      $options[2] is the name of the folder found in the component controller folder for controllers
	 *      not prefixed with J.
	 *      Additional options maybe added to parameterise the controller.
	 *
	 * @param  JApplication  $app  A JApplication object (could be JApplication or JApplicationWeb)
	 *
	 * @return  JController  A JController object
	 *
	 * @since  3.2
	 */

	public function parseController($app)
	{
		if ($controllerTask = $app->input->get('controller'))
		{
			// Temporary solution
			if (strpos($controllerTask, '/') !== false)
			{
				$tasks = explode('/', $controllerTask);
			}
			else
			{
				$tasks = explode('.', $controllerTask);
			}
		}
		else
		{
			// Checking for old MVC task
			$task = $app->input->get('task');

			// Toolbar expects old style but we are using new style
			// Remove when toolbar can handle either directly
			if (strpos($task, '/') !== false)
			{
				$tasks = explode('/', $task);
			}
			else
			{
				$tasks = explode('.', $task);
			}
		}

		if (empty($tasks[0]) || $tasks[0] == 'j')
		{
			$location = 'J';
		}
		else
		{
			$location = ucfirst(strtolower($tasks[0]));
		}

		if (empty($tasks[1]))
		{
			$activity = 'Display';
		}
		else
		{
			$activity = ucfirst(strtolower($tasks[1]));
		}

		$view = '';

		if (empty($tasks[2]) && $location != 'J')
		{
			$view = ucfirst(strtolower($app->input->get('view')));
		}
		elseif ($location != 'J')
		{
			$view = ucfirst(strtolower($tasks[2]));
		}

		$controllerName = $location .  'Controller' . $view . $activity;

		if (!class_exists($controllerName))
		{
			return false;
		}

		$controller = new $controllerName;
		$controller->options = array();
		$controller->options = $tasks;

		return $controller;
	}
}