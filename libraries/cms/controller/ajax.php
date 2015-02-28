<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerAjax extends JControllerCms
{
	/**
	 * Uses the input to select the task controller and set the subject to the configuration
	 *
	 * @param   JInput       $input  The input object.
	 * @param   JApplication $app    The application object.
	 * @param   array        $config Configuration
	 *
	 * @throws InvalidArgumentException
	 * @since  0.0.2
	 */
	public function __construct(JInput $input, $app = null, $config = array())
	{
		parent::__construct($input, $app, $config);
	}

	/**
	 * @return void
	 * @todo use the format variable to determine the output
	 * @todo check to see if any of the subcontrollers are a view controller
	 */
	public function execute()
	{
		try
		{
			$this->executeController();
		}
		catch (Exception $e)
		{
			$this->addMessage($e->getMessage(), 'error');
			header('http', null, 500);
		}

		//only echo the messages, if it isn't a display task
		$task  = $this->input('task', 'display');
		$tasks = explode('.', $task);
		if (!in_array('display', (array) $tasks))
		{
			/** @var JApplicationCms $app */
			$app     = $this->app;
			$mesages = $app->getMessageQueue();
			echo json_encode($mesages);
		}

		$this->app->close();
	}
}