<?php
/**
 * @package     Joomla.Libraries
 * @subpackage Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerUpdateNew extends JControllerUpdateBase
{
	public function execute()
	{
		if (parent::execute())
		{
			$config = $this->config;
			$url = 'index.php?option='.$config['option'].'&task=add.'.$config['subject'];
				
			try
			{
				$this->checkin();
			}
			catch (Exception $e)
			{
				$msg = $e->getMessage();
				$this->setRedirect($url, $msg, 'warning', true, false);
			}
				
			$msg = $this->translate('JLIB_APPLICATION_MSG_SAVE_COMPLETED');
			$this->setRedirect($url, $msg, 'message');
			return true;
		}

		return false;
	}

}
