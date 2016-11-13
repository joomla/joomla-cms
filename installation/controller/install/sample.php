<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to install the sample data for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerInstallSample extends JControllerBase
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
		/* @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the options from the session
		$options = (new InstallationModelSetup)->getOptions();

		$r = new stdClass;
		$r->view = 'install';

		// Check if the database was initialised
		if (!(new InstallationModelDatabase)->installSampleData($options))
		{
			$r->view = 'database';
		}

		$app->sendJsonResponse($r);
	}
}
