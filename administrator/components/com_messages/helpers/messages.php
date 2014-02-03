<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 * @since       1.6
 */
class MessagesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	The name of the active view.
	 *
	 * @return  void
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
	 * Get a list of filter options for the state of a module.
	 *
	 * @return  array  An array of JHtmlOption elements.
	 */
	public static function getStateOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option',	'1',	JText::_('COM_MESSAGES_OPTION_READ'));
		$options[]	= JHtml::_('select.option',	'0',	JText::_('COM_MESSAGES_OPTION_UNREAD'));
		$options[]	= JHtml::_('select.option',	'-2',	JText::_('JTRASHED'));
		return $options;
	}
}
