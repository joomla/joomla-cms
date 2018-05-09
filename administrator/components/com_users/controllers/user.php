<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * User controller class.
 *
 * @since  1.6
 */
class UsersControllerUser extends JControllerForm
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 * @since  1.6
	 */
	protected $text_prefix = 'COM_USERS_USER';

	/**
	 * Overrides JControllerForm::allowEdit
	 *
	 * Checks that non-Super Admins are not editing Super Admins.
	 *
	 * @param   array   $data  An array of input data.
	 * @param   string  $key   The name of the key for the primary key.
	 *
	 * @return  boolean  True if allowed, false otherwise.
	 *
	 * @since   1.6
	 */
	protected function allowEdit($data = array(), $key = 'id')
	{
		// Check if this person is a Super Admin
		if (JAccess::check($data[$key], 'core.admin'))
		{
			// If I'm not a Super Admin, then disallow the edit.
			if (!JFactory::getUser()->authorise('core.admin'))
			{
				return false;
			}
		}

		return parent::allowEdit($data, $key);
	}

	/**
	 * Method to run batch operations.
	 *
	 * @param   object  $model  The model.
	 *
	 * @return  boolean  True on success, false on failure
	 *
	 * @since   2.5
	 */
	public function batch($model = null)
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Set the model
		$model = $this->getModel('User', '', array());

		// Preset the redirect
		$this->setRedirect(JRoute::_('index.php?option=com_users&view=users' . $this->getRedirectToListAppend(), false));

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
	 * @since   3.1
	 */
	protected function postSaveHook(JModelLegacy $model, $validData = array())
	{
		return;
	}

	/**
	 * Method to activate a user.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function activate()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		$data  = $this->input->post->get('jform', array(), 'array');

		if (empty($data['id']))
		{
			JError::raiseWarning(500, JText::_('COM_USERS_USERS_NO_ITEM_SELECTED'));
		}
		else
		{
			/*
			 *	Know if we call this for activate the user and send a email notification
			 *	Or for only send a email notification
			 *	In both case we active the user on table, but we show a different message
			 */
			$model = $this->getModel();
			$table = $model->getTable();
			$table->load($data['id']);
			$wasActive = $table->activation;

			// Change the state of the records.
			if (!$model->activate($data['id']))
			{
				JError::raiseWarning(500, $model->getError());
			}
			// Then we wanted active the user and send a email notification
			elseif (!empty($wasActive))
			{
				$this->setMessage(JText::sprintf('COM_USERS_USER_ACTIVATED_NOTIFIED', $data['name']));
			}
			// Then we wanted only send again the email notification
			else
			{
				$this->setMessage(JText::sprintf('COM_USERS_USER_NOTIFIED', $data['name']));
			}
		}

		$this->setRedirect('index.php?option=com_users&view=users');
	}
}
