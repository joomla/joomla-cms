<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package 	Joomla.Administrator
 * @subpackage	Weblinks
 * @since		1.5
 */
class JHtmlWeblink
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	function state($value = 0, $i)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('tick.png',		'weblinks.unpublish',	'JState_Published',		'JState_UnPublish_Item'),
			0	=> array('publish_x.png',	'weblinks.publish',		'JState_UnPublished',	'JState_Publish_Item'),
			-1	=> array('reported.png',	'weblinks.publish',		'Weblinks_Reported',	'JState_Publish_Item'),
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.$state[3].'">'
				. JHtml::_('image.administrator', $state[0], '/images/', null, '/images/', $state[2]).'</a>';

		return $html;
	}

	/**
	 * Display an HTML select list of state filters
	 *
	 * @param	int $selected	The selected value of the list
	 * @return	string			The HTML code for the select tag
	 * @since	1.6
	 */
	function filterstate($selected)
	{
		// Build the active state filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', 'JSelect_Any');
		$options[]	= JHtml::_('select.option', '0', 'JState_UnPublished');
		$options[]	= JHtml::_('select.option', '1', 'JState_Published');
		$options[]	= JHtml::_('select.option', '-1', 'Weblinks_Reported');

		return JHTML::_('select.genericlist', $options, 'filter_state', 'class="inputbox"', 'value', 'text', $selected);
	}
}
