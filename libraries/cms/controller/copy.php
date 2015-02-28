<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerCopy extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model   = $this->getModel($this->config['resource']);
		$context = $model->getContext();

		if (!$model->allowAction('core.create'))
		{
			$msg = JText::_('BABELU_LIB_ACL_ERROR_CREATE_RECORD_NOT_PERMITTED');
			throw new ErrorException($msg);
		}

		$input  = $this->input;
		$editId = $input->getInt('id', null);
		$data   = $input->get('jform', array(), 'array');

		if (!empty($editId))
		{
			$model->checkin($editId);
		}

		$model->create($data);
		$keyName = $model->getKeyName();

		$item = $model->getItem();
		$model->checkout($item->$keyName);
		$this->setUserState($context . '.edit.id', $item->$keyName);
		$this->setUserState($context . '.jform.data', $item->getProperties());

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=form&id=' . $item->$keyName;;
		$this->setReturn($url);

		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_SAVE_COMPLETED'));

		//execute any controllers we might have
		return $this->executeController();
	}

}