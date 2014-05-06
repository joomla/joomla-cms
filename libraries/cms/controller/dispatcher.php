<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDispatcher extends JControllerCms
{
	/**
	 * The Task Controller
	 * @var JControllerCms
	 */
	protected $controller;

	/**
	 * Uses the input to select the task controller and set the subject to the input
	 * uses the 'option' and 'task' to determine $this->controller
	 * format Option.'Controller'.Task
	 * Also sets the subject using the task
	 * if the task isn't in controller.subject format, it defaults to task.view
	 * if no task is set, it defaults to display.view
	 * if both the task and the view are not set, it defaults to display.$config['default_view']
	 *
	 * @param JInput           $input
	 * @param JApplication $app
	 * @param array            $config
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(JInput $input, $app = null, $config = array())
	{
		$command = $input->get('task', 'display', 'cmd');

		if (!strpos($command, '.'))
		{
			if (!array_key_exists('default_view', $config))
			{
				$config['default_view'] = 'default';
			}

			$command .= '.' . $input->get('view', $config['default_view']);
		}

		list($controllerName, $subject) = explode('.', $command);

		$config['task']    = $controllerName;
		$config['subject'] = $subject;

		parent::__construct($input, $app, $config);

		$optionPrefix = $this->getPrefix();

		$class = $optionPrefix . 'Controller' . ucfirst($controllerName);

		if (!class_exists($class)) // get the fallback
		{
			$class = $this->getFallbackClassName($controllerName);

			if (!class_exists($class)) // PANIC!
			{
				$format = $input->getWord('format', 'html');
				throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $controllerName, $format));
			}
		}

		$input->set('task', $controllerName);

		$this->controller = new $class($input, $app, $config);
	}

	/**
	 * Method to get a the default task controller.
	 * "JController'.ucfirst($task);
	 *
	 * @param String $task
	 *
	 * @return string
	 */
	protected function getFallbackClassName($task)
	{
		$class_name = 'JController' . ucfirst($task);

		return $class_name;
	}

	/**
	 * proxy for $this->controller->execute()
	 * @see JControllerCms::execute()
	 */
	public function execute()
	{
		if ($this->controller->execute())
		{
			return true;
		}

		return false;
	}

	/**
	 * proxy for $this->controller->redirect()
	 * @see JControllerCms::redirect()
	 */
	public function redirect()
	{
		return $this->controller->redirect();
	}

	/**
	 * proxy for $this->controller->mergeModels()
	 * @param array $models Associative array of models that follow the $models[prefix][$name] format
	 * @param bool  $overwrite True to overwrite existing models with $models value
	 * @return void
	 */
	public function mergeModels($models = array(), $overwrite = false)
	{
		$this->controller->mergeModels($models, $overwrite);
	}
}