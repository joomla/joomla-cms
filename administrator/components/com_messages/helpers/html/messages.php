<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * JHtml administrator messages class.
 *
 * @since  1.6
 */
class JHtmlMessages
{
	/**
	 * Get the HTML code of the state switcher
	 *
	 * @param   int      $value      The state value
	 * @param   int      $i          Row number
	 * @param   boolean  $canChange  Can the user change the state?
	 *
	 * @return  string
	 *
	 * @since   1.6
	 *
	 * @deprecated  4.0  Use JHtmlMessages::status() instead
	 */
	public static function state($value = 0, $i = 0, $canChange = false)
	{
		// Log deprecated message
		JLog::add(
			'JHtmlMessages::state() is deprecated. Use JHtmlMessages::status() instead.',
			JLog::WARNING,
			'deprecated'
		);

		// Note: $i is required but has to be an optional argument in the function call due to argument order
		if (null === $i)
		{
			throw new InvalidArgumentException('$i is a required argument in JHtmlMessages::state');
		}

		// Note: $canChange is required but has to be an optional argument in the function call due to argument order
		if (null === $canChange)
		{
			throw new InvalidArgumentException('$canChange is a required argument in JHtmlMessages::state');
		}

		return static::status($i, $value, $canChange);
	}

	/**
	 * Get the HTML code of the state switcher
	 *
	 * @param   int      $i          Row number
	 * @param   int      $value      The state value
	 * @param   boolean  $canChange  Can the user change the state?
	 *
	 * @return  string
	 *
	 * @since   3.4
	 */
	public static function status($i, $value = 0, $canChange = false)
	{
		// Array of image, task, title, action.
		$states	= array(
			-2	=> array('trash.png',		'messages.unpublish',	'JTRASHED',				'COM_MESSAGES_MARK_AS_UNREAD'),
			1	=> array('tick.png',		'messages.unpublish',	'COM_MESSAGES_OPTION_READ',		'COM_MESSAGES_MARK_AS_UNREAD'),
			0	=> array('publish_x.png',	'messages.publish',		'COM_MESSAGES_OPTION_UNREAD',	'COM_MESSAGES_MARK_AS_READ')
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= JHtml::_('image', 'admin/' . $state[0], JText::_($state[2]), null, true);

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" title="' . JText::_($state[3]) . '">'
					. $html . '</a>';
		}

		return $html;
	}
}
