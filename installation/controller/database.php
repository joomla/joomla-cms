<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
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
//		JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		(new InstallationModelSetup)->checkForm('setup');
		// Get the options from the session
		$options = (new InstallationModelSetup)->getOptions();

		// Determine if the configuration file path is writable.
		$path   = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = file_exists($path) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$r = new stdClass;
		$r->view = $useftp ? 'ftp' : 'install';

		// Attempt to initialise the database.
		if (!(new InstallationModelDatabase)->initialise($options)){
			if (!(new InstallationModelDatabase)->createDatabase($options))
			{
				$r->view = 'setup';
			}
		}

//		$app->redirect('index.php?view=remove');
		$app->sendJsonResponse($r);
	}
}
