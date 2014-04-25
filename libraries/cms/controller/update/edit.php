<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Captcha
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JCmsControllerUpdateEdit extends JCmsControllerUpdateBase
{
	public function execute()
	{
		if (parent::execute())
		{
			$config = $this->config;
			$input = $this->input;
				
			$prefix = $this->getPrefix();
			$model = $this->getModel($prefix, $config['subject'], $config);
			$keyName = $model->getKeyName();
			$keyValue = $input->getInt($keyName);
				
			$url = 'index.php?option='.$config['option'].'&task=edit.'.$config['subject'];
			$url .='&'.$keyName.'='.$keyValue;
				
			$msg = $this->translate('JLIB_APPLICATION_MSG_SAVE_COMPLETED');
			$this->setRedirect($url, $msg, 'message');
			return true;
		}

		return false;
	}

}