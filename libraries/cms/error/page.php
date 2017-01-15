<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Cms\Error\AbstractRenderer;

/**
 * Displays the custom error page when an uncaught exception occurs.
 *
 * @since  3.0
 */
class JErrorPage
{
	/**
	 * Render the error page based on an exception.
	 *
	 * @param   Exception|Throwable  $error  An Exception or Throwable (PHP 7+) object for which to render the error page.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function render($error)
	{
		$expectedClass = PHP_MAJOR_VERSION >= 7 ? 'Throwable' : 'Exception';
		$isException   = $error instanceof $expectedClass;

		// In PHP 5, the $error object should be an instance of Exception; PHP 7 should be a Throwable implementation
		if ($isException)
		{
			try
			{
				// Try to log the error, but don't let the logging cause a fatal error
				try
				{
					JLog::add(
						sprintf(
							'Uncaught %1$s of type %2$s thrown. Stack trace: %3$s',
							$expectedClass,
							get_class($error),
							$error->getTraceAsString()
						),
						JLog::CRITICAL,
						'error'
					);
				}
				catch (Throwable $e)
				{
					// Logging failed, don't make a stink about it though
				}
				catch (Exception $e)
				{
					// Logging failed, don't make a stink about it though
				}

				$app = JFactory::getApplication();

				// If site is offline and it's a 404 error, just go to index (to see offline message, instead of 404)
				if ($error->getCode() == '404' && $app->get('offline') == 1)
				{
					$app->redirect('index.php');
				}

				/*
				 * Try and determine the format to render the error page in
				 *
				 * First we check if a JDocument instance was registered to JFactory and use the type from that if available
				 * If a type doesn't exist for that format, we try to use the format from the application's JInput object
				 * Lastly, if all else fails, we default onto the HTML format to at least render something
				 */
				if (JFactory::$document)
				{
					// We're probably in an CLI environment
					$format = JFactory::getDocument()->getType();
				}
				else
				{
					$format = $app->input->getString('format', 'html');
				}

				try
				{
					$renderer = AbstractRenderer::getRenderer($format);
				}
				catch (InvalidArgumentException $e)
				{
					// Default to the HTML renderer
					$renderer = AbstractRenderer::getRenderer('html');
				}

				$data = $renderer->render($error);

				// Do not allow cache
				$app->allowCache(false);

				// If nothing was rendered, just use the message from the Exception
				if (empty($data))
				{
					$data = $error->getMessage();
				}

				$app->setBody($data);

				echo $app->toString();

				$app->close(0);

				// This return is needed to ensure the test suite does not trigger the non-Exception handling below
				return;
			}
			catch (Throwable $e)
			{
				// Pass the error down
			}
			catch (Exception $e)
			{
				// Pass the error down
			}
		}

		// This isn't an Exception, we can't handle it.
		if (!headers_sent())
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		$message = 'Error displaying the error page';

		if ($isException)
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
