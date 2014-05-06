<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCreateClose extends JControllerCreateBase
{
	public function execute()
	{
		if (parent::execute())
		{
			$msg = $this->translate('JLIB_APPLICATION_MSG_SAVE_COMPLETED');
			$this->abort($msg, 'error');

			return true;
		}

		return false;
	}

}