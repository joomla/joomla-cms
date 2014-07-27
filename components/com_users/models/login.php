<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Rest model class for Users.
 *
 * @package     Joomla.Site
 * @subpackage  com_users
 * @since       1.6
 */
class UsersModelLogin extends JModelForm
{
	/**
	 * Method to get the login form.
	 *
	 * The base form is loaded from XML and then an event is fired
	 * for users plugins to extend the form with extra fields.
	 *
	 * @param   array  $data		An optional array of data for the form to interogate.
	 * @param   boolean	$loadData	True if the form is to load its own data (default case), false if not.
	 * @return  JForm	A JForm object on success, false on failure
	 * @since   1.6
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Get the form.
		$form = $this->loadForm('com_users.login', 'login', array('load_data' => $loadData));
		if (empty($form))
		{
			return false;
		}

		return $form;
	}

	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  array  The default data is an empty array.
	 * @since   1.6
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered login form data.
		$app  = JFactory::getApplication();
		$data = $app->getUserState('users.login.form.data', array());

		$input = $app->input;
		$method = $input->getMethod();

		// Check for return URL from the request first
		if ($return = $input->$method->get('return', '', 'BASE64'))
		{
			$data['return'] = base64_decode($return);

			if (!JUri::isInternal($data['return']))
			{
				$data['return'] = '';
			}
		}

		// Set the return URL if empty.
		if (!isset($data['return']) || empty($data['return']))
		{
			$data['return'] = 'index.php?option=com_users&view=profile';
		}
		$app->setUserState('users.login.form.data', $data);

		$this->preprocessData('com_users.login', $data);

		return $data;
	}

	/**
	 * Method to auto-populate the model state.
	 *
	 * Note. Calling getState in this method will result in recursion.
	 *
	 * @since   1.6
	 */
	protected function populateState()
	{
		// Get the application object.
		$params	= JFactory::getApplication()->getParams('com_users');

		// Load the parameters.
		$this->setState('params', $params);
	}

	/**
	 * Override JModelAdmin::preprocessForm to ensure the correct plugin group is loaded.
	 *
	 * @param   JForm   $form   A JForm object.
	 * @param   mixed   $data   The data expected for the form.
	 * @param   string  $group  The name of the plugin group to import (defaults to "content").
	 *
	 * @return  void
	 *
	 * @since   1.6
	 * @throws  Exception if there is an error in the form event.
	 */
	protected function preprocessForm(JForm $form, $data, $group = 'user')
	{
		parent::preprocessForm($form, $data, $group);
	}
}
