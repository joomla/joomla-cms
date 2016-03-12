<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

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
	 * @param   object  $error  An Exception or Throwable (PHP 7+) object for which to render the error page.
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
				$app      = JFactory::getApplication();
				$document = JDocument::getInstance('error');

				if (!$document)
				{
					// We're probably in an CLI environment
					jexit($error->getMessage());
				}

				// Get the current template from the application
				$template = $app->getTemplate();

				// Push the error object into the document
				$document->setError($error);

				if (ob_get_contents())
				{
					ob_end_clean();
				}

				$document->setTitle(JText::_('Error') . ': ' . $error->getCode());

				$data = $document->render(
					false,
					array(
						'template'  => $template,
						'directory' => JPATH_THEMES,
						'debug'     => JDEBUG
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
