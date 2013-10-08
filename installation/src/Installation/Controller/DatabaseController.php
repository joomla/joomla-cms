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
	Installation\Model\DatabaseModel,
	Installation\Application\WebApplication;

/**
 * Controller class to initialise the database for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class DatabaseController extends JControllerBase
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
		/* @var WebApplication $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new \Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the setup model.
		$model = new SetupModel;

		// Check the form
		$vars = $model->checkForm('database');

		// Determine if the configuration file path is writable.
		$path = JPATH_CONFIGURATION . '/configuration.php';
		$useftp = (file_exists($path)) ? !is_writable($path) : !is_writable(JPATH_CONFIGURATION . '/');

		$r = new \stdClass;
		$r->view = $useftp ? 'ftp' : 'summary';

		// Get the database model.
		$db = new DatabaseModel;

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
