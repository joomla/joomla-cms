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
class InstallationControllerSite extends JControllerBase
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

		// Set the page redirect.
		$r = new stdClass;
		$r->view = 'database';

		// Check the form
		if ((new InstallationModelSetup)->checkForm('site') === false)
		{
			$r->view = 'site';
		}

		$app->sendJsonResponse($r);
	}
}
