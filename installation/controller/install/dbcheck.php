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
 * Controller class to set the site data for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerInstallDbcheck extends JControllerBase
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

		// Redirect to the page.
		$r = new stdClass;
		$r->view = 'setup';

		// Check the form
		if ((new InstallationModelSetup)->checkForm('setup') === false || (new InstallationModelSetup)->initialise('setup') === false)
		{
			$r->messages = 'Check your DB credentials, db type, db name or hostname';
			$r->view = 'setup';
		}

		$app->sendJsonResponse($r);
	}
}
