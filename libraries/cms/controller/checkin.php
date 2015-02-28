<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCheckin extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model = $this->getModel($this->config['resource']);
		$ids   = $this->getIds();

		foreach ($ids AS $id)
		{
			$model->checkin($id);
		}

		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_CHECKIN_SUCCEEDED'));

		//execute any controllers we might have
		return $this->executeController();
	}
}