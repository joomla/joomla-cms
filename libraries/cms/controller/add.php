<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerAdd extends JControllerAdministrate
{
	public function execute()
	{
		/** @var JModelAdministrator $model */
		$model = $this->getModel($this->config['resource']);

		$context = $model->getContext();
		$editId = $this->getUserState($context . '.edit.id', 0);

		//check in previously checked out record
		if($editId != 0)
		{
			$model->checkin($editId);
		}

		//clear the session variables
		$this->setUserState($context . '.edit.id', 0);
		$this->setUserState($context . '.jform.data', null);

		$config = $this->config;
		$url = 'index.php?option='.$config['option'].'&view='.$config['view'].'&layout=form';
		$this->setReturn($url);

		return true;
	}
}