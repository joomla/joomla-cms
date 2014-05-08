<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerUpdateBase extends JControllerSave
{

	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$input  = $this->input;

		$model    = $this->getModel();
		$keyName  = $model->getKeyName();
		$keyValue = $input->getInt($keyName);

		$url = 'index.php?option=' . $config['option'] . '&task=edit.' . $config['subject'];
		$url .= '&' . $keyName . '=' . $keyValue;

		if (!$model->allowAction('core.edit'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_EDIT_RECORD_NOT_PERMITTED');
			$this->setRedirect($url, $msg, 'error');

			return false;
		}

		try
		{
			$input = $this->input;
			$data  = $input->post->get('jform', array(), 'array');
			$this->commit($model, $data);
		}
		catch (Exception $e)
		{
			$this->setUserState($model);
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'error');

			return false;
		}

		return true;
	}

	/**
	 * @see JControllerSave::commit()
	 */
	protected function commit($model, $data)
	{
		$model->update($data);
	}

	/**
	 * Method to checkin a record
	 * @return boolean
	 */
	protected function checkin()
	{
		$config = $this->config;

		$prefix  = $this->getPrefix();
		$model   = $this->getModel($prefix, $config['subject'], $config);
		$keyName = $model->getKeyName();

		$input    = $this->input;
		$keyValue = $input->getInt($keyName);

		$model->checkin($keyValue);

		return true;
	}
}
