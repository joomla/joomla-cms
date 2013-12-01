<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_BASE') or die;

/**
 * Displays the error message when an uncaught exception occurs.
 *
 * @package     Joomla.Site
 * @subpackage  com_ajax
 * @since       3.2
 */
class AjaxError
{
	/**
	 * Display the error message based on an exception.
	 *
	 * @param   Exception  $error
	 * @return  void
	 *
	 * @since   3.2
	 */
	public static function render(Exception $error)
	{
		try
		{
			$app   = JFactory::getApplication();
			$debug = JFactory::getConfig()->get('debug');

			// Display the error message depend from given format
			$message = '';
			switch ($app->input->get('format')) {
				case 'json':
					$doc 	 = JDocument::getInstance('json');
					$message = new JResponseJson($error, null, false, $app->input->get('ignoreMessages', true, 'bool'));
					break;

				default:
					$doc 	 = JDocument::getInstance('raw');
					$message = $error->getMessage();
					// Display backtrace if debug enabled
					if($debug)
					{
						$message .= "\n" . $error->getTraceAsString();
					}
					break;
			}

			// Do not allow cache
			$app->allowCache(false);
			// Set the status header
			$app->setHeader('status', $error->getCode() . ' ' . str_replace("\n", ' ', $error->getMessage()), true);

			// Set the Document content, and render it
			$doc->setBuffer($message);
			$data = $doc->render();

			// Failsafe to get the error displayed.
			if (empty($data))
			{
				exit($error->getMessage());
			}
			else
			{
				$app->setBody($data);
				echo $app->toString();
			}

		}
		catch (Exception $e)
		{
			exit('AjaxError displaying the error: ' . $e->getMessage());
		}
	}
}
