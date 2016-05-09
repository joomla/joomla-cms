<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die();

use Joomla\Registry\Registry;

class FieldsControllerField extends JControllerForm
{

	private $internalContext;

	private $component;

	public function __construct ($config = array())
	{
		parent::__construct($config);

		$this->internalContext = $this->input->getCmd('context', 'com_content.article');
		$parts = FieldsHelper::extract($this->internalContext);
		$this->component = $parts ? $parts[0] : null;
	}

	public function catchange ()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$app = JFactory::getApplication();
		$data = $this->input->get($this->input->get('formcontrol', 'jform'), array(), 'array');

		$parts = FieldsHelper::extract($this->input->getCmd('context'));
		if ($parts)
		{
			$app->setUserState($parts[0] . '.edit.' . $parts[1] . '.data', $data);
		}
		$app->redirect(base64_decode($this->input->get->getBase64('return')));
		$app->close();
	}

	protected function allowAdd ($data = array())
	{
		return JFactory::getUser()->authorise('core.create', $this->component);
	}

	protected function allowEdit ($data = array(), $key = 'parent_id')
	{
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', $this->component))
		{
			return true;
		}

		// Check specific edit permission.
		if ($user->authorise('core.edit', $this->internalContext . '.field.' . $recordId))
		{
			return true;
		}

		// Fallback on edit.own.
		// First test if the permission is available.
		if ($user->authorise('core.edit.own', $this->internalContext . '.field.' . $recordId) || $user->authorise('core.edit.own', $this->component))
		{
			// Now test the owner is the user.
			$ownerId = (int) isset($data['created_user_id']) ? $data['created_user_id'] : 0;

			if (empty($ownerId) && $recordId)
			{
				// Need to do a lookup from the model.
				$record = $this->getModel()->getItem($recordId);

				if (empty($record))
				{
					return false;
				}

				$ownerId = $record->created_user_id;
			}

			// If the owner matches 'me' then do the test.
			if ($ownerId == $userId)
			{
				return true;
			}
		}

		return false;
	}

	public function batch ($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('Field');

		// Preset the redirect
		$this->setRedirect('index.php?option=com_fields&view=fields&context=' . $this->internalContext);

		return parent::batch($model);
	}

	protected function getRedirectToItemAppend ($recordId = null, $urlVar = 'id')
	{
		$append = parent::getRedirectToItemAppend($recordId);
		$append .= '&context=' . $this->internalContext;

		return $append;
	}

	protected function getRedirectToListAppend ()
	{
		$append = parent::getRedirectToListAppend();
		$append .= '&context=' . $this->internalContext;

		return $append;
	}

	protected function postSaveHook (JModelLegacy $model, $validData = array())
	{
		$item = $model->getItem();

		if (isset($item->params) && is_array($item->params))
		{
			$registry = new Registry();
			$registry->loadArray($item->params);
			$item->params = (string) $registry;
		}

		return;
	}
}
