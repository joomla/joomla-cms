<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

/**
 * Controller class to install additional languages for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerInstallLanguages extends JControllerBase
{
	/**
	 * Constructor.
	 *
	 * @since   3.1
	 */
	public function __construct()
	{
		parent::__construct();

		/** @var InstallationApplicationWeb $app */
		$app = $this->getApplication();

		// Overrides application config and set the configuration.php file so tokens and database works
		$app->loadConfiguration(new JConfig);
	}

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

		// Get array of selected languages
		$lids = $this->input->get('cid', [], 'array');
		$lids = ArrayHelper::toInteger($lids, []);

		if (!$lids)
		{
			// No languages have been selected
			$app->enqueueMessage(JText::_('INSTL_LANGUAGES_NO_LANGUAGE_SELECTED'), 'warning');
		}
		else
		{
			// Get the languages model.
			$model = new InstallationModelLanguages;

			// Install selected languages
			$model->install($lids);

			// Publish the Content Languages.
			$model->publishContentLanguages();

			$app->enqueueMessage(JText::_('INSTL_LANGUAGES_MORE_LANGUAGES'), 'notice');
		}

		// Redirect to the page.
		$r = new stdClass;
		$r->view = 'defaultlanguage';
		$app->sendJsonResponse($r);
	}
}
