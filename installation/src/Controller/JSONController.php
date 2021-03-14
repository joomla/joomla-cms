<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Controller
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installation\Response\JsonResponse;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Session\Session;

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
		$this->app->mimeType = 'application/json';

		// Very crude workaround to give an error message when JSON is disabled
		if (!function_exists('json_encode') || !function_exists('json_decode'))
		{
			$this->app->setHeader('status', 500);
			echo '{"token":"' . Session::getFormToken(true) . '","lang":"' . Factory::getLanguage()->getTag()
				. '","error":true,"header":"' . Text::_('INSTL_HEADER_ERROR') . '","message":"' . Text::_('INSTL_WARNJSON') . '"}';

			return;
		}

		// Check if we need to send an error code.
		if ($response instanceof \Exception)
		{
			// Send the appropriate error code response.
			$this->app->setHeader('status', $response->getCode(), true);
		}

		// Send the JSON response.
		echo json_encode(new JsonResponse($response));
	}

	/**
	 * Checks for a form token, if it is invalid a JSON response with the error code 403 is sent.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 * @see     Session::checkToken()
	 */
	public function checkValidToken()
	{
		// Check for request forgeries.
		if (!Session::checkToken())
		{
			$this->sendJsonResponse(new \Exception(Text::_('JINVALID_TOKEN_NOTICE'), 403));

			$this->app->close();
		}
	}
}
