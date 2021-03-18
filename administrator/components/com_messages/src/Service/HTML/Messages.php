<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_messages
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Component\Messages\Administrator\Service\HTML;

\defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

/**
 * JHtml administrator messages class.
 *
 * @since  1.6
 */
class Messages
{
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
	public function status($i, $value = 0, $canChange = false)
	{
		// Array of image, task, title, action.
		$states = array(
			-2 => array('trash', 'messages.unpublish', 'JTRASHED', 'COM_MESSAGES_MARK_AS_UNREAD'),
			1  => array('publish', 'messages.unpublish', 'COM_MESSAGES_OPTION_READ', 'COM_MESSAGES_MARK_AS_UNREAD'),
			0  => array('unpublish', 'messages.publish', 'COM_MESSAGES_OPTION_UNREAD', 'COM_MESSAGES_MARK_AS_READ'),
		);

		$state = ArrayHelper::getValue($states, (int) $value, $states[0]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return Joomla.listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="tbody-icon'
				. ($value == 1 ? ' active' : '') . '" title="' . Text::_($state[3]) . '"><span class="icon-'
				. $icon . '" aria-hidden="true"></span></a>';
		}

		return $html;
	}
}
