<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationControllerSetup extends JController
{
	/**
	 * Method to set the setup language for the application.
	 *
	 * @access	public
	 * @return	void
	 * @since	1.0
	 */
	function setlanguage()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the application object.
		$app = & JFactory::getApplication();

		// Get the setup model.
		$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'language');

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
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

	function database()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the application object.
		$app = & JFactory::getApplication();

		// Get the setup model.
		$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'database');

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
				}
			}

			// Redirect back to the database selection screen.
			$this->setRedirect('index.php?view=database');
			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		// Get the database model.
		$database = & $this->getModel('Database', 'JInstallationModel', array('dbo' => null));

		// Attempt to initialize the database.
		$return = $database->initialize($vars);

		// Ensure a language was set.
		if (!$return) {
			$this->setMessage($database->getError(), 'notice');
			$this->setRedirect('index.php?view=database');
		}
		else {
			$this->setRedirect('index.php?view=filesystem');
		}
	}

	function filesystem()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the application object.
		$app = & JFactory::getApplication();

		// Get the setup model.
		$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'filesystem');

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
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

	function saveconfig()
	{
		// Check for request forgeries.
		JRequest::checkToken() or jexit(JText::_('Invalid_Token'));

		// Get the application object.
		$app = & JFactory::getApplication();

		// Get the setup model.
		$model = &$this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'site');

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if (JError::isError($errors[$i])) {
					$app->enqueueMessage($errors[$i]->getMessage(), 'notice');
				} else {
					$app->enqueueMessage($errors[$i], 'notice');
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
		$configuration = & $this->getModel('Configuration', 'JInstallationModel', array('dbo' => null));

		// Attempt to setup the configuration.
		$return = $configuration->setup($vars);

		// Ensure a language was set.
		if (!$return) {
			$this->setMessage($configuration->getError(), 'notice');
			$this->setRedirect('index.php?view=site');
		}
		else {
			$this->setRedirect('index.php?view=complete');
		}
	}
}