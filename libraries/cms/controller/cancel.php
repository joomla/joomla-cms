<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JCmsControllerCancel extends JCmsControllerBase
{
	/**
	 * (non-PHPdoc)
	 * @see JController::execute()
	 */
	public function execute()
	{
		$config = $this->config;
		$url = 'index.php?option='.$config['option'].'&task=display.'.$config['subject'];

		$prefix = $this->getPrefix();
		$model = $this->getModel($prefix, $config['subject'], $config);
		$keyName = $model->getKeyName();

		$input = $this->input;
		$pk = $input->getInt($keyName, 0);

		if ($pk != 0)
		{
			try
			{
				$model->checkin($pk);
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				$this->setRedirect($url, $msg, 'warning');
				return false;
			}
		}

		$this->setRedirect($url);
		return true;
	}


}