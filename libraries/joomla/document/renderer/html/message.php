<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Document
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

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
			'msgList'  => $msgList,
			'msgQueue' => $this->getData(2),
			'name'     => $name,
			'params'   => $params,
			'content'  => $content
		);

		$app        = JFactory::getApplication();
		$chromePath = JPATH_THEMES . '/' . $app->getTemplate() . '/html/message.php';

		if (file_exists($chromePath))
		{
			include_once $chromePath;
		}

		if (function_exists('renderMessage'))
		{
			JLog::add('renderMessage() is deprecated. Override system message rendering with layouts instead.', JLog::WARNING, 'deprecated');

			return renderMessage($msgList);
		}

		return JLayoutHelper::render('joomla.system.message', $displayData);
	}

	/**
	 * Get and prepare system message data for output
	 *
	 * @param   integer  $version  Type of returned array. 1 for B/C mode.
	 * 
	 * @return  array  An array contains system message
	 *
	 * @since   3.5
	 */
	private function getData($version = 1)
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
					if ($version === 1)
					{
						$groupIdentifier = $msg['type'];
					}
					else
					{
						$groupIdentifier = serialize(array_replace($msg['options'], array('type' => $msg['type'])));
					}

					$lists[$groupIdentifier][] = $msg['message'];
				}
			}
		}

		return $lists;
	}
}
