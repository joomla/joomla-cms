<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Module controller class.
 *
 * @since  1.6
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
			$redirectUrl = 'index.php?option=' . $this->option . '&view=' . $this->view_item . '&layout=edit';

			$this->setRedirect(JRoute::_($redirectUrl, false));

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
		// Use custom position if selected
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

		// Zero record (id:0), return component edit permission by calling parent controller method
		if (!$recordId)
		{
			return parent::allowEdit($data, $key);
		}

		// Check edit on the record asset (explicit or inherited)
		if ($user->authorise('core.edit', 'com_modules.module.' . $recordId))
		{
			return true;
		}

		return false;
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
		$model = $this->getModel('Module', '', array());

		// Preset the redirect
		$redirectUrl = 'index.php?option=com_modules&view=modules' . $this->getRedirectToListAppend();

		$this->setRedirect(JRoute::_($redirectUrl, false));

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
	 * Method to save a record.
	 *
	 * @param   string  $key     The name of the primary key of the URL variable.
	 * @param   string  $urlVar  The name of the URL variable if different from the primary key
	 *
	 * @return  boolean  True if successful, false otherwise.
	 */
	public function save($key = null, $urlVar = null)
	{
		if (!JSession::checkToken())
		{
			JFactory::getApplication()->redirect('index.php', JText::_('JINVALID_TOKEN'));
		}

		if (JFactory::getDocument()->getType() == 'json')
		{
			$model = $this->getModel();
			$data  = $this->input->post->get('jform', array(), 'array');
			$item = $model->getItem($this->input->get('id'));
			$properties = $item->getProperties();

			if (isset($data['params']))
			{
				unset($properties['params']);
			}

			// Replace changed properties
			$data = array_replace_recursive($properties, $data);

			if (!empty($data['assigned']))
			{
				$data['assigned'] = array_map('abs', $data['assigned']);
			}

			// Add new data to input before process by parent save()
			$this->input->post->set('jform', $data);

			// Add path of forms directory
			JForm::addFormPath(JPATH_ADMINISTRATOR . '/components/com_modules/models/forms');

		}

		parent::save($key, $urlVar);

	}

	/**
	 * Method to get the other modules in the same position
	 *
	 * @return  string  The data for the Ajax request.
	 *
	 * @since   3.6.3
	 */
	public function orderPosition()
	{
		$app = JFactory::getApplication();

		// Send json mime type.
		$app->mimeType = 'application/json';
		$app->setHeader('Content-Type', $app->mimeType . '; charset=' . $app->charSet);
		$app->sendHeaders();

		// Check if user token is valid.
		if (!JSession::checkToken('get'))
		{
			$app->enqueueMessage(JText::_('JINVALID_TOKEN'), 'error');
			echo new JResponseJson;
			$app->close();
		}

		$jinput   = $app->input;
		$clientId = $jinput->getValue('client_id');
		$position = $jinput->getValue('position');

		$db    = JFactory::getDbo();
		$query = $db->getQuery(true)
			->select('position, ordering, title')
			->from('#__modules')
			->where('client_id = ' . (int) $clientId . ' AND position = ' . $db->q($position))
			->order('ordering');

		$db->setQuery($query);

		try
		{
			$orders = $db->loadObjectList();
		}
		catch (RuntimeException $e)
		{
			JError::raiseWarning(500, $e->getMessage());

			return '';
		}

		$orders2 = array();
		$n = count($orders);

		if ($n > 0)
		{
			for ($i = 0, $n; $i < $n; $i++)
			{
				if (!isset($orders2[$orders[$i]->position]))
				{
					$orders2[$orders[$i]->position] = 0;
				}

				$orders2[$orders[$i]->position]++;
				$ord = $orders2[$orders[$i]->position];
				$title = JText::sprintf('COM_MODULES_OPTION_ORDER_POSITION', $ord, htmlspecialchars($orders[$i]->title, ENT_QUOTES, 'UTF-8'));

				$html[] = $orders[$i]->position . ',' . $ord . ',' . $title;
			}
		}
		else
		{
			$html[] = $position . ',' . 1 . ',' . JText::_('JNONE');
		}

		echo new JResponseJson($html);
		$app->close();
	}
}
