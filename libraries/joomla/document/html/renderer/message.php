<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * JDocument system message renderer
 *
 * @package     Joomla.Platform
 * @subpackage  Document
 * @since       11.1
 */
class JDocumentRendererMessage extends JDocumentRenderer
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
	 * @since   11.1
	 */
	public function render($name, $params = array (), $content = null)
	{
		$msgList = $this->getData();
		$buffer = null;
		$app = JFactory::getApplication();
		$chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';
		$itemOverride = false;

		if (file_exists($chromePath))
		{
			include_once $chromePath;
			if (function_exists('renderMessage'))
			{
				$itemOverride = true;
			}
		}

		$buffer = ($itemOverride) ? renderMessage($msgList) : $this->renderDefaultMessage($msgList);

		return $buffer;
	}

	/**
	 * Get and prepare system message data for output
	 *
	 * @return  array  An array contains system message
	 *
	 * @since   12.2
	 */
	private function getData()
	{
		// Initialise variables.
		$lists = array();

		// Get the message queue
		$messages = JFactory::getApplication()->getMessageQueue();

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

	/**
	 * Render the system message if no message template file found
	 *
	 * @param   array  $msgList  An array contains system message
	 *
	 * @return  string  System message markup
	 *
	 * @since   12.2
	 */
	private function renderDefaultMessage($msgList)
	{
		// Build the return string
		$buffer = '';
		$buffer .= "\n<div id=\"system-message-container\">";

		// If messages exist render them
		if (is_array($msgList))
		{
			$buffer .= "\n<div id=\"system-message\">";
			foreach ($msgList as $type => $msgs)
			{
				$buffer .= "\n<div class=\"alert alert-" . $type . "\">";

				// This requires JS so we should add it trough JS. Progressive enhancement and stuff.
				$buffer .= "<a class=\"close\" data-dismiss=\"alert\">×</a>";

				if (count($msgs))
				{
					$buffer .= "\n<h4 class=\"alert-heading\">" . JText::_($type) . "</h4>";
					$buffer .= "\n<div>";
					foreach ($msgs as $msg)
					{
						$buffer .= "\n\t\t<p>" . $msg . "</p>";
					}
					$buffer .= "\n</div>";
				}
				$buffer .= "\n</div>";
			}
			$buffer .= "\n</div>";
		}

		$buffer .= "\n</div>";

		return $buffer;
	}
}
