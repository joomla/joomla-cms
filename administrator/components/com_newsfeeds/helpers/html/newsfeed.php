<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Utility class for creating HTML Grids
 *
 * @static
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.5
 */
class JHtmlNewsfeed
{
	/**
	 * @param	int $value	The state value
	 * @param	int $i
	 */
	public static function state($value = 0, $i)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('tick.png',		'newsfeeds.unpublish',	'JPUBLISHED',			'COM_NEWSFEEDS_UNPUBLISH_ITEM'),
			0	=> array('publish_x.png',	'newsfeeds.publish',		'JUNPUBLISHED',		'COM_NEWSFEEDS_PUBLISH_ITEM')
		);
		$state	= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$html	= '<a href="#" onclick="return listItemTask(\'cb'.$i.'\',\''.$state[1].'\')" title="'.JText::_($state[3]).'">'
				. JHtml::_('image', 'admin/'.$state[0], JText::_($state[2]), null, true).'</a>';

		return $html;
	}

	/**
	 * Display an HTML select list of state filters
	 *
	 * @param	int $selected	The selected value of the list
	 * @return	string			The HTML code for the select tag
	 * @since	1.6
	 */
	public static function filterstate($selected)
	{
		// Build the active state filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '*', JText::_('JOPTION_ANY'));
		$options[]	= JHtml::_('select.option', '1', JText::_('JPUBLISHED'));
		$options[]	= JHtml::_('select.option', '0', JText::_('JUNPUBLISHED'));

		return JHtml::_('select.genericlist', $options, 'filter_published',
			array(
				'list.attr' => 'class="inputbox" onchange="this.form.submit();"',
				'list.select' => $selected
			)
		);
	}
}
