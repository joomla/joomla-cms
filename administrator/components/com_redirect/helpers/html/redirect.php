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
		$states = array(
			1  => array('publish', 'links.unpublish', 'JENABLED', 'COM_REDIRECT_DISABLE_LINK'),
			0  => array('unpublish', 'links.publish', 'JDISABLED', 'COM_REDIRECT_ENABLE_LINK'),
			2  => array('archive', 'links.unpublish', 'JARCHIVED', 'JUNARCHIVE'),
			-2 => array('trash', 'links.publish', 'JTRASHED', 'COM_REDIRECT_ENABLE_LINK'),
		);

		$state = JArrayHelper::getValue($states, (int) $value, $states[0]);
		$icon  = $state[0];

		if ($canChange)
		{
			$html = '<a href="#" onclick="return listItemTask(\'cb' . $i . '\',\'' . $state[1] . '\')" class="btn btn-micro hasTooltip'
				. ($value == 1 ? ' active' : '') . '" title="' . JHtml::tooltipText($state[3]) . '"><span class="icon-'	. $icon . '"></span></a>';
		}

		return $html;
	}
}
