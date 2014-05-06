<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerStateOrderup extends JControllerStateBase
{
	public function execute()
	{
		try
		{
			parent::execute();
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->abort($msg, 'warning');

			return false;
		}

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&task=display.' . $config['subject'];
		$msg    = $this->translate('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
		$this->setRedirect($url, $msg, 'message');

		return true;
	}

	/**
	 * @param JModelAdministrator $model
	 * @param array               $cid
	 *
	 * @see JControllerStateBase::execute()
	 */
	protected function updateRecordState($model, $cid)
	{
		$model->reorder($cid, 'up');
	}
}