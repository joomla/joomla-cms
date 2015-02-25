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
	 * The Task Controller
	 * @var JControllerCms
	 */
	protected $controller;

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
			$this->message     = $controller->message;
			$this->messageType = $controller->messageType;
		}
		catch (Exception $e)
		{
			$this->setReturn(JRoute::_('index.php?Itemid=0'), $e->getMessage(), 'error');

			return false;
		}

		return true;
	}


	/**
	 * Redirects the browser or returns false if no redirect is set.
	 *
	 * @return  boolean  False if no redirect exists.
	 *
	 * @todo refactor MVSC controllers to set the application redirect themselves.
	 */
	public function redirect()
	{
		if ($this->config['task'] != 'display')
		{
			$config = $this->config;
			$this->setReturn('index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=' . $config['layout']);

			$controller = $this->controller;
			if ($controller->hasReturn())
			{
				$this->setReturn($controller->getReturn());
			}
		}

		if ($this->hasReturn())
		{
			$app = $this->app;

			// Enqueue the redirect message
			$app->enqueueMessage($this->message, $this->messageType);

			// Execute the redirect
			$app->redirect($this->return);
		}

		return false;
	}
}