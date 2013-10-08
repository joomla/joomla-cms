<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Installation\Controller\Install;

defined('_JEXEC') or die;

use JText,
	JFactory,
	JSession,
	JArrayHelper,
	JControllerBase;

use Installation\Model\LanguagesModel;

/**
 * Controller class to install additional languages for the Joomla Installer.
 *
 * @package     Joomla.Installation
 * @subpackage  Controller
 * @since       3.1
 */
class LanguagesController extends JControllerBase
{
	/**
	 * Constructor.
	 *
	 * @since   3.1
	 */
	public function __construct()
	{
		parent::__construct();

		// Overrides application config and set the configuration.php file so tokens and database works
		JFactory::$config = null;
		JFactory::getConfig(JPATH_SITE . '/configuration.php');
		JFactory::$session = null;
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
		/* @var \Installation\Application\WebApplication $app */
		$app = $this->getApplication();

		// Check for request forgeries.
		JSession::checkToken() or $app->sendJsonResponse(new \Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get array of selected languages
		$lids = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($lids, array());

		// Get the languages model.
		$model = new LanguagesModel;

		if (!$lids)
		{
			// No languages have been selected
			$app->enqueueMessage(JText::_('INSTL_LANGUAGES_NO_LANGUAGE_SELECTED'));
		}
		else
		{
			// Install selected languages
			$model->install($lids);
		}

		// Redirect to the page.
		$r = new \stdClass;
		$r->view = 'defaultlanguage';
		$app->sendJsonResponse($r);
	}
}
