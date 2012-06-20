<?php
/**
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 * - JSON Protocol -
 *
 * @package		Joomla.Installation
 * @since		3.0
 */
class InstallationControllerSetup extends JControllerLegacy
{
	/**
	 * Method to set the setup language for the application.
	 *
	 * @return  void
	 * @since   3.0
	 */
	public function setlanguage()
	{
		// Check for request forgeries.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the application object.
		$app = JFactory::getApplication();

		// Very crude workaround to give an error message when JSON is disabled
		if (!function_exists('json_encode') || !function_exists('json_decode'))
		{
			JResponse::setHeader('status', 500);
			JResponse::setHeader('Content-Type', 'application/json; charset=utf-8');
			JResponse::sendHeaders();
			echo '{"token":"'.JSession::getFormToken(true).'","lang":"'.JFactory::getLanguage()->getTag().'","error":true,"header":"'.JText::_('INSTL_HEADER_ERROR').'","message":"'.JText::_('INSTL_WARNJSON').'"}';
			$app->close();
		}

		// Check for potentially unwritable session
		$session = JFactory::getSession();

		if ($session->isNew()) {
			$this->sendResponse(new Exception(JText::_('INSTL_COOKIES_NOT_ENABLED'), 500));
		}

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'language');

		$r = new JObject();
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
			$r->view = 'language';
			$this->sendResponse($r);
			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		// Redirect to the next page.
		$r->view = 'preinstall';
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function database()
	{
		// Check for request forgeries.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'database');

		$r = new JObject();
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
			$r->view = 'database';
			$this->sendResponse($r);

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
			$app->enqueueMessage($database->getError(), 'notice');
			$r->view = 'database';
			$this->sendResponse($r);
		} else {
			// Mark sample content as not installed yet
			$data = array(
				'sample_installed' => '0'
			);
			$dummy = $model->storeOptions($data);

			$r->view = 'filesystem';
			$this->sendResponse($r);
		}
	}

	/**
	 * @return	void
	 *
	 * @since	3.0
	 */
	public function filesystem()
	{
		// Check for request forgeries.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = JRequest::getVar('jform', array(), 'post', 'array');
		$return	= $model->validate($data, 'filesystem');

		$r = new JObject();
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
			$r->view = 'filesystem';
			$this->sendResponse($r);

			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		$r->view = 'site';
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function saveconfig()
	{
		// Check for request forgeries.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

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

		$r = new JObject();
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

			// Redirect back to the configuration screen.
			$r->view = 'site';
			$this->sendResponse($r);

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
			$app->enqueueMessage($configuration->getError(), 'notice');
			$r->view = 'site';
		} else {
			$r->view = 'complete';
		}
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function loadSampleData()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the options from the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to load the database sample data.
		$return = $database->installSampleData($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new Exception($database->getError(), 500));
		} else {
			// Mark sample content as installed
			$data = array(
				'sample_installed' => '1'
			);
			$dummy = $model->storeOptions($data);
		}

		// Create a response body.
		$r = new JObject();
		$r->sampleDataLoaded = true;

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function detectFtpRoot()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$filesystem = $this->getModel('Filesystem', 'InstallationModel', array('dbo' => null));

		// Attempt to detect the Joomla root from the ftp account.
		$return = $filesystem->detectFtpRoot($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new Exception($filesystem->getError(), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->root = $return;

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function verifyFtpSettings()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$filesystem = $this->getModel('Filesystem', 'InstallationModel', array('dbo' => null));

		// Verify the FTP settings.
		$return = $filesystem->verifyFtpSettings($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new Exception($filesystem->getError(), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->valid = $return;

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function removeFolder()
	{
		jimport('joomla.filesystem.folder');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken('request') or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		$path = JPATH_INSTALLATION;
		//check whether the folder still exists
		if (!file_exists($path)) {
			$this->sendResponse(new Exception(JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_ALREADY_REMOVED'), 500));
		}

		// check whether we need to use FTP
		$useFTP = false;
		if ((file_exists($path) && !is_writable($path))) {
			$useFTP = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode')) {
			$useFTP = true;
		}

		// Enable/Disable override
		if (!isset($options->ftpEnable) || ($options->ftpEnable != 1)) {
			$useFTP = false;
		}

		if ($useFTP == true) {
			// Connect the FTP client
			jimport('joomla.client.ftp');
			jimport('joomla.filesystem.path');

			$ftp = JFTP::getInstance($options->ftp_host, $options->ftp_port);
			$ftp->login($options->ftp_user, $options->ftp_pass);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $options->ftp_root, $path), '/');
			$return = $ftp->delete($file);

			// Delete the extra XML file while we're at it
			if ($return) {
				$file = JPath::clean($options->ftp_root.'/joomla.xml');
				if (file_exists($file)) {
					$return = $ftp->delete($file);
				}
			}

			$ftp->quit();
		} else {
			// Try to delete the folder.
			// We use output buffering so that any error message echoed JFolder::delete
			// doesn't land in our JSON output.
			ob_start();
			$return = JFolder::delete($path) && (!file_exists(JPATH_ROOT.'/joomla.xml') || JFile::delete(JPATH_ROOT.'/joomla.xml'));
			ob_end_clean();
		}

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new Exception(JText::_('INSTL_COMPLETE_ERROR_FOLDER_DELETE'), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->text = JText::_('INSTL_COMPLETE_FOLDER_REMOVED');

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * Method to handle a send a JSON response. The data parameter
	 * can be a Exception object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @param	object	$response	JObject on success, Exception on failure.
	 *
	 * @return  void
	 * @since   3.0
	 */
	public function sendResponse($response)
	{
		// Check if we need to send an error code.
		if ($response instanceof Exception) {
			// Send the appropriate error code response.
			JResponse::setHeader('status', $response->getCode());
			JResponse::setHeader('Content-Type', 'application/json; charset=utf-8');
			JResponse::sendHeaders();
		}

		// Send the JSON response.
		echo json_encode(new InstallationJsonResponse($response));

		// Close the application.
		$app = JFactory::getApplication();
		$app->close();
	}
}

/**
 * Joomla Core Installation JSON Response Class
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationJsonResponse
{
	function __construct($state)
	{
		// The old token is invalid so send a new one.
		$this->token = JSession::getFormToken(true);

		// Get the language and send it's code along
		$lang = JFactory::getLanguage();
		$this->lang = $lang->getTag();

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && count($messages)) {
			foreach ($messages as $msg)
			{
				if (isset($msg['type']) && isset($msg['message'])) {
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		// If messages exist add them to the output
		if (isset($lists) && is_array($lists)) {
			$this->messages = $lists;
		}

		// Check if we are dealing with an error.
		if ($state instanceof Exception) {
			// Prepare the error response.
			$this->error	= true;
			$this->header	= JText::_('INSTL_HEADER_ERROR');
			$this->message	= $state->getMessage();
		} else {
			// Prepare the response data.
			$this->error	= false;
			$this->data		= $state;
		}
	}
}
