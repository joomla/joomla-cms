<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2012 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Exception;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Document\Document;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;

/**
 * Displays the custom error page when an uncaught exception occurs.
 *
 * @since  3.0
 */
class ExceptionHandler
{
	/**
	 * Handles exceptions: logs errors and renders error page.
	 *
	 * @param   \Exception|\Throwable  $error  An Exception or Throwable (PHP 7+) object for which to render the error page.
	 *
	 * @return  void
	 *
	 * @since   3.10.0
	 */
	public static function handleException($error)
	{
		if (static::isException($error))
		{
			static::logException($error);
		}

		static::render($error);
	}

	/**
	 * Render the error page based on an exception.
	 *
	 * @param   \Exception|\Throwable  $error  An Exception or Throwable (PHP 7+) object for which to render the error page.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function render($error)
	{
		// Render template error page for exceptions only, because template will expect exception object
		if (static::isException($error))
		{
			try
			{
				$app = Factory::getApplication();

				// If site is offline and it's a 404 error, just go to index (to see offline message, instead of 404)
				if ($error->getCode() == '404' && $app->get('offline') == 1)
				{
					$app->redirect('index.php');
				}

				$attributes = array(
					'charset'   => 'utf-8',
					'lineend'   => 'unix',
					'tab'       => "\t",
					'language'  => 'en-GB',
					'direction' => 'ltr',
				);

				// If there is a \JLanguage instance in Factory then let's pull the language and direction from its metadata
				if (Factory::$language)
				{
					$attributes['language']  = Factory::getLanguage()->getTag();
					$attributes['direction'] = Factory::getLanguage()->isRtl() ? 'rtl' : 'ltr';
				}

				$document = Document::getInstance('error', $attributes);

				if (!$document)
				{
					// We're probably in an CLI environment
					jexit($error->getMessage());
				}

				// Get the current template from the application
				$template = $app->getTemplate();

				// Push the error object into the document
				$document->setError($error);

				// Clear buffered output at all levels
				while (ob_get_level())
				{
					ob_end_clean();
				}

				// This is needed to ensure the test suite can still get the output buffer
				ob_start();

				$document->setTitle(Text::_('ERROR') . ': ' . $error->getCode());

				$data = $document->render(
					false,
					array(
						'template'  => $template,
						'directory' => JPATH_THEMES,
						'debug'     => JDEBUG,
					)
				);

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
			catch (\Throwable $e)
			{
				// Pass the error down
			}
			catch (\Exception $e)
			{
				// Pass the error down
			}
		}

		// This isn't an Exception, we can't handle it.
		if (!headers_sent())
		{
			header('HTTP/1.1 500 Internal Server Error');
		}

		$message = 'Error';

		if (static::isException($error))
		{
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
		}

		echo $message;

		jexit(1);
	}

	/**
	 * Checks if given error belong to PHP exception class (\Throwable for PHP 7+, \Exception for PHP 5-).
	 *
	 * @param   mixed  $error  Any error value.
	 *
	 * @return  bool
	 *
	 * @since   3.10.0
	 */
	protected static function isException($error)
	{
		$expectedClass = PHP_MAJOR_VERSION >= 7 ? '\Throwable' : '\Exception';

		return $error instanceof $expectedClass;
	}

	/**
	 * Logs exception, catching all possible errors during logging.
	 *
	 * @param   \Exception|\Throwable  $error  An Exception or Throwable (PHP 7+) object to get error message from.
	 *
	 * @return  void
	 *
	 * @since   3.10.0
	 */
	protected static function logException($error)
	{
		// Try to log the error, but don't let the logging cause a fatal error
		try
		{
			Log::add(
				sprintf(
					'Uncaught %1$s of type %2$s thrown. Stack trace: %3$s',
					PHP_MAJOR_VERSION >= 7 ? 'Throwable' : 'Exception',
					get_class($error),
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
		catch (\Exception $e)
		{
			// Logging failed, don't make a stink about it though
		}
	}
}
