<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

abstract class JControllerExportBase extends JControllerCms
{
	/** @noinspection PhpInconsistentReturnPointsInspection */
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$model = $this->getModel();

		if (!$model->allowAction('core.export'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_EXPORT_NOT_PERMITTED');
			$this->abort($msg, 'error');

			return false;
		}

		try
		{
			$config = $this->config;
			$input = $this->input;
			$this->export($model, $input, $config);
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->abort($msg, 'error');

			return false;
		}

		// We assume the export file was sent to the browser
		//So close the application
		JFactory::getApplication()->close();
	}

	/**
	 * Method to export data from the model
	 *
	 * @param JModelCms $model
	 * @param JInput    $input
	 * @param array     $config
	 *
	 * @return boolean True if the file was sent successfully.
	 */
	abstract function export($model, $input, $config);
}