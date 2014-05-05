<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerImport extends JControllerCms
{

	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&task=display.' . $config['subject'];

		$model = $this->getModel();

		if (!$model->allowAction('core.import'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_IMPORT_NOT_PERMITTED');
			$this->setRedirect($url, $msg, 'error');

			return false;
		}

		try
		{
			$input = $this->input;
			$data  = $input->post->get('jform', array(), 'array');
			$files = $input->files->get('jform');

			$this->import($model, $data, $files);

		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'error');

			return false;
		}

		$msg = $this->translate('JLIB_APPLICATION_MESSAGE_IMPORT_COMPLETED');
		$this->setRedirect($url, $msg, 'message');

		return true;
	}

	/**
	 * Method to execute model import function
	 *
	 * @param JModelCms $model
	 * @param array     $data  JForm data
	 * @param array     $files JForm files
	 */
	protected function import($model, $data, $files)
	{
		$model->import($data, $files);
	}
}