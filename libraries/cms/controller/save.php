<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JCmsControllerSave extends JCmsControllerBase
{

	/**
	 * Method to save the data to the model
	 * @param JCmsModelData $model to save to
	 * @param array $data from the form
	 */
	abstract protected function commit($model, $data);


	protected function setUserState()
	{
		$config = $this->config;
		$key = $config['option'].'.edit.'.$config['subject'].'.data';

		$input = $this->input;
		$data = $input->post->get('jform', array(), 'array');

		$app = $this->app;
		$app->setUserState($key, $data);
	}
}
