<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
	 * @param   Exception  $error  The exception for which to render the error page.
	 *
	 * @return  void
	 *
	 * @since   3.0
	 */
	public static function render(Exception $error)
	{
		try
		{
			$app      = JFactory::getApplication();
			$document = JDocument::getInstance('error');

			if (!$document)
			{
				// We're probably in an CLI environment
				exit($error->getMessage());
			}

			$config = JFactory::getConfig();

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
				array('template' => $template,
				'directory' => JPATH_THEMES,
				'debug' => $config->get('debug'))
			);

			// Failsafe to get the error displayed.
			if (empty($data))
			{
				exit($error->getMessage());
			}
			else
			{
				// Do not allow cache
				$app->allowCache(false);

				$app->setBody($data);
				echo $app->toString();
			}
		}
		catch (Exception $e)
		{
			// Try to set a 500 header if they haven't already been sent
			if (!headers_sent())
			{
				header('HTTP/1.1 500 Internal Server Error');
			}

			exit('Error displaying the error page: ' . $e->getMessage() . ': ' . $error->getMessage());
		}
	}
}
