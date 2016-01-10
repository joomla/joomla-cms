<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Messages helper class.
 *
 * @since  1.6
 */
class MessagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_MESSAGES_ADD'),
			'index.php?option=com_messages&view=message&layout=edit',
			$vName == 'message'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_MESSAGES_READ'),
			'index.php?option=com_messages',
			$vName == 'messages'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return  JObject
	 *
	 * @deprecated  3.2  Use JHelperContent::getActions() instead
	 */
	public static function getActions()
	{
		// Log usage of deprecated function
		JLog::add(__METHOD__ . '() is deprecated, use JHelperContent::getActions() with new arguments order instead.', JLog::WARNING, 'deprecated');

		// Get list of actions
		$result = JHelperContent::getActions('com_messages');

		return $result;
	}

	/**
	 * Get a list of filter options for the state of a module.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 *
	 * @since   1.6
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options   = array();
		$options[] = JHtml::_('select.option', '1', JText::_('COM_MESSAGES_OPTION_READ'));
		$options[] = JHtml::_('select.option', '0', JText::_('COM_MESSAGES_OPTION_UNREAD'));
		$options[] = JHtml::_('select.option', '-2', JText::_('JTRASHED'));

		return $options;
	}
}
