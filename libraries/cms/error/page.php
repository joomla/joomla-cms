<?php
/**
 * @package     Joomla.Libraries
 * @subpackage  Error
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Displays the custom error page when an uncaught exception occurs.
 *
 * @package     Joomla.Libraries
 * @subpackage  Error
 * @since       3.0
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
			// Get the current template from the application.
			// If the application is not available, fall back to the system template
			$template = (is_null(JFactory::$application)) ? 'system' : JFactory::getApplication()->getTemplate();

			$document = JDocument::getInstance('error');

			if (!$document)
			{
				// We're probably in an CLI environment
				exit($error->getMessage());
			}

			// Push the error object into the document
			$document->setError($error);

			if(ob_get_level())
			{
				ob_end_clean();
			}

			$document->setTitle(JText::_('Error') . ': ' . $error->getCode());
			$data = $document->render(
				false,
				array('template' => $template,
				'directory' => JPATH_THEMES,
				'debug' => JFactory::getConfig()->get('debug'))
			);

			// Failsafe to get the error displayed.
			if (empty($data))
			{
				exit($error->getMessage());
			}
			else
			{
				// Do not allow cache
				JResponse::allowCache(false);

				JResponse::setBody($data);
				echo JResponse::toString();
			}
		}
		catch (Exception $e)
		{
			exit('Error displaying the error page: ' . $e->getMessage());
		}
	}
}
