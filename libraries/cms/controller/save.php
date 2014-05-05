<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerSave extends JControllerCms
{

	/**
	 * Method to save the data to the model
	 *
	 * @param JModelCms $model to save to
	 * @param array     $data  from the form
	 */
	abstract protected function commit($model, $data);


	/**
	 * Method to save the user input into state.
	 * This is intended to be used to preserve form data when server side validation fails
	 */
	protected function setUserState()
	{
		$config = $this->config;
		$key    = $config['option'] . '.edit.' . $config['subject'] . '.data';

		$input = $this->input;
		$data  = $input->post->get('jform', array(), 'array');

		$app = $this->app;
		$app->setUserState($key, $data);
	}
}
