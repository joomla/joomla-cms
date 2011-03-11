<?php
/**
 * @version		$Id$
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Setup controller for the Joomla Core Installer.
 * - JSON Protocol -
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationControllerSetup extends JController
{
	function loadSampleData()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Get the options from the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$database = $this->getModel('Database', 'JInstallationModel', array('dbo' => null));

		// Attempt to load the database sample data.
		$return = $database->installSampleData($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new JException($database->getError(), 500));
		} else {
			// Mark sample content as installed
			$data = array(
				'sample_installed' => '1'
			);
			$dummy = $model->storeOptions($data);
		}

		// Create a response body.
		$r = new JObject();
		$r->text = JText::_('INSTL_SITE_SAMPLE_LOADED');

		// Send the response.
		$this->sendResponse($r);
	}

	function detectFtpRoot()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$filesystem = $this->getModel('Filesystem', 'JInstallationModel', array('dbo' => null));

		// Attempt to detect the Joomla root from the ftp account.
		$return = $filesystem->detectFtpRoot($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new JException($filesystem->getError(), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->root = $return;

		// Send the response.
		$this->sendResponse($r);
	}

	function verifyFtpSettings()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		// Get the setup model.
		$model = $this->getModel('Setup', 'JInstallationModel', array('dbo' => null));

		// Store the options in the session.
		$vars = $model->storeOptions($vars);

		// Get the database model.
		$filesystem = $this->getModel('Filesystem', 'JInstallationModel', array('dbo' => null));

		// Verify the FTP settings.
		$return = $filesystem->verifyFtpSettings($vars);

		// If an error was encountered return an error.
		if (!$return) {
			$this->sendResponse(new JException($filesystem->getError(), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->valid = $return;

		// Send the response.
		$this->sendResponse($r);
	}
	
	function removeFolder()
	{
		jimport('joomla.filesystem.folder');

		// Check for a valid token. If invalid, send a 403 with the error message.
		JRequest::checkToken('request') or $this->sendResponse(new JException(JText::_('JINVALID_TOKEN'), 403));

		// Get the posted config options.
		$vars = JRequest::getVar('jform', array());

		$path = JPATH_INSTALLATION;
		//check whether the folder still exists
		if (!file_exists($path)) {
			$this->sendResponse(new JException(JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_ALREADY_REMOVED'), 500));
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
			if ($return)
			{
				$file = JPath::clean($options->ftp_root.'/joomla.xml');
				if (file_exists($file))
				{
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
			$this->sendResponse(new JException(JText::_('INSTL_COMPLETE_ERROR_FOLDER_DELETE'), 500));
		}

		// Create a response body.
		$r = new JObject();
		$r->text = JText::_('INSTL_COMPLETE_FOLDER_REMOVED');

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * Method to handle a send a JSON response. The data parameter
	 * can be a JException object for when an error has occurred or
	 * a JObject for a good response.
	 *
	 * @access	public
	 * @param	object	JObject on success, JException on failure.
	 * @return	void
	 * @since	1.6
	 */
	function sendResponse($response)
	{
		// Check if we need to send an error code.
		if (JError::isError($response))
		{
			// Send the appropriate error code response.
			JResponse::setHeader('status', $response->getCode());
			JResponse::setHeader('Content-Type', 'application/json; charset=utf-8');
			JResponse::sendHeaders();
		}

		// Send the JSON response.
		echo json_encode(new JInstallationJsonResponse($response));

		// Close the application.
		$app = JFactory::getApplication();
		$app->close();
	}
}

/**
 * Joomla Core Installation JSON Response Class
 *
 * @package		Joomla.Installation
 * @since		1.6
 */
class JInstallationJsonResponse
{
	function __construct($state)
	{
		// The old token is invalid so send a new one.
		$this->token = JUtility::getToken(true);

		// Check if we are dealing with an error.
		if (JError::isError($state))
		{
			// Prepare the error response.
			$this->error	= true;
			$this->header	= JText::_('INSTL_HEADER_ERROR');
			$this->message	= $state->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->error	= false;
			$this->data		= $state;
		}
	}
}

// Set the error handler.
//JError::setErrorHandling(E_ALL, 'callback', array('JInstallationControllerSetup', 'sendResponse'));
