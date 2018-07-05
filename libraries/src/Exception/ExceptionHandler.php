<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Exception;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Error\AbstractRenderer;
use Joomla\CMS\Factory;
use Joomla\CMS\Log\Log;

/**
 * Displays the custom error page when an uncaught exception occurs.
 *
 * @since  3.0
 */
class ExceptionHandler
{
	/**
	 * Handles an error triggered with the E_USER_DEPRECATED level.
	 *
	 * @param   integer  $errorNumber   The level of the raised error, represented by the E_* constants.
	 * @param   string   $errorMessage  The error message.
	 * @param   string   $errorFile     The file the error was triggered from.
	 * @param   integer  $errorLine     The line number the error was triggered from.
	 *
	 * @return  boolean
	 *
	 * @since   4.0.0
	 */
	public static function handleUserDeprecatedErrors(int $errorNumber, string $errorMessage, string $errorFile, int $errorLine): bool
	{
		// We only want to handle user deprecation messages, these will be triggered in code
		if ($errorNumber === E_USER_DEPRECATED)
		{
			Log::add(
				$errorMessage,
				Log::WARNING,
				'deprecated'
			);

			// If debug mode is enabled, we want to let PHP continue to handle the error; otherwise, we can bail early
			if (defined('JDEBUG') && JDEBUG)
			{
				return true;
			}
		}

		// Always return false, this will tell PHP to handle the error internally
		return false;
	}

	/**
	 * Render the error page based on an exception.
	 *
	 * @param   \Throwable  $error  An Exception or Throwable (PHP 7+) object for which to render the error page.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function render(\Throwable $error)
	{
		$isCli = false;

		try
		{
			// Try to log the error, but don't let the logging cause a fatal error
			try
			{
				Log::add(
					sprintf(
						'Uncaught Throwable of type %1$s thrown with message "%2$s". Stack trace: %3$s',
						get_class($error),
						$error->getMessage(),
						$error->getTraceAsString()
					),
					Log::CRITICAL,
					'error'
				);
			}
			catch (\Throwable $e)
			{
				// Logging failed, don't make a stink about it though
			}

			$app = Factory::getApplication();

			// Flag if we are on cli
			$isCli = $app->isClient('cli');

			// If site is offline and it's a 404 error, just go to index (to see offline message, instead of 404)
			if (!$isCli && $error->getCode() == '404' && $app->get('offline') == 1)
			{
				$app->redirect('index.php');
			}

			/*
			 * Try and determine the format to render the error page in
			 *
			 * First we check if a Document instance was registered to Factory and use the type from that if available
			 * If a type doesn't exist for that format, we try to use the format from the application's Input object
			 * Lastly, if all else fails, we default onto the HTML format to at least render something
			 */
			if (Factory::$document)
			{
				$format = Factory::$document->getType();
			}
			else
			{
				$format = $app->input->getString('format', 'html');
			}

			try
			{
				$renderer = AbstractRenderer::getRenderer($format);
			}
			catch (\InvalidArgumentException $e)
			{
				// Default to the HTML renderer
				$renderer = AbstractRenderer::getRenderer('html');
			}

			// Reset the document object in the factory, this gives us a clean slate and lets everything render properly
			Factory::$document = $renderer->getDocument();

			$data = $renderer->render($error);

			// If nothing was rendered, just use the message from the Exception
			if (empty($data))
			{
				$data = $error->getMessage();
			}

			if ($isCli)
			{
				echo $data;
			}
			else
			{
				/** @var CMSApplication $app */

				// Do not allow cache
				$app->allowCache(false);

				$app->setBody($data);
			}

			// This return is needed to ensure the test suite does not trigger the non-Exception handling below
			return;
		}
		catch (\Throwable $e)
		{
			// Pass the error down
		}

		/*
		 * To reach this point in the code means there was an error creating the error page.
		 * We try to send at least something back other than a WSOD at this point.
		 */
		if (!$isCli && !headers_sent())
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		$message = 'Error';

		// Make sure we do not display sensitive data in production environments
		if (ini_get('display_errors'))
		{
			$message .= ': ';

			if (isset($e))
			{
				$message .= $e->getMessage() . ': ';
			}

			$message .= $error->getMessage();
		}

		echo $message;

		jexit(1);
	}
}
