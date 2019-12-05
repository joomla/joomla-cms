<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
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
		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN_NOTICE'), 403));

		// Get the setup model.
		$model = new InstallationModelSetup;

		// Get the options from the session
		$options = $model->getOptions();

		// Get the database model.
		$db = new InstallationModelDatabase;

		// Attempt to create the database tables.
		$return = $db->installSampleData($options);

		$r = new stdClass;
		$r->view = 'install';

		// Check if the database was initialised
		if (!$return)
		{
			$r->view = 'database';
		}

		$app->sendJsonResponse($r);
	}
}
