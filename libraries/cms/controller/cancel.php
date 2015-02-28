<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCancel extends JControllerAdministrate
{
	public function execute()
	{
		/** @var JModelAdministrator $model */
		$model   = $this->getModel($this->config['resource']);
		$context = $model->getContext();
		$editId  = $this->getUserState($context . '.edit.id', 0);

		if ($editId != 0)
		{
			$model->checkin($editId);
		}

		$context = $model->getContext();
		//clear the form state
		$this->setUserState($context . '.jform.data', null);
		$this->setUserState($context . '.edit.id', null);

		$config = $this->config;
		$this->setReturn('index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=default');

		return $this->executeController();
	}
}