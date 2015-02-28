<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

class JControllerStore extends JControllerAdministrate
{
	public function execute()
	{
		//Check for request forgeries
		$this->validateSession();

		/** @var JModelAdministrator $model */
		$model   = $this->getModel($this->config['resource']);
		$keyName = $model->getKeyName();

		$data = $this->input->get('jform', array(), 'array');

		// I really hate this! mixing two tasks in one sucks.
		$this->checkACL($model, $data[$keyName]);

		$context = $model->getContext();
		$editId  = $this->getUserState($context . '.edit.id', 0);

		if ($data[$keyName] != $editId && (int) $data[$keyName] != 0)
		{
			$msg = JText::_('BABELU_LIB_ERROR_RECORD_ID_MISMATCH');
			throw new InvalidArgumentException($msg);
		}

		$this->commit($model, $data, $keyName);

		$item = $model->getItem();
		$model->checkout($item->$keyName);
		$this->setUserState($context . '.edit.id', $item->$keyName);
		$this->setUserState($context . '.jform.data', $item->getProperties());

		$config = $this->config;
		$url    = 'index.php?option=' . $config['option'] . '&view=' . $config['view'] . '&layout=form&id=' . $item->$keyName;;
		$this->addMessage(JText::_('BABELU_LIB_CONTROLLER_MESSAGE_SAVE_COMPLETED'));
		$this->setReturn($url);

		//execute any controllers we might have
		return $this->executeController();
	}

	protected function checkACL($model, $id)
	{
		$permission  = 'create';
		$acl_message = 'BABELU_LIB_ACL_ERROR_CREATE_RECORD_NOT_PERMITTED';

		if (!empty($id))
		{
			$permission  = 'edit';
			$acl_message = 'BABELU_LIB_ACL_ERROR_EDIT_RECORD_NOT_PERMITTED';
		}

		if (!$model->allowAction('core' . $permission))
		{
			$msg = JText::_($acl_message);
			throw new ErrorException($msg);
		}
	}

	protected function commit($model, $data, $keyName)
	{
		if (!empty($data[$keyName]))
		{
			$updateNulls = ($this->input->post->get('update_nulls', false, 'BOOLEAN') == 1);
			$model->update($data, array(), $updateNulls);

			return;
		}

		$model->create($data);
	}
}