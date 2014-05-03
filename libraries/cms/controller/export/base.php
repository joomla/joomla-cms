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
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];

		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);

		if (!$model->allowAction('core.export'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_EXPORT_NOT_PERMITTED');
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		try
		{
			$input = $this->input;
			$this->export($model, $input, $config);
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		// We assume the exprot file was sent to the browser
		// So if we made it this far we just need to close the app.
		$app = $this->app;
		$app->close();
	}

	/**
	 * Method to export data from the model
	 * @param JModelData $model
	 * @param JInput $input
	 * @param array $config
	 * @return boolean True if the file was sent successfully.
	 */
	abstract function export($model, $input ,$config);
}