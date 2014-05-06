<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerDelete extends JControllerCms
{
	/**
	 * (non-PHPdoc)
	 * @see JController::execute()
	 */
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		$model = $this->getModel();

		If (!$model->allowAction('core.delete'))
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_DELETE_NOT_PERMITTED');
			$this->abort($msg, 'error');

			return false;
		}

		$input = $this->input;

		$cid       = $input->post->get('cid', array(), 'array');
		$totalCids = count($cid);

		if (!is_array($cid) || $totalCids < 1)
		{
			$msg = $this->translate('JLIB_APPLICATION_ERROR_NO_ITEM_SELECTED');
			$this->abort($msg, 'error');

			return false;
		}


		// Make sure the item ids are integers
		$cid = $this->cleanCid($cid);

		try
		{
			$model->delete($cid);
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->abort($msg, 'error');

			return false;
		}

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&task=display.' . $config['subject'];
		$msg    = $this->translate('JLIB_APPLICATION_MSG_ITEMS_DELETED');
		$this->setRedirect($url, $msg, 'message');

		return true;
	}
}