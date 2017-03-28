<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\Utilities\ArrayHelper;

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
		try
		{
			JLog::add(
				sprintf('%s() is deprecated. Use JHtmlMessages::status() instead.', __METHOD__),
				JLog::WARNING,
				'deprecated'
			);
		}
		catch (RuntimeException $exception)
		{
			// Informational log only
		}

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
		$states = array(
			-2 => array('trash', 'messages.unpublish', 'JTRASHED', 'COM_MESSAGES_MARK_AS_UNREAD'),
			1 => array('publish', 'messages.unpublish', 'COM_MESSAGES_OPTION_READ', 'COM_MESSAGES_MARK_AS_UNREAD'),
			0 => array('unpublish', 'messages.publish', 'COM_MESSAGES_OPTION_UNREAD', 'COM_MESSAGES_MARK_AS_READ'),
		);

		$state = ArrayHelper::getValue($states, (int) $value, $states[0]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::_('tooltipText', $state[3]) . '"><span class="icon-'	. $icon . '"></span></a>';
		}

		return $html;
	}
}
