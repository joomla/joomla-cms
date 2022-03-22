<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Controller class to set the language for the Joomla Installer.
 *
 * @since  3.1
 */
class InstallationControllerSetlanguage extends JControllerBase
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

		// Very crude workaround to give an error message when JSON is disabled
		if (!function_exists('json_encode') || !function_exists('json_decode'))
		{
			$app->setHeader('status', 500);
			$app->setHeader('Content-Type', 'application/json; charset=utf-8');
			$app->sendHeaders();
			echo '{"token":"' . JSession::getFormToken(true) . '","lang":"' . JFactory::getLanguage()->getTag()
				. '","error":true,"header":"' . JText::_('INSTL_HEADER_ERROR') . '","message":"' . JText::_('INSTL_WARNJSON') . '"}';
			$app->close();
		}

		// Check for potentially unwritable session
		$session = JFactory::getSession();

		if ($session->isNew())
		{
			$this->sendResponse(new Exception(JText::_('INSTL_COOKIES_NOT_ENABLED'), 500));
		}

		// Get the setup model.
		$model = new InstallationModelSetup;

		// Get the posted values from the request and validate them.
		$data   = $this->input->post->get('jform', array(), 'array');
		$return = $model->validate($data, 'preinstall');

		$r = new stdClass;

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to
			 * redirect back to the site setup screen.
			 */
			$r->view = $this->input->getWord('view', 'site');
			$app->sendJsonResponse($r);
		}

		// Store the options in the session.
		$model->storeOptions($return);

		// Setup language
		$language = JFactory::getLanguage();
		$language->setLanguage($return['language']);

		// Redirect to the page.
		$r->view = $this->input->getWord('view', 'site');
		$app->sendJsonResponse($r);
	}
}
