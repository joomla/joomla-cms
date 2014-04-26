<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerCreateBase extends JControllerJoomlaSave
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$url = 'index.php?option='.$config['option'].'&task=add.'.$config['subject'];

		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);

		if (!$model->allowAction('core.create'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED');
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		try
		{
			$input = $this->input;
			$data = $input->post->get('jform', array(), 'array');
			$keyName = $model->getKeyName();
			$data[$keyName] = 0;
				
			$this->commit($model, $data);
		}
		catch (Exception $e)
		{
			$this->setUserState();
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		return true;

	}

	protected function commit($model, $data)
	{
		$model->create($data);
	}
}
