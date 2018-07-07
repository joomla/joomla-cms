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
 * Controller class to detect the site's FTP root for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerDetectftproot extends JControllerBase
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
		// Get the application
		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the setup model.
		$model = new InstallationModelSetup;

		// Get the data
		$data = $app->input->post->get('jform', array(), 'array');

		// Store the options in the session.
		$vars = $model->storeOptions($data);

		// Get the database model.
		$ftp = new InstallationModelFtp;

		// Attempt to detect the Joomla root from the ftp account.
		$return = $ftp->detectFtpRoot($vars);

		// Build the response object
		$r = new stdClass;
		$r->view = 'ftp';

		// If we got a FTP root, add it to the response object
		if ($return)
		{
			$r->root = $return;
		}

		$app->sendJsonResponse($r);
	}
}
