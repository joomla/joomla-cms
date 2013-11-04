<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Module controller class.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 * @since       1.6
 */
class ModulesControllerModule extends JControllerForm
{
	/**
	 * Override parent add method.
	 *
	 * @return  mixed  True if the record can be added, a JError object if not.
	 *
	 * @since   1.6
	 */
	public function add()
	{
		$app = JFactory::getApplication();

		// Get the result of the parent method. If an error, just return it.
		$result = parent::add();
		if ($result instanceof Exception)
		{
			return $result;
		}

		// Look for the Extension ID.
		$extensionId = $app->input->get('eid', 0, 'int');
		if (empty($extensionId))
		{
			$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_item.'&layout=edit', false));
			return JError::raiseWarning(500, JText::_('COM_MODULES_ERROR_INVALID_EXTENSION'));
		}

		$app->setUserState('com_modules.add.module.extension_id', $extensionId);
		$app->setUserState('com_modules.add.module.params', null);

		// Parameters could be coming in for a new item, so let's set them.
		$params = $app->input->get('params', array(), 'array');
		$app->setUserState('com_modules.add.module.params', $params);
	}

	/**
	 * Override parent cancel method to reset the add module state.
	 *
	 * @param   string  $key  The name of the primary key of the URL variable.
	 *
	 * @return  boolean  True if access level checks pass, false otherwise.
	 *
	 * @since   1.6
	 */
	public function cancel($key = null)
	{
		$app = JFactory::getApplication();

		$result = parent::cancel();

		$app->setUserState('com_modules.add.module.extension_id', null);
		$app->setUserState('com_modules.add.module.params', null);

		return $result;
	}

	/**
	 * Override parent allowSave method.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   1.6
	 */
	protected function allowSave($data, $key = 'id')
	{
		// use custom position if selected
		if (isset($data['custom_position']))
		{
			if (empty($data['position']))
			{
				$data['position'] = $data['custom_position'];
			}

			unset($data['custom_position']);
		}

		return parent::allowSave($data, $key);
	}

	/**
	 * Method override to check if you can edit an existing record.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean
	 *
	 * @since   3.2
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Initialise variables.
		$recordId = (int) isset($data[$key]) ? $data[$key] : 0;
		$user = JFactory::getUser();
		$userId = $user->get('id');

		// Check general edit permission first.
		if ($user->authorise('core.edit', 'com_modules.module.' . $recordId))
		{
			return true;
		}

		// Since there is no asset tracking, revert to the component permissions.
		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   string  $model  The model
	 *
	 * @return  boolean  True on success.
	 *
	 * @since   1.7
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model	= $this->getModel('Module', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_modules&view=modules'.$this->getRedirectToListAppend(), false));

		return parent::batch($model);
	}

	/**
	 * Function that allows child controller access to model data after the data has been saved.
	 *
	 * @param   JModelLegacy  $model      The data model object.
	 * @param   array         $validData  The validated data.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		$app = JFactory::getApplication();
		$task = $this->getTask();

		switch ($task)
		{
			case 'save2new':
				$app->setUserState('com_modules.add.module.extension_id', $model->getState('module.extension_id'));
				break;

			default:
				$app->setUserState('com_modules.add.module.extension_id', null);
				break;
		}

		$app->setUserState('com_modules.add.module.params', null);
	}

	/**
	 * Save fuction for com_modules
	 *
	 * @see JControllerForm::save()
	 */
	public function save($key = null, $urlVar = null)
	{
		if (!JSession::checkToken())
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JINVALID_TOKEN'));
		}

		$document = JFactory::getDocument();

		if ($document->getType() == 'json')
		{

			$app   = JFactory::getApplication();
			$lang  = JFactory::getLanguage();
			$model = $this->getModel();
			$table = $model->getTable();
			$data  = $this->input->post->get('jform', array(), 'array');
			$checkin = property_exists($table, 'checked_out');
			$context = "$this->option.edit.$this->context";
			$task = $this->getTask();
			$item = $model->getItem($this->input->get('id'));

			$properties = $item->getProperties();

			// Merge 'params' array seperately
			$data['params'] = array_merge($properties['params'], $data['params']);

			// Merge other properties
			$data = array_merge($properties, $data);

			$key = $table->getKeyName();
			$urlVar = $key;

			$recordId = $this->input->getInt($urlVar);

			// Access check.
			if (!$this->allowSave($data, $key))
			{

				$app->enqueueMessage(JText::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

				return false;
			}

			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_modules/models/forms');

			// Validate the posted data.
			// Sometimes the form needs some posted data, such as for plugins and modules.
			$form = $model->getForm($data, false);

			if (!$form)
			{
				$app->enqueueMessage($model->getError(), 'error');

				return false;
			}

			// Test whether the data is valid.
			$validData = $model->validate($form, $data);

			if ($validData === false)
			{
				// Get the validation messages.
				$errors = $model->getErrors();

				// Push up to three validation messages out to the user.
				for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
				{
					if ($errors[$i] instanceof Exception)
					{
						$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
					}
					else
					{
					$app->enqueueMessage($errors[$i], 'warning');
					}
				}

				// Save the data in the session.
				$app->setUserState($context . '.data', $data);

				return false;
			}

			if (!isset($validData['tags']))
			{
				$validData['tags'] = null;
			}

			// Attempt to save the data.
			if (!$model->save($validData))
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');

				return false;
			}

			// Save succeeded, so check-in the record.
			if ($checkin && $model->checkin($validData[$key]) === false)
			{
				// Save the data in the session.
				$app->setUserState($context . '.data', $validData);

				// Check-in failed, so go back to the record and display a notice.
				$app->enqueueMessage(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()), 'error');

				return false;
			}

			// Redirect the user and adjust session state
			// Set the record data in the session.
			$recordId = $model->getState($this->context . '.id');
			$this->holdEditId($context, $recordId);
			$app->setUserState($context . '.data', null);
			$model->checkout($recordId);

			// Invoke the postSave method to allow for the child class to access the model.
			$this->postSaveHook($model, $validData);

			return true;
		}
		else
		{
			parent::save($key, $urlVar);
		}
	}

}
