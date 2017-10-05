<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Language;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

/**
 * Default controller class for the Joomla Installer.
 *
 * @since  3.1
 */
class LanguageController extends BaseController
{
	/**
	 * Sets the language.
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function set()
	{
		// Get the application
		$app = $this->app;

		// Check for request forgeries.
		// JSession::checkToken() or $app->sendJsonResponse(new Exception(JText::_('JINVALID_TOKEN'), 403));

		// Very crude workaround to give an error message when JSON is disabled
		if (!function_exists('json_encode') || !function_exists('json_decode'))
		{
			$app->setHeader('status', 500);
			$app->setHeader('Content-Type', 'application/json; charset=utf-8');
			$app->sendHeaders();
			echo '{"token":"' . Session::getFormToken(true) . '","lang":"' . Factory::getLanguage()->getTag()
				. '","error":true,"header":"' . \JText::_('INSTL_HEADER_ERROR') . '","message":"' . \JText::_('INSTL_WARNJSON') . '"}';
			$app->close();
		}

		// Check for potentially unwritable session
		$session = $app->getSession();

		if ($session->isNew())
		{
			$app->sendJsonResponse(new \Exception(\JText::_('INSTL_COOKIES_NOT_ENABLED'), 500));
		}

		// Get the setup model.
		$model = $this->getModel('Setup');

		// Get the posted values from the request and validate them.
		$data   = $this->input->post->get('jform', [], 'array');
		$return = $data;//$model->validate($data, 'preinstall');

		$r = new \stdClass;

		// Check for validation errors.
		if ($return === false)
		{
			/*
			 * The validate method enqueued all messages for us, so we just need to
			 * redirect back to the site setup screen.
			 */
			$r->view = $this->input->getWord('view', 'setup');
			$app->sendJsonResponse($r);
		}

		// Store the options in the session.
		$model->storeOptions($return);

		// Setup language
		Factory::$language = Language::getInstance($return['language']);

		// Redirect to the page.
		$r->view = $this->input->getWord('view', 'setup');
		$app->sendJsonResponse($r);
	}
}
