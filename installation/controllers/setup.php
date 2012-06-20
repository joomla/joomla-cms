<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		3.0
 */
class InstallationControllerSetup extends JControllerLegacy
{
	/**
	 * Method to set the setup language for the application.
	 *
	 * @return	void
	 *
	 * @since	3.0
	 */
	public function setlanguage()
	{
		// Get the application object.
		$app = JFactory::getApplication();

		// Check for potentially unwritable session
		$session = JFactory::getSession();

		if ($session->isNew()) {
			JError::setErrorHandling(E_ERROR, 'message');
			JError::raise(E_ERROR, 500, JText::_('INSTL_COOKIES_NOT_ENABLED'));

			return false;
		}

		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'language');

		// Check for validation errors.
		if ($return === false) {

			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the language selection screen.
			$this->setRedirect('index.php?view=language');
			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		// Redirect to the next page.
		$this->setRedirect('index.php?view=preinstall');
	}

	/**
	 * @since	3.0
	 */
	function database()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'database');

		// Check for validation errors.
		if ($return === false) {
			// Store the options in the session.
			$vars = $model->storeOptions($data);

			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the database selection screen.
			$this->setRedirect('index.php?view=database');

			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to initialise the database.
		$return = $database->initialise($vars);

		// Check if the databasa was initialised
		if (!$return) {
			$this->setMessage($database->getError(), 'notice');
			$this->setRedirect('index.php?view=database');
		} else {
			// Mark sample content as not installed yet
			$data = array(
				'sample_installed' => '0'
			);
			$dummy = $model->storeOptions($data);

			$this->setRedirect('index.php?view=filesystem');
		}
	}

	/**
	 * @since	3.0
	 */
	function filesystem()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'filesystem');

		// Check for validation errors.
		if ($return === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the database selection screen.
			$this->setRedirect('index.php?view=filesystem');

			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		$this->setRedirect('index.php?view=site');
	}

	/**
	 * @since	3.0
	 */
	function saveconfig()
	{
		// Check for request forgeries.
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'site');

		// Attempt to save the data before validation
		$form = $model->getForm();
		$data = $form->filter($data);
		unset($data['admin_password2']);
		$model->storeOptions($data);

		// Check for validation errors.
		if ($return === false) {
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the database selection screen.
			$this->setRedirect('index.php?view=site');

			return false;
		}

		// Store the options in the session.
		unset($return['admin_password2']);
		$vars = $model->storeOptions($return);

		// Get the configuration model.
		$configuration = $this->getModel('Configuration', 'InstallationModel', array('dbo' => null));

		// Attempt to setup the configuration.
		$return = $configuration->setup($vars);

		// Ensure a language was set.
		if (!$return) {
			$this->setMessage($configuration->getError(), 'notice');
			$this->setRedirect('index.php?view=site');
		} else {
			$this->setRedirect('index.php?view=complete');
		}
	}
}
