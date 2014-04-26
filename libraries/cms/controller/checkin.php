<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCheckin extends JControllerCms
{
	/**
	 * (non-PHPdoc)
	 * @see JController::execute()
	 */
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$config = $this->config;
		$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];

		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);

		if (!$model->allowAction('core.manage'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_CHECKIN_NOT_ALLOWED');
			$this->setRedirect($url, $msg, 'error');
			return false;
		}

		$input = $this->input;
		$cid = $input->post->get('cid', array(), 'array');
		// Make sure the item ids are integers
		$cid = $this->cleanCid($cid);


		try
		{
			foreach ($cid AS $pk)
			{
				$model->checkin($pk);
			}
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'warning');
			return false;
		}

		$msg = $this->translate('JLIB_APPLICATION_MSG_CHECKIN_SUCCEEDED');
		$this->setRedirect($url, $msg, 'message');
		return true;
	}
}