<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\Controller;

defined('_JEXEC') or die;

use JText,
	JSession,
	JControllerBase;

use Installation\Model\SetupModel,
	Installation\Model\DatabaseModel;

/**
 * Controller class to backup the old database for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class InstallDatabaseBackupController extends JControllerBase
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
		/* @var $app \Installation\Application\WebApplication */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new \Exception(JText::_('JINVALID_TOKEN'), 403));

		$state = new \JRegistry;
		$state->set('administratorPath', $app->get('administratorPath'));
		$state->set('installationPath', $app->get('installationPath'));

		// Get the setup model.
		$model = new SetupModel($state);

		// Get the options from the session
		$options = $model->getOptions();

		// Get the database model.
		$db = new DatabaseModel($state);

		// Attempt to create the database tables.
		$return = $db->handleOldDatabase($options);

		$r = new \stdClass;
		$r->view = 'install';

		// Check if the database was initialised
		if (!$return)
		{
			$r->view = 'database';
		}

		$app->sendJsonResponse($r);
	}
}
