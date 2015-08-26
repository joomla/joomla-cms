<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to initialise the database for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerDatabase extends JControllerBase
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

		// Get the setup model.
		$model = new InstallationModelSetup;

		// Check the form
		$vars = $model->checkForm('database');

		// Determine if the configuration file path is writable.
		$path = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = (file_exists($path)) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$r = new stdClass;
		$r->view = $useftp ? 'ftp' : 'summary';

		// Get the database model.
		$db = new InstallationModelDatabase;

		// Attempt to initialise the database.
		$return = $db->createDatabase($vars);

		// Check if the database was initialised
		if (!$return)
		{
			$r->view = 'database';
		}

		$app->sendJsonResponse($r);
	}
}
