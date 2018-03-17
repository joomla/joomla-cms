<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to set the FTP data for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerRemovefolder extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function execute()
	{
		// Get the application.
		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		$path = JPATH_INSTALLATION;

		// Check whether the folder still exists.
		if (!file_exists($path))
		{
			$app->sendJsonResponse(new Exception(JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_ALREADY_REMOVED', 'installation'), 500));
		}

		// Check whether we need to use FTP.
		$useFTP = false;

		if (file_exists($path) && !is_writable($path))
		{
			$useFTP = true;
		}

		// Check for safe mode.
		if (ini_get('safe_mode'))
		{
			$useFTP = true;
		}

		// Enable/Disable override.
		if (!isset($options->ftpEnable) || ($options->ftpEnable != 1))
		{
			$useFTP = false;
		}

		if ($useFTP == true)
		{
			// Connect the FTP client.
			$ftp = JClientFtp::getInstance($options->ftp_host, $options->ftp_port);
			$ftp->login($options->ftp_user, $options->ftp_pass);

			// Translate path for the FTP account.
			$file   = JPath::clean(str_replace(JPATH_CONFIGURATION, $options->ftp_root, $path), '/');
			$return = $ftp->delete($file);

			// Delete the extra XML file while we're at it.
			if ($return)
			{
				$file = JPath::clean($options->ftp_root . '/joomla.xml');

				if (file_exists($file))
				{
					$return = $ftp->delete($file);
				}
			}

			// Rename the robots.txt.dist file to robots.txt.
			if ($return)
			{
				$robotsFile = JPath::clean($options->ftp_root . '/robots.txt');
				$distFile   = JPath::clean($options->ftp_root . '/robots.txt.dist');

				if (!file_exists($robotsFile) && file_exists($distFile))
				{
					$return = $ftp->rename($distFile, $robotsFile);
				}
			}

			$ftp->quit();
		}
		else
		{
			/*
			 * Try to delete the folder.
			 * We use output buffering so that any error message echoed JFolder::delete
			 * doesn't land in our JSON output.
			 */
			ob_start();
			$return = JFolder::delete($path) && (!file_exists(JPATH_ROOT . '/joomla.xml') || JFile::delete(JPATH_ROOT . '/joomla.xml'));

			// Rename the robots.txt.dist file if robots.txt doesn't exist
			if ($return && !file_exists(JPATH_ROOT . '/robots.txt') && file_exists(JPATH_ROOT . '/robots.txt.dist'))
			{
				$return = JFile::move(JPATH_ROOT . '/robots.txt.dist', JPATH_ROOT . '/robots.txt');
			}

			ob_end_clean();
		}

		// If an error was encountered return an error.
		if (!$return)
		{
			$app->sendJsonResponse(new Exception(JText::sprintf('INSTL_COMPLETE_ERROR_FOLDER_DELETE', 'installation'), 500));
		}

		// Create a response body.
		$r = new stdClass;
		$r->text = JText::sprintf('INSTL_COMPLETE_FOLDER_REMOVED', 'installation');

		/*
		 * Send the response.
		 * This is a hack since by now, the rest of the folder is deleted and we can't make a new request
		 */
		$this->sendJsonResponse($r);
	}

	/**
	 * Method to send a JSON response. The data parameter
	 * can be an Exception object for when an error has occurred or
	 * a stdClass for a good response.
	 *
	 * @param   mixed  $response  stdClass on success, Exception on failure.
	 *
	 * @return  void
	 *
	 * @since   3.1
	 */
	public function sendJsonResponse($response)
	{
		// Check if we need to send an error code.
		if ($response instanceof Exception)
		{
			// Send the appropriate error code response.
			$this->setHeader('status', $response->getCode());
			$this->setHeader('Content-Type', 'application/json; charset=utf-8');
			$this->sendHeaders();
		}

		// Send the JSON response.
		JLoader::register('InstallationResponseJson', __FILE__);

		echo json_encode(new InstallationResponseJson($response));

		// Close the application.
		exit;
	}
}

/**
 * JSON Response class for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationResponseJson
{
	/**
	 * Constructor for the JSON response
	 *
	 * @param   mixed  $data  Exception if there is an error, otherwise, the session data
	 *
	 * @since   3.1
	 */
	public function __construct($data)
	{
		// The old token is invalid so send a new one.
		$this->token = JSession::getFormToken(true);

		// Get the language and send it's tag along.
		$this->lang = JFactory::getLanguage()->getTag();

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

		// Build the sorted message list.
		if (is_array($messages) && count($messages))
		{
			foreach ($messages as $msg)
			{
				if (isset($msg['type'], $msg['message']))
				{
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		// If messages exist add them to the output.
		if (isset($lists) && is_array($lists))
		{
			$this->messages = $lists;
		}

		// Check if we are dealing with an error.
		if ($data instanceof Exception)
		{
			// Prepare the error response.
			$this->error   = true;
			$this->header  = JText::_('INSTL_HEADER_ERROR');
			$this->message = $data->getMessage();
		}
		else
		{
			// Prepare the response data.
			$this->error = false;
			$this->data  = $data;
		}
	}
}
