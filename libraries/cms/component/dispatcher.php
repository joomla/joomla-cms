<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Dispatcher
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JComponentDispatcher implements JComponentDispatcherInterface
{
	// Constants that define the form of the controller passed in the URL
	const CONTROLLER_PREFIX = 0;
	const CONTROLLER_ACTIVITY = 1;
	const CONTROLLER_VIEW_FOLDER = 2;

	/**
	 * @var    JControllerCmsInterface  The Task Controller.
	 * @since  3.4
	 */
	protected $controller;

	/**
	 * @var    string  The name of the default view, in case none is specified.
	 * @since  3.4
	 */
	public $defaultView = 'cpanel';

	/**
	 * @var    JInput  The input object.
	 * @since  3.4
	 */
	protected $input = null;

	/**
	 * @var    array  The configuration to be injected.
	 * @since  3.4
	 */
	protected $config = array();

	/**
	 * Gets an instance of a Dispatcher, creating one if one does not exist
	 *
	 * @param   string  $option  The component name
	 * @param   string  $view    The View name
	 * @param   JInput  $input   An input object
	 * @param   array   $config  Configuration data
	 *
	 * @return  JComponentDispatcher
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function getInstance($option, $view = null, JInput $input = null, $config = array())
	{
		// Create an input object if one isn't given.
		if (!$input)
		{
			$input = new JInput;
		}

		// Set the view in the input object
		$input->set('view', $view);

		$prefix = ucfirst(substr($option, 4));

		// We can't let people use JDispatcher as that is a unrelated legacy class name in Joomla 3.x
		if ($prefix === 'J')
		{
			throw new InvalidArgumentException('The prefix J for a class name is not allowed');
		}

		// Get the dispatcher class name
		$className = $prefix . 'Dispatcher';

		// If we can't find a component specific dispatcher use this.
		if (!class_exists($className))
		{
			$className = 'JComponentDispatcher';
		}

		$instance = new $className($option, $input, $config);

		return $instance;
	}

	/**
	 * Sets the input, option, config and view variables for use elsewhere in the class.
	 *
	 * @param   string  $option  The application object
	 * @param   JInput  $input   The input class
	 * @param   array   $config  A config array
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct($option, JInput $input = null, $config = array())
	{
		// Create an input object if one doesn't exist.
		if (!$input)
		{
			$input = new JInput;
		}

		$this->input = $input;
		$this->config = (array) $config;

		// Set the option value in the config and input
		$this->config['option'] = $option;
		$input->set('option', $option);

		// Set the view value in config and input object. To get the view we check the input
		// object. If we aren't given one in the input object then we use the default view
		$view = $this->input->getCmd('view', $this->defaultView);
		$this->config['view'] = $view;
		$this->input->set('view', $view);
	}

	/**
	 * Gets a controller and dispatches it. If there is a redirect it is
	 * performed
	 *
	 * @param   JApplicationCms  $app  The application object.
	 *
	 * @return  void
	 *
	 * @throws  InvalidArgumentException
	 */
	public function dispatch(JApplicationCms $app = null)
	{
		$controller = $this->getController($app);
		$controller->options = $this->getTasks();

		try
		{
			$result = $controller->execute();
		}
		catch (Exception $e)
		{
			// @todo Do something with the exception generated from the execute method!
			return;
		}

		// @todo Do something if the execute method returns false.
		if (!$result)
		{
			return;
		}

		$controller->redirect();
	}

	/**
	 * Gets the controller
	 *
	 * @param   JApplicationCms  $app  The application object.
	 *
	 * @return  JControllerCmsInterface
	 *
	 * @throws  InvalidArgumentException
	 */
	public function getController(JApplicationCms $app = null)
	{
		// Assemble the queue of potential classes
		$class = null;
		$classQueue = $this->getControllerNames();
		$classQueue->top();

		$first = null;

		// Loop through each class and see if one exists
		while($classQueue->valid())
		{
			$potentialClass = $classQueue->current();

			// Log the primary controller name so if we can't find any classes
			// we can add this name to the log later
			if(!$first)
			{
				$first = $potentialClass;
			}

			// Check if the class exists and implements the interface
			if (class_exists($potentialClass) && $potentialClass instanceof JControllerCmsInterface)
			{
				// We've found a class - we can stop searching
				$class = $potentialClass;

				break;
			}

			$classQueue->next();
		}

		if (!$class)
		{
			$format = $this->input->getWord('format', 'html');

			throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $first, $format));
		}

		$this->controller = new $class($this->input, $app, $this->config);

		return $this->controller;
	}

	/**
	 * Gets the controller name
	 *
	 * @return  SplPriorityQueue  An SplPriorityQueue containing potential class names
	 *
	 * @throws  InvalidArgumentException
	 */
	protected function getControllerNames()
	{
		$tasks = $this->getTasks();

		if (empty($tasks[self::CONTROLLER_PREFIX]) || $tasks[self::CONTROLLER_PREFIX] == 'j')
		{
			$location = 'J';
		}
		else
		{
			$location = ucfirst(strtolower($tasks[self::CONTROLLER_PREFIX]));
		}

		if (empty($tasks[self::CONTROLLER_ACTIVITY]))
		{
			$activity = 'Displaylist';
		}
		else
		{
			$activity = ucfirst(strtolower($tasks[self::CONTROLLER_ACTIVITY]));
		}

		$view = '';

		if (empty($tasks[self::CONTROLLER_VIEW_FOLDER]) && $location != 'J')
		{
			$view = ucfirst(strtolower($this->input->get('view')));
		}
		elseif ($location != 'J')
		{
			$view = ucfirst(strtolower($tasks[self::CONTROLLER_VIEW_FOLDER]));
		}

		// Create the priority queue now we've sorted things out
		$queue = new SplPriorityQueue;
		$queue->insert($location . 'Controller' . $view . $activity, 100);
		$queue->insert($location . 'Controller' . $activity, 10);
		$queue->insert('JController' . $activity, 1);

		return $queue;
	}

	/**
	 * Method to get the controller information from the URL
	 * Defaults to the base controllers.
	 *      $tasks[CONTROLLER_PREFIX] is the location of the controller which defaults to the core libraries (referenced as 'j'
	 *      and then the named folder within the component entry point file.
	 *      $tasks[CONTROLLER_ACTIVITY] is the name of the controller file,
	 *      $tasks[CONTROLLER_VIEW_FOLDER] is the name of the folder found in the component controller folder for controllers
	 *      not prefixed with J.
	 *
	 * @return   array  The tasks in the form listed above
	 */
	public function getTasks()
	{
		$controllerTask = $this->input->get('task');

		if (empty($controllerTask))
		{
			$controllerTask = $this->input->get('controller');
		}

		if (!empty($controllerTask))
		{
			// Temporary solution - Toolbar expects old style but we are using new style
			// Remove when toolbar can handle either directly
			// @todo Talk with Buddhima/Elin about what this actually means!?
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
			// In the absence of a named controller default to display.
			$tasks = array('j', 'displaylist');
		}

		return $tasks;
	}
}
