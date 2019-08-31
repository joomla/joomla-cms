<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Log\Log;

// @TODO Remove when webcomponents get merged into the core

/**
 * JDocument system message renderer
 *
 * @since  3.5
 */
class JDocumentRendererHtmlMessage extends JDocumentRenderer
{
	/**
	 * Renders the error stack and returns the results as a string
	 *
	 * @param   string  $name     Not used.
	 * @param   array   $params   Associative array of values
	 * @param   string  $content  Not used.
	 *
	 * @return  string  The output of the script
	 *
	 * @since   3.5
	 */
	public function render($name, $params = array(), $content = null)
	{
		$msgList     = $this->getData();
		$displayData = array(
			'msgList' => $msgList,
			'name'    => $name,
			'params'  => $params,
			'content' => $content,
		);

		$app        = Factory::getApplication();
		$chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';

		if (file_exists($chromePath))
		{
			include_once $chromePath;
		}

		if (function_exists('renderMessage'))
		{
			Log::add('renderMessage() is deprecated. Override system message rendering with layouts instead.', Log::WARNING, 'deprecated');

			return renderMessage($msgList);
		}

		return LayoutHelper::render('installation.template.html.system.message', $displayData);
	}

	/**
	 * Get and prepare system message data for output
	 *
	 * @return  array  An array contains system message
	 *
	 * @since   3.5
	 */
	private function getData()
	{
		// Initialise variables.
		$lists = array();

		// Get the message queue
		$messages = Factory::getApplication()->getMessageQueue();

		// Build the sorted message list
		if (is_array($messages) && !empty($messages))
		{
			foreach ($messages as $msg)
			{
				if (isset($msg['type']) && isset($msg['message']))
				{
					$lists[$msg['type']][] = $msg['message'];
				}
			}
		}

		return $lists;
	}
}
