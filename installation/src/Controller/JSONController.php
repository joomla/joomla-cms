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

use Joomla\CMS\Installation\Response\JsonResponse;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Factory;

/**
 * Default JSON controller class for the Joomla Installer controllers.
 *
 * @since  4.0.0
 */
abstract class JSONController extends BaseController
{
	/**
	 * Method to send a JSON response. The data parameter
	 * can be an Exception object for when an error has occurred or
	 * a JsonResponse for a good response.
	 *
	 * @param   mixed  $response  JsonResponse on success, Exception on failure.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	protected function sendJsonResponse($response)
	{
		// Very crude workaround to give an error message when JSON is disabled
		if (!function_exists('json_encode') || !function_exists('json_decode'))
		{
			$this->app->setHeader('status', 500);
			$this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
			$this->app->sendHeaders();
			echo '{"token":"' . Session::getFormToken(true) . '","lang":"' . Factory::getLanguage()->getTag()
				. '","error":true,"header":"' . \JText::_('INSTL_HEADER_ERROR') . '","message":"' . \JText::_('INSTL_WARNJSON') . '"}';
			$this->app->close();
		}

		// Check if we need to send an error code.
		if ($response instanceof \Exception)
		{
			// Send the appropriate error code response.
			$this->app->setHeader('status', $response->getCode());
			$this->app->setHeader('Content-Type', 'application/json; charset=utf-8');
			$this->app->sendHeaders();
		}

		// Send the JSON response.
		echo json_encode(new JsonResponse($response));

		// Close the application.
		$this->app->close();
	}

	/**
	 * Checks for a form token, if it is invalid a JSOn response with the error code 403 is sent.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @see     Session::checkToken()
	 */
	public function checkValidToken()
	{
		// Check for request forgeries.
		Session::checkToken() or $this->sendJsonResponse(new \Exception(\JText::_('JINVALID_TOKEN'), 403));
	}
}
