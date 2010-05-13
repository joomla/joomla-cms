<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * Utility class for creating HTML Grids
 *
 * @package		Joomla.Framework
 * @subpackage	HTML
 * @since		1.6
 */
abstract class JHtmlJGrid
{
	/**
	 * Returns an action on a grid
	 *
	 * @param	int		$i			The row index
	 * @param	string	$task		The task to fire
	 * @param	string	$prefix		An optional task prefix
	 * @param	string	$text		An optional text to display (will be translated)
	 * @param	string	$title		An optional tooltip to display if $canChange is true (will be translated)
	 * @param	string	$active		An optional active html class
	 * @param	string	$inactive	An optional inactive html class
	 * @param	boolean	$canChange	An optional setting for access control on the action.
	 */
	public static function action($i, $task, $prefix='', $text='', $title='', $active='', $inactive, $canChange = true)
	{
		if ($canChange) {
			return '<a class="jgrid" href="javascript:void(0);" onclick="return listItemTask(\'cb'.$i.'\',\''.$prefix.$task.'\')" title="'.addslashes(htmlspecialchars(JText::_($title), ENT_COMPAT, 'UTF-8')).'"><span class="state '.$active.'"><span class="text">'.JText::_($text).'</span></span></a>';
		}
		else {
			return '<span class="jgrid"><span class="state '.$inactive.'"><span class="text">'.JText::_($text).'</span></span></span>';
		}
	}

	/**
	 * Returns a state on a grid
	 *
	 * @param	array	$states		array of value/state. Each state is an array of the form (task, text, title,html active class, html inactive class)
	 *								or ('task'=>task, 'text'=>text, 'title'=>title, 'active'=>html class, 'inactive'=>html class) 
	 * @param	int		$value		An optional state value.
	 * @param	int		$i			An optinal row index
	 * @param	string	$prefix		An optional prefix for the task.
	 * @param	boolean	$canChange	An optional setting for access control on the action.
	 */
	public static function state($states, $value = 0, $i=0, $prefix = '', $canChange = true)
	{
		$state		= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$task		= array_key_exists('task',		$state) ? $state['task']		: $state[0];
		$text		= array_key_exists('text',		$state) ? $state['text']		: (array_key_exists(1,$state) ? $state[1] : '');
		$title		= array_key_exists('title',		$state) ? $state['title']		: (array_key_exists(2,$state) ? $state[2] : '');
		$active		= array_key_exists('active',	$state) ? $state['active']		: (array_key_exists(3,$state) ? $state[3] : '');
		$inactive	= array_key_exists('inactive',	$state) ? $state['inactive']	: (array_key_exists(4,$state) ? $state[4] : $active);
		
		return self::action($i, $task, $prefix, $text, $title, $active, $inactive, $canChange);
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param	int		$value		An optional state value.
	 * @param	int		$i			An optinal row index
	 * @param	string	$prefix		An optional prefix for the task.
	 * @param	boolean	$canChange	An optional setting for access control on the action.
	 * @see JHtmlJGrid::state
	 */
	public static function published($value = 0, $i=0, $prefix = '', $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('unpublish',	'JPUBLISHED',	'JLIB_HTML_UNPUBLISH_ITEM',	'publish'),
			0	=> array('publish',		'JUNPUBLISHED',	'JLIB_HTML_PUBLISH_ITEM',	'unpublish'),
			2	=> array('unpublish',	'JARCHIVED',	'JLIB_HTML_UNPUBLISH_ITEM',	'archive'),
			-2	=> array('publish',		'JTRASHED',		'JLIB_HTML_PUBLISH_ITEM',	'trash'),
		);
		return self::state($states, $value, $i, $prefix, $canChange);
	}

	/**
	 * Returns a isDefault state on a grid
	 *
	 * @param	int		$value		An optional state value.
	 * @param	int		$i			An optinal row index
	 * @param	string	$prefix		An optional prefix for the task.
	 * @param	boolean	$canChange	An optional setting for access control on the action.
	 * @see JHtmlJGrid::state
	 */
	public static function isdefault($value = 0, $i=0, $prefix = '', $canChange = true)
	{
		// Array of image, task, title, action
		$states	= array(
			1	=> array('unsetDefault',	'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM',	'default'),
			0	=> array('setDefault', 		'',			'JLIB_HTML_SETDEFAULT_ITEM',	'notdefault'),
		);
		return self::state($states, $value, $i, $prefix, $canChange);
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @param	array			An array of configuration options.
	 *							This array can contain a list of key/value pairs where values are boolean
	 *							and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
	 *							These pairs determine which values are displayed.
	 * @return	string			The HTML code for the select tag
	 * @since	1.6
	 */
	public static function publishedOptions($config = array())
	{
		// Build the active state filter options.
		$options	= array();
		if (!array_key_exists('published', $config) || $config['published']) {
			$options[]	= JHtml::_('select.option', '1', 'JPUBLISHED');
		}
		if (!array_key_exists('unpublished', $config) || $config['unpublished']) {
			$options[]	= JHtml::_('select.option', '0', 'JUNPUBLISHED');
		}
		if (!array_key_exists('archived', $config) || $config['archived']) {
			$options[]	= JHtml::_('select.option', '2', 'JARCHIVED');
		}
		if (!array_key_exists('trash', $config) || $config['trash']) {
			$options[]	= JHtml::_('select.option', '-2', 'JTRASH');
		}
		if (!array_key_exists('all', $config) || $config['all']) {
			$options[]	= JHtml::_('select.option', '*', 'JALL');
		}
		return $options;
	}

	/**
	 * Returns a checked-out icon
	 *
	 * @param	string	The name of the editor.
	 * @param	string	The time that the object was checked out.
	 *
	 * @return	string	The required HTML.
	 */
	public static function checkedout($editorName, $time)
	{
		$text	= addslashes(htmlspecialchars($editorName, ENT_COMPAT, 'UTF-8'));
		$date	= addslashes(htmlspecialchars(JHTML::_('date',$time, '%A, %d %B %Y'), ENT_COMPAT, 'UTF-8'));
		$time	= addslashes(htmlspecialchars(JHTML::_('date',$time, '%H:%M'), ENT_COMPAT, 'UTF-8'));

		$html  = '<span class="jgrid editlinktip hasTip" title="'. JText::_('JLIB_HTML_CHECKED_OUT') .'::'. $text .'<br />'. $date .'<br />'. $time .'">';
		$html .= 	'<span class="state checkedout"><span class="text">'.JText::_('JLIB_HTML_CHECKED_OUT').'</span>';
		$html .= '</span>';
		
		return $html;
	}

	/**
	 * Creates a order-up action icon.
	 *
	 * @param	integer	$i			The row index.
	 * @param	string	$task		An optional task to fire.
	 * @param	string	$prefix		An optional task prefix.
	 * @param	string	$text		The text to display
	 * @param	boolean	$enabled	True to enable the action.
	 *
	 * @return	string	The required HTML.
	 * @since	1.6
	 */
	public static function orderUp($i, $task='orderup', $prefix='', $text = 'JLIB_HTML_MOVE_UP', $enabled = true)
	{
		return self::action($i, $task, $prefix, $text, $text, 'uparrow', 'uparrow_disabled', $enabled);
	}

	/**
	 * Creates a order-down action icon.
	 *
	 * @param	integer	$i			The row index.
	 * @param	string	$task		An optional task to fire.
	 * @param	string	$prefix		An optional task prefix.
	 * @param	string	$text		The text to display
	 * @param	boolean	$enabled	True to enable the action.
	 *
	 * @return	string	The required HTML.
	 * @since	1.6
	 */
	public static function orderDown($i, $task='orderdown', $prefix='', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true)
	{
		return self::action($i, $task, $prefix, $text, $text, 'downarrow', 'downarrow_disabled', $enabled);
	}
}
