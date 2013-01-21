<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('InstallationControllerSetup', __DIR__ . '/setup.json.php');

/**
 * Setup controller for the Joomla Core Installer Languages feature.
 * - JSON Protocol -
 *
 * @package  Joomla.Installation
 * @since    3.0
 */
class InstallationControllerLanguages extends InstallationControllerSetup
{
	/**
	 * Constructor.
	 *
	 * @param   array  $config  An optional associative array of configuration settings.
	 *
	 * @since   3.0
	 */
	public function __construct($config = array())
	{
		// Overrides application config and set the configuration.php file so tokens and database works
		JFactory::$config = null;
		JFactory::getConfig(JPATH_SITE . '/configuration.php');
		JFactory::$session = null;
		parent::__construct();
	}

	/**
	 * Method to install languages to Joomla application.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public function installLanguages()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the application object.
		$app = JFactory::getApplication();

		// Get array of selected languages
		$lids = $this->input->get('cid', array(), 'array');
		JArrayHelper::toInteger($lids, array());

		// Get the languages model.
		$model = $this->getModel('Languages', 'InstallationModel');

		$return = false;

		if (!$lids)
		{
			// No languages have been selected
			$app->enqueueMessage(JText::_('INSTL_LANGUAGES_NO_LANGUAGE_SELECTED'));
		}
		else
		{
			// Install selected languages
			$return = $model->install($lids);
		}

		$r = new stdClass;

		// Check for validation errors.
		if ($return === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}

			// Redirect back to the language selection screen.
			$r->view = 'languages';
			$this->sendResponse($r);
		}

		// Create a response body.
		$r->view = 'defaultlanguage';

		// Send the response.
		$this->sendResponse($r);
	}

	/**
	 * Set the selected language as the main language to the Joomla! administrator
	 *
	 * @return  void
	 *
	 * @since	3.0
	 */
	function setDefaultLanguage()
	{
		// Check for a valid token. If invalid, send a 403 with the error message.
		JSession::checkToken() or $this->sendResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Get the languages model.
		$model = $this->getModel('Languages', 'InstallationModel');

		// Check for request forgeries
		$admin_lang = $this->input->getString('administratorlang', false);

		// Check that is an Lang ISO Code avoiding any injection.
		if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $admin_lang))
		{
			$admin_lang = 'en-GB';
		}

		$app = JFactory::getApplication();
		$r   = new stdClass;

		if (!$model->setDefault($admin_lang, 'administrator'))
		{
			// Create a error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_ADMIN_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_ADMIN_SET_DEFAULT', $admin_lang));
		}

		$frontend_lang = $this->input->getString('frontendlang', false);

		// check that is an Lang ISO Code avoiding any injection.
		if (!preg_match('/^[a-z]{2}(\-[A-Z]{2})?$/', $frontend_lang))
		{
			$frontend_lang = 'en-GB';
		}

		if (!$model->setDefault($frontend_lang, 'site'))
		{
			// Create a error response message.
			$app->enqueueMessage(JText::_('INSTL_DEFAULTLANGUAGE_FRONTEND_COULDNT_SET_DEFAULT'), 'error');
		}
		else
		{
			// Create a response body.
			$app->enqueueMessage(JText::sprintf('INSTL_DEFAULTLANGUAGE_FRONTEND_SET_DEFAULT', $frontend_lang));
		}

		// Create a response body.
		$r->view = 'remove';

		// Send the response.
		$this->sendResponse($r);
	}
}
