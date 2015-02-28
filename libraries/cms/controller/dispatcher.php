<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDispatcher extends JControllerCms
{
	/**
	 * Uses the input to select the task controller and set the subject to the configuration
	 *
	 * @param   JInput           $input  The input object.
	 * @param   JApplicationBase $app    The application object.
	 * @param   array            $config Configuration
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct(JInput $input, JApplicationBase $app = null, $config = array())
	{
		parent::__construct($input, $app, $config);

		if (!isset($this->config['task']))
		{
			$this->config['task'] = $this->input->post->get('task', 'display', 'CMD');

			if (empty($this->config['task']))
			{
				$this->config['task'] = 'display';
			}
		}

		$this->controller = $this->getController($this->config['task'], null, $input, $app, $this->config);
	}

	/**
	 * Method to get a controller
	 *
	 * @param string           $name   of the controller to return
	 * @param string           $prefix using the format $prefix.'Controller'.$name
	 * @param JInput           $input  to use in the constructor method
	 * @param JApplicationBase $app    to use in the constructor method
	 * @param array            $config to use in the constructor method, this is normalized using the calling classes config array.
	 *
	 * @return mixed
	 */
	protected function getController($name, $prefix = null, JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		if (strpos($name, '.'))
		{
			$name = explode('.', $name);
		}

		//make sure we have an array
		settype($name, 'array');

		$tasks = array_reverse($name);

		$controller    = null;
		$subController = null;
		foreach ($tasks AS $task)
		{
			$config['task'] = $task;

			if (is_null($prefix))
			{
				$prefix = $config['prefix'];
			}

			$class = ucfirst($prefix) . 'Controller' . ucfirst($task);

			if (!class_exists($class))
			{
				$class = $this->getFallbackController($task, $input, $app, $config);
			}

			$controller = new $class($input, $app, $config);
			$controller->setController($subController);
			$subController = $controller;
		}

		return $controller;
	}

	/**
	 * Method to get a the default task controller.
	 *
	 * Override this to use your own Fallback controller family.
	 *
	 * @param   string           $name   postfix name of the controller
	 * @param   JInput           $input  The input object.
	 * @param   JApplicationBase $app    The application object.
	 * @param   array            $config Configuration
	 *
	 * @throws InvalidArgumentException
	 * @return string
	 */
	protected function getFallbackController($name, JInput $input = null, JApplicationBase $app = null, $config = array())
	{
		$config = $this->normalizeConfig($config);

		$fallbackClass = 'JController' . ucfirst($name);

		if (!class_exists($fallbackClass))
		{
			$format = $input->getWord('format', 'html');
			throw new InvalidArgumentException(JText::sprintf('JLIB_APPLICATION_ERROR_INVALID_CONTROLLER', $fallbackClass, $format));
		}

		return $fallbackClass;
	}

	/**
	 * Proxy for $this->controller->execute()
	 *
	 * @return bool True if the controller executed successfully
	 */
	public function execute()
	{
		JPluginHelper::importPlugin('extension');
		$dispatcher = JEventDispatcher::getInstance();
		$dispatcher->trigger('onDispatchControllerExecute', array($this));
		$controller = $this->controller;

		try
		{
			$this->controller->execute();
		}
		catch (Exception $e)
		{
			$this->addMessage($e->getMessage(), 'error');
			$this->setReturn(JRoute::_('index.php?Itemid=0'));

			return false;
		}

		return true;
	}


	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 *
	 */
	public function redirect()
	{
		/** @var JApplicationCms $app */
		$app = $this->app;

		$tasks = explode('.', $this->config['task']);
		if (!in_array('display', (array) $tasks) && !$app->hasDefRedirect())
		{
			$config = $this->config;
			$this->setReturn('index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=' . $config['layout']);
		}

		// Execute the redirect
		$app->redirect($app->getDefRedirect());

		return false;
	}
}