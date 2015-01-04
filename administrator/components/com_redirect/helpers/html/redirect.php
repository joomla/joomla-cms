<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_redirect
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class for creating HTML Grids.
 *
 * @since  1.6
 */
class JHtmlRedirect
{
	/**
	 * Display the published or unpublished state of an item.
	 *
	 * @param   int      $value      The state value.
	 * @param   int      $i          The ID of the item.
	 * @param   boolean  $canChange  An optional prefix for the task.
	 *
	 * @return  string
	 *
	 * @since   1.6
	 *
	 * @throws  InvalidArgumentException
	 */
	public static function published($value = 0, $i = null, $canChange = true)
	{
		// Note: $i is required but has to be an optional argument in the function call due to argument order
		if (null === $i)
		{
			throw new InvalidArgumentException('$i is a required argument in JHtmlRedirect::published');
		}

		// Array of image, task, title, action
		$states	= array(
			1	=> array('tick.png',		'links.unpublish',	'JENABLED',	'COM_REDIRECT_DISABLE_LINK'),
			0	=> array('publish_x.png',	'links.publish',		'JDISABLED',	'COM_REDIRECT_ENABLE_LINK'),
			2	=> array('disabled.png',	'links.unpublish',	'JARCHIVED',	'JUNARCHIVE'),
			-2	=> array('trash.png',		'links.publish',		'JTRASHED',	'COM_REDIRECT_ENABLE_LINK'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= JHtml::_('image', 'admin/' . $state[0], JText::_($state[2]), null, true);

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" title="' . JText::_($state[3]) . '">' . $html . '</a>';
		}

		return $html;
	}
}
