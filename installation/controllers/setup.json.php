<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 * - JSON Protocol -
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationControllerSetup extends JControllerLegacy
{
	/**
	 * Method to set the setup language for the application.
	 *
	 * @return  void
	 *
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
			echo '{"token":"' . JSession::getFormToken(true) . '","lang":"' . JFactory::getLanguage()->getTag() . '","error":true,"header":"' . JText::_('INSTL_HEADER_ERROR') . '","message":"' . JText::_('INSTL_WARNJSON') . '"}';
			$app->close();
		}

		// Check for potentially unwritable session
		$session = JFactory::getSession();

		if ($session->isNew())
		{
			$this->sendResponse(new Exception(JText::_('INSTL_COOKIES_NOT_ENABLED'), 500));
		}

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = $this->input->post->get('jform', array(), 'array');
		$return	= $model->validate($data, 'preinstall');

		$r = new stdClass;

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors	= $model->getErrors();

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

			// Redirect to the page.
			$r->view = $this->input->getWord('view', 'site');
			$this->sendResponse($r);

			return false;
		}

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		// Setup language
		$language = JFactory::getLanguage();
		$language->setLanguage($return['language']);

		// Redirect to the page.
		$r->view = $this->input->getWord('view', 'site');
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function preinstall()
	{
		$r = new stdClass;
		$r->view = 'preinstall';
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function site()
	{
		if (!($vars = $this->checkForm('site')))
		{
			return false;
		}

		$r = new stdClass;
		$r->view = 'database';
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function database()
	{
		if (!($vars = $this->checkForm('database')))
		{
			return false;
		}

		// Determine if the configuration file path is writable.
		$path = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = (file_exists($path)) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$r = new stdClass;
		$r->view = $useftp ? 'ftp' : 'summary';

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to initialise the database.
		$return = $database->createDatabase($vars);

		// Check if the database was initialised
		if (!$return)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($database->getError(), 'notice');
			$r->view = 'database';
		}

		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function ftp()
	{
		if (!($vars = $this->checkForm('ftp')))
		{
			return false;
		}

		$r = new stdClass;
		$r->view = 'summary';
		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function summary()
	{
		if (!($vars = $this->checkForm('summary')))
		{
			return false;
		}

		$r = new stdClass;
		$r->view = 'install';
		$this->sendResponse($r);
	}


	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_database_remove()
	{
		$this->install_database_backup();
	}


	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_database_backup()
	{
		$r = new stdClass;
		$r->view = 'install';

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
		$options = $model->getOptions();

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to create the database tables.
		$return = $database->handleOldDatabase($options);

		// Check if creation of database tables was successful
		if (!$return)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($database->getError(), 'notice');
			$r->view = 'database';
		}

		$this->sendResponse($r);
	}


	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_database()
	{
		$r = new stdClass;
		$r->view = 'install';

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
		$options = $model->getOptions();

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to create the database tables.
		$return = $database->createTables($options);

		// Check if creation of database tables was successful
		if (!$return)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($database->getError(), 'notice');
			$r->view = 'database';
		}

		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_sample()
	{
		$r = new stdClass;
		$r->view = 'install';

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
		$options = $model->getOptions();

		// Get the database model.
		$database = $this->getModel('Database', 'InstallationModel', array('dbo' => null));

		// Attempt to load the database sample data.
		$return = $database->installSampleData($options);

		// If an error was encountered return an error.
		if (!$return)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($database->getError(), 'notice');
			$r->view = 'database';
		}

		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_config()
	{
		$r = new stdClass;
		$r->view = 'install';

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
		$options = $model->getOptions();

		// Get the configuration model.
		$configuration = $this->getModel('Configuration', 'InstallationModel', array('dbo' => null));

		// Attempt to setup the configuration.
		$return = $configuration->setup($options);

		// Ensure a language was set.
		if (!$return)
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage($configuration->getError(), 'notice');
			$r->view = 'site';
		}

		$this->sendResponse($r);
	}

	/**
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function install_email()
	{
		$r = new stdClass;
		$r->view = 'install';

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));
		$options = $model->getOptions();

		$name = $options['admin_user'];
		$email = $options['admin_email'];
		$subject = JText::sprintf(JText::_('INSTL_EMAIL_SUBJECT'), $options['site_name']);

		// Prepare email body
		$body = array();
		$body[] = JText::sprintf(JText::_('INSTL_EMAIL_HEADING'), $options['site_name']);
		$body[] = '';
		$body[] = array(JText::_('INSTL_SITE_NAME_LABEL'), $options['site_name']);

		$body[] = $this->emailTitle(JText::_('INSTL_COMPLETE_ADMINISTRATION_LOGIN_DETAILS'));
		$body[] = array(JText::_('JEMAIL'), $options['admin_email']);
		$body[] = array(JText::_('JUSERNAME'), $options['admin_user']);
		if ($options['summary_email_passwords'])
		{
			$body[] = array(JText::_('INSTL_ADMIN_PASSWORD_LABEL'), $options['admin_password']);
		}

		$body[] = $this->emailTitle(JText::_('INSTL_DATABASE'));
		$body[] = array(JText::_('INSTL_DATABASE_TYPE_LABEL'), $options['db_type']);
		$body[] = array(JText::_('INSTL_DATABASE_HOST_LABEL'), $options['db_host']);
		$body[] = array(JText::_('INSTL_DATABASE_USER_LABEL'), $options['db_user']);
		if ($options['summary_email_passwords'])
		{
			$body[] = array(JText::_('INSTL_DATABASE_PASSWORD_LABEL'), $options['db_pass']);
		}
		$body[] = array(JText::_('INSTL_DATABASE_NAME_LABEL'), $options['db_name']);
		$body[] = array(JText::_('INSTL_DATABASE_PREFIX_LABEL'), $options['db_prefix']);

		if (isset($options['ftp_enable']) && $options['ftp_enable'])
		{
			$body[] = $this->emailTitle(JText::_('INSTL_FTP'));
			$body[] = array(JText::_('INSTL_FTP_USER_LABEL'), $options['ftp_user']);
			if ($options['summary_email_passwords'])
			{
				$body[] =array( JText::_('INSTL_FTP_PASSWORD_LABEL'), $options['ftp_pass']);
			}
			$body[] = array(JText::_('INSTL_FTP_HOST_LABEL'), $options['ftp_host']);
			$body[] = array(JText::_('INSTL_FTP_PORT_LABEL'), $options['ftp_port']);
		}

		$max = 0;
		foreach ($body as $line)
		{
			if (is_array($line))
			{
				$max = max(array($max, strlen($line['0'])));
			}
		}

		foreach ($body as $i => $line)
		{
			if (is_array($line))
			{
				$label = $line['0'];
				$label .= ': '.str_repeat(' ', $max-strlen($label));
				$body[$i] = $label.$line['1'];
			}
		}
		$body = implode("\r\n", $body);

		$mail = JFactory::getMailer();
		$mail->addRecipient($email);
		$mail->addReplyTo($email, $name);
		$mail->setSender(array($email, $name));
		$mail->setSubject($subject);
		$mail->setBody($body);
		$sent = $mail->Send();

		if (($sent instanceof Exception))
		{
			$app = JFactory::getApplication();
			$app->enqueueMessage(JText::_('INSTL_EMAIL_NOT_SENT'), 'notice');
			$r->view = 'complete';
		}

		$this->sendResponse($r);
	}


	/**
	 * @return  string
	 *
	 * @since   3.0
	 */
	function emailTitle($title)
	{
		return "\r\n".$title."\r\n".str_repeat('=', strlen($title));
	}

	/**
	 * @return  array
	 *
	 * @since   3.0
	 */
	function checkForm($page = 'site')
	{

		// Check for request forgeries.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Get the posted values from the request and validate them.
		$data = $this->input->post->get('jform', array(), 'array');
		$return	= $model->validate($data, $page);

		// Attempt to save the data before validation
		$form = $model->getForm();
		$data = $form->filter($data);
		unset($data['admin_password2']);
		$model->storeOptions($data);

		// Check for validation errors.
		if ($return === false)
		{

			// Get the validation messages.
			$errors	= $model->getErrors();

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

			// Redirect back to the page.
			$r = new stdClass;
			$r->view = $page;
			$this->sendResponse($r);

			return false;
		}

		unset($return['admin_password2']);

		// Store the options in the session.
		$vars = $model->storeOptions($return);

		return $vars;
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
		$vars = $this->input->get('jform', array(), 'array');

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$ftp = $this->getModel('FTP', 'InstallationModel', array('dbo' => null));

		// Attempt to detect the Joomla root from the ftp account.
		$return = $ftp->detectFtpRoot($vars);

		// If an error was encountered return an error.
		if (!$return)
		{
			$this->sendResponse(new Exception($ftp->getError(), 500));
		}

		// Create a response body.
		$r = new stdClass;
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
		$vars = $this->input->get('jform', array(), 'array');

		// Get the setup model.
		$model = $this->getModel('Setup', 'InstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$ftp = $this->getModel('FTP', 'InstallationModel', array('dbo' => null));

		// Verify the FTP settings.
		$return = $ftp->verifyFtpSettings($vars);

		// If an error was encountered return an error.
		if (!$return)
		{
			$this->sendResponse(new Exception($ftp->getError(), 500));
		}

		// Create a response body.
		$r = new stdClass;
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
		$vars = $this->input->get('jform', array(), 'array');

		$path = JPATH_INSTALLATION;

		// Check whether the folder still exists
		if (!file_exists($path))
		{
			$this->sendResponse(new Exception(JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_ALREADY_REMOVED'), 500));
		}

		// Check whether we need to use FTP
		$useFTP = false;
		if ((file_exists($path) && !is_writable($path)))
		{
			$useFTP = true;
		}

		// Check for safe mode
		if (ini_get('safe_mode'))
		{
			$useFTP = true;
		}

		// Enable/Disable override
		if (!isset($options->ftpEnable) || ($options->ftpEnable != 1))
		{
			$useFTP = false;
		}

		if ($useFTP == true)
		{

			// Connect the FTP client
			jimport('joomla.filesystem.path');

			$ftp = JClientFtp::getInstance($options->ftp_host, $options->ftp_port);
			$ftp->login($options->ftp_user, $options->ftp_pass);

			// Translate path for the FTP account
			$file = JPath::clean(str_replace(JPATH_CONFIGURATION, $options->ftp_root, $path), '/');
			$return = $ftp->delete($file);

			// Delete the extra XML file while we're at it
			if ($return)
			{
				$file = JPath::clean($options->ftp_root . '/joomla.xml');
				if (file_exists($file))
				{
					$return = $ftp->delete($file);
				}
			}

			$ftp->quit();
		}
		else
		{

			// Try to delete the folder.
			// We use output buffering so that any error message echoed JFolder::delete
			// doesn't land in our JSON output.
			ob_start();
			$return = JFolder::delete($path) && (!file_exists(JPATH_ROOT . '/joomla.xml') || JFile::delete(JPATH_ROOT . '/joomla.xml'));
			ob_end_clean();
		}

		// If an error was encountered return an error.
		if (!$return)
		{
			$this->sendResponse(new Exception(JText::_('INSTL_COMPLETE_ERROR_FOLDER_DELETE'), 500));
		}

		// Create a response body.
		$r = new stdClass;
		$r->text = JText::_('INSTL_COMPLETE_FOLDER_REMOVED');

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * Method to handle a send a JSON response. The data parameter
	 * can be a Exception object for when an error has occurred or
	 * a stdClss object for a good response.
	 *
	 * @param   object  $response  stdClass on success, Exception on failure.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function sendResponse($response)
	{

		// Check if we need to send an error code.
		if ($response instanceof Exception)
		{

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
		$this->lang = JFactory::getLanguage()->getTag();

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && count($messages))
		{
			foreach ($messages as $msg)
			{
				if (isset($msg['type']) && isset($msg['message']))
				{
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		// If messages exist add them to the output
		if (isset($lists) && is_array($lists))
		{
			$this->messages = $lists;
		}

		// Check if we are dealing with an error.
		if ($state instanceof Exception)
		{

			// Prepare the error response.
			$this->error   = true;
			$this->header  = JText::_('INSTL_HEADER_ERROR');
			$this->message = $state->getMessage();
		}
		else
		{

			// Prepare the response data.
			$this->error = false;
			$this->data  = $state;
		}
	}
}
