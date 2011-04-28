<?php
/**
 * @package     Joomla.Platform
 * @subpackage  HTML
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Utility class for creating HTML Grids
 *
 * @package		Joomla.Platform
 * @subpackage	HTML
 * @since		11.1
 */
abstract class JHtmlJGrid
{
	/**
	 * Returns an action on a grid
	 *
	 * @param	int				$i					The row index
	 * @param	string			$task				The task to fire
	 * @param	string|array	$prefix				An optional task prefix or an array of options
	 * @param	string			$text				An optional text to display
	 * @param	string			$active_title		An optional active tooltip to display if $enable is true
	 * @param	string			$inactive_title		An optional inactive tooltip to display if $enable is true
	 * @param	boolean			$tip				An optional setting for tooltip
	 * @param	string			$active_class		An optional active html class
	 * @param	string			$inactive_class		An optional inactive html class
	 * @param	boolean			$enabled			An optional setting for access control on the action.
	 * @param	boolean			$translate			An optional setting for translation.
	 * @param	string			$checkbox			An optional prefix for checkboxes.
	 *
	 * @return The Html code
	 *
	 * @since	11.1
	 */
	public static function action($i, $task, $prefix='', $text='', $active_title='', $inactive_title='', $tip=false, $active_class='', $inactive_class='', $enabled = true, $translate=true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options			= $prefix;
			$text				= array_key_exists('text',				$options) ? $options['text']				: $text;
			$active_title		= array_key_exists('active_title',		$options) ? $options['active_title']		: $active_title;
			$inactive_title		= array_key_exists('inactive_title',	$options) ? $options['inactive_title']		: $inactive_title;
			$tip				= array_key_exists('tip',				$options) ? $options['tip']					: $tip;
			$active_class		= array_key_exists('active_class',		$options) ? $options['active_class']		: $active_class;
			$inactive_class		= array_key_exists('inactive_class',	$options) ? $options['inactive_class']		: $inactive_class;
			$enabled			= array_key_exists('enabled',			$options) ? $options['enabled']				: $enabled;
			$translate			= array_key_exists('translate',			$options) ? $options['translate']			: $translate;
			$checkbox			= array_key_exists('checkbox',			$options) ? $options['checkbox']			: $checkbox;
			$prefix				= array_key_exists('prefix',			$options) ? $options['prefix']				: '';
		}
		if ($tip) {
			JHtml::_('behavior.tooltip');
		}
		if ($enabled) {
			return '<a class="jgrid'.($tip?' hasTip':'').'" href="javascript:void(0);" onclick="return listItemTask(\''.$checkbox.$i.'\',\''.$prefix.$task.'\')" title="'.addslashes(htmlspecialchars($translate?JText::_($active_title):$active_title, ENT_COMPAT, 'UTF-8')).'"><span class="state '.$active_class.'"><span class="text">'.($translate?JText::_($text):$text).'</span></span></a>';
		}
		else {
			return '<span class="jgrid'.($tip?' hasTip':'').'" title="'.addslashes(htmlspecialchars($translate?JText::_($inactive_title):$inactive_title, ENT_COMPAT, 'UTF-8')).'"><span class="state '.$inactive_class.'"><span class="text">'.($translate?JText::_($text):$text).'</span></span></span>';
		}
	}

	/**
	 * Returns a state on a grid
	 *
	 * @param	array			$states		array of value/state. Each state is an array of the form (task, text, title,html active class, html inactive class)
	 *										or ('task'=>task, 'text'=>text, 'active_title'=>active title, 'inactive_title'=>inactive title, 'tip'=>boolean, 'active_class'=>html active class, 'inactive_class'=>html inactive class)
	 * @param	int				$value		The state value.
	 * @param	int				$i			The row index
	 * @param	string|array	$prefix		An optional task prefix or an array of options
	 * @param	boolean			$enabled	An optional setting for access control on the action.
	 * @param	boolean			$translate	An optional setting for translation.
	 * @param	string			$checkbox	An optional prefix for checkboxes.
	 *
	 * @return The Html code
	 *
	 * @since	1.
	 */
	public static function state($states, $value, $i, $prefix = '', $enabled = true, $translate=true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$translate	= array_key_exists('translate',	$options) ? $options['translate']	: $translate;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$state			= JArrayHelper::getValue($states, (int) $value, $states[0]);
		$task			= array_key_exists('task',				$state) ? $state['task']			: $state[0];
		$text			= array_key_exists('text',				$state) ? $state['text']			: (array_key_exists(1,$state) ? $state[1] : '');
		$active_title	= array_key_exists('active_title',		$state) ? $state['active_title']	: (array_key_exists(2,$state) ? $state[2] : '');
		$inactive_title	= array_key_exists('inactive_title',	$state) ? $state['inactive_title']	: (array_key_exists(3,$state) ? $state[3] : '');
		$tip			= array_key_exists('tip',				$state) ? $state['tip'	]			: (array_key_exists(4,$state) ? $state[4] : false);
		$active_class	= array_key_exists('active_class',		$state) ? $state['active_class']	: (array_key_exists(5,$state) ? $state[5] : '');
		$inactive_class	= array_key_exists('inactive_class',	$state) ? $state['inactive_class']	: (array_key_exists(6,$state) ? $state[6] : '');

		return self::action($i, $task, $prefix, $text, $active_title, $inactive_title, $tip, $active_class, $inactive_class, $enabled, $translate, $checkbox);
	}

	/**
	 * Returns a published state on a grid
	 *
	 * @param	int				$value			The state value.
	 * @param	int				$i				The row index
	 * @param	string|array	$prefix			An optional task prefix or an array of options
	 * @param	boolean			$enabled		An optional setting for access control on the action.
	 * @param	string			$checkbox		An optional prefix for checkboxes.
	 * @param	string			$publish_up		An optional start publishing date.
	 * @param	string			$publish_down	An optional finish publishing date.
	 *
	 * @return The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since	11.1
	 */
	public static function published($value, $i, $prefix = '', $enabled = true, $checkbox = 'cb', $publish_up = null, $publish_down = null)
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('unpublish',	'JPUBLISHED',	'JLIB_HTML_UNPUBLISH_ITEM',	'JPUBLISHED',	false,	'publish',		'publish'),
			0	=> array('publish',		'JUNPUBLISHED',	'JLIB_HTML_PUBLISH_ITEM',	'JUNPUBLISHED',	false,	'unpublish',	'unpublish'),
			2	=> array('unpublish',	'JARCHIVED',	'JLIB_HTML_UNPUBLISH_ITEM',	'JARCHIVED',	false,	'archive',		'archive'),
			-2	=> array('publish',		'JTRASHED',		'JLIB_HTML_PUBLISH_ITEM',	'JTRASHED',		false,	'trash',		'trash'),
		);

		// Special state for dates
		if ($publish_up || $publish_down)
		{
			$nullDate 	= JFActory::getDBO()->getNullDate();
			$nowDate 	= JFactory::getDate()->toUnix();

			$tz	= JFactory::getApplication()->getCfg('offset');

			$publish_up		= ($publish_up 		!= $nullDate) ? JFactory::getDate($publish_up, $tz) 	: false;
			$publish_down 	= ($publish_down 	!= $nullDate) ? JFactory::getDate($publish_down, $tz) 	: false;

			// Create tip text, only we have publish up or down settings
			$tips = array();
			if ($publish_up) {
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_START', $publish_up->toFormat());
			}
			if ($publish_down) {
				$tips[] = JText::sprintf('JLIB_HTML_PUBLISHED_FINISHED', $publish_down->toFormat());
			}
			$tip = empty($tips) ? false : implode('<br/>', $tips);

			// Add tips and special titles
			foreach($states as $key=>$state) {
				// Create special titles for published items
				if ($key == 1) {
					$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_ITEM';
					if ($publish_up && $nowDate < $publish_up->toUnix()) {
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_PENDING_ITEM';
						$states[$key][5] = $states[$key][6] = 'pending';
					}
					if ($publish_down && $nowDate > $publish_down->toUnix()) {
						$states[$key][2] = $states[$key][3] = 'JLIB_HTML_PUBLISHED_EXPIRED_ITEM';
						$states[$key][5] = $states[$key][6] = 'expired';
					}
				}

				// Add tips to titles
				if ($tip) {
					$states[$key][1] = JText::_($states[$key][1]);
					$states[$key][2] = JText::_($states[$key][2]) . '::' . $tip;
					$states[$key][3] = JText::_($states[$key][3]) . '::' . $tip;
					$states[$key][4] = true;
				}
			}
			return self::state($states, $value, $i, array('prefix'=>$prefix,'translate'=>!$tip), $enabled, true, $checkbox);
		}

		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns a isDefault state on a grid
	 *
	 * @param	int				$value		The state value.
	 * @param	int				$i			The row index
	 * @param	string|array	$prefix		An optional task prefix or an array of options
	 * @param	boolean			$enabled	An optional setting for access control on the action.
	 * @param	string			$checkbox	An optional prefix for checkboxes.
	 *
	 * @return The Html code
	 *
	 * @see JHtmlJGrid::state
	 *
	 * @since	11.1
	 */
	public static function isdefault($value, $i, $prefix = '', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$states	= array(
			1	=> array('unsetDefault',	'JDEFAULT', 'JLIB_HTML_UNSETDEFAULT_ITEM',	'JDEFAULT',	false,	'default',		'default'),
			0	=> array('setDefault', 		'',			'JLIB_HTML_SETDEFAULT_ITEM',	'',			false,	'notdefault',	'notdefault'),
		);
		return self::state($states, $value, $i, $prefix, $enabled, true, $checkbox);
	}

	/**
	 * Returns an array of standard published state filter options.
	 *
	 * @param	array			An array of configuration options.
	 *							This array can contain a list of key/value pairs where values are boolean
	 *							and keys can be taken from 'published', 'unpublished', 'archived', 'trash', 'all'.
	 *							These pairs determine which values are displayed.
	 * @return	string			The HTML code for the select tag
	 *
	 * @since	11.1
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
			$options[]	= JHtml::_('select.option', '-2', 'JTRASHED');
		}
		if (!array_key_exists('all', $config) || $config['all']) {
			$options[]	= JHtml::_('select.option', '*', 'JALL');
		}
		return $options;
	}

	/**
	 * Returns a checked-out icon
	 *
	 * @param	integer			$i			The row index.
	 * @param	string			$editorName	The name of the editor.
	 * @param	string			$time		The time that the object was checked out.
	 * @param	string|array	$prefix		An optional task prefix or an array of options
	 * @param	string			$text		The text to display
	 * @param	boolean			$enabled	True to enable the action.
	 *
	 * @return	string	The required HTML.
	 *
	 * @since	11.1
	 */
	public static function checkedout($i, $editorName, $time, $prefix='', $enabled=false, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		$text			= addslashes(htmlspecialchars($editorName, ENT_COMPAT, 'UTF-8'));
		$date			= addslashes(htmlspecialchars(JHtml::_('date',$time, JText::_('DATE_FORMAT_LC')), ENT_COMPAT, 'UTF-8'));
		$time			= addslashes(htmlspecialchars(JHtml::_('date',$time, 'H:i'), ENT_COMPAT, 'UTF-8'));
		$active_title	= JText::_('JLIB_HTML_CHECKIN') 	.'::'. $text .'<br />'. $date .'<br />'. $time;
		$inactive_title	= JText::_('JLIB_HTML_CHECKED_OUT')	.'::'. $text .'<br />'. $date .'<br />'. $time;

		return  self::action($i, 'checkin', $prefix, JText::_('JLIB_HTML_CHECKED_OUT'), $active_title, $inactive_title, true, 'checkedout', 'checkedout', $enabled, false, $checkbox);
	}

	/**
	 * Creates a order-up action icon.
	 *
	 * @param	integer			$i			The row index.
	 * @param	string			$task		An optional task to fire.
	 * @param	string|array	$prefix		An optional task prefix or an array of options
	 * @param	string			$text		An optional text to display
	 * @param	boolean			$enabled	An optional setting for access control on the action.
	 * @param	string			$checkbox	An optional prefix for checkboxes.
	 *
	 * @return	string	The required HTML.
	 *
	 * @since	11.1
	 */
	public static function orderUp($i, $task='orderup', $prefix='', $text = 'JLIB_HTML_MOVE_UP', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$text		= array_key_exists('text',		$options) ? $options['text']		: $text;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		return self::action($i, $task, $prefix, $text, $text, $text, false, 'uparrow', 'uparrow_disabled', $enabled, true, $checkbox);
	}

	/**
	 * Creates a order-down action icon.
	 *
	 * @param	integer			$i			The row index.
	 * @param	string			$task		An optional task to fire.
	 * @param	string|array	$prefix		An optional task prefix or an array of options
	 * @param	string			$text		An optional text to display
	 * @param	boolean			$enabled	An optional setting for access control on the action.
	 * @param	string			$checkbox	An optional prefix for checkboxes.
	 *
	 * @return	string	The required HTML.
	 *
	 * @since	11.1
	 */
	public static function orderDown($i, $task='orderdown', $prefix='', $text = 'JLIB_HTML_MOVE_DOWN', $enabled = true, $checkbox='cb')
	{
		if (is_array($prefix)) {
			$options	= $prefix;
			$text		= array_key_exists('text',		$options) ? $options['text']		: $text;
			$enabled	= array_key_exists('enabled',	$options) ? $options['enabled']		: $enabled;
			$checkbox	= array_key_exists('checkbox',	$options) ? $options['checkbox']	: $checkbox;
			$prefix		= array_key_exists('prefix',	$options) ? $options['prefix']		: '';
		}
		return self::action($i, $task, $prefix, $text, $text, $text, false, 'downarrow', 'downarrow_disabled', $enabled, true, $checkbox);
	}
}
