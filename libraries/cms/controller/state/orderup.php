<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JCmsControllerStateOrderup extends JCmsControllerStateBase
{
	public function execute()
	{
		$config = $this->config;
		$input = $this->input;
		$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];

		try
		{
			parent::execute();
		}
		catch (Exception $e)
		{
			$msg = $e->getMessage();
			$this->setRedirect($url, $msg, 'warning');
			return false;
		}

		$msg = $this->translate('JLIB_APPLICATION_SUCCESS_ITEM_REORDERED');
		$this->setRedirect($url, $msg, 'message');
		return true;
	}

	/**
	 * (non-PHPdoc)
	 * @see JCmsControllerStateBase::execute()
	 */
	protected function updateRecordState($model, $cid)
	{
		$input = $this->input;

		$model->reorder($cid, 'up');
	}
}