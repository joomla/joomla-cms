<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Weblinks helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_weblinks
 * @since       1.6
 */
class WeblinksHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	The name of the active view.
	 * @since   1.6
	 */
	public static function addSubmenu($vName = 'weblinks')
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBLINKS_SUBMENU_WEBLINKS'),
			'index.php?option=com_weblinks&view=weblinks',
			$vName == 'weblinks'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_WEBLINKS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_weblinks',
			$vName == 'categories'
		);
		if ($vName == 'categories')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_weblinks')),
				'weblinks-categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  The category ID.
	 * @return  JObject
	 * @since   1.6
	 */
	public static function getActions($categoryId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId))
		{
			$assetName = 'com_weblinks';
			$level = 'component';
		}
		else
		{
			$assetName = 'com_weblinks.category.'.(int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_weblinks', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}
	
	/**
	 * Returns an action on a grid
	 *
	 * @param   integer       $i               The row index
	 * @param   string        $task            The task to fire
	 * @param   string|array  $prefix          An optional task prefix or an array of options
	 * @param   string        $text            An optional text to display [unused - @deprecated 4.0]
	 * @param   string        $active_title    An optional active tooltip to display if $enable is true
	 * @param   string        $inactive_title  An optional inactive tooltip to display if $enable is true
	 * @param   boolean       $tip             An optional setting for tooltip
	 * @param   string        $active_class    An optional active HTML class
	 * @param   string        $inactive_class  An optional inactive HTML class
	 * @param   boolean       $enabled         An optional setting for access control on the action.
	 * @param   boolean       $translate       An optional setting for translation.
	 * @param   string        $checkbox	       An optional prefix for checkboxes.
	 *
	 * @see JHtmlJGrid::action
	 * @return  string  The HTML markup
	 *
	 * @since   1.6
	 */
	public static function action($i, $task, $prefix = '', $text = '', $active_title = '', $inactive_title = '', $tip = false, $active_class = '',
	$inactive_class = '', $enabled = true, $translate = false, $checkbox = 'cb')
	{
		return JHtml::_('jgrid.action', $i, $task, $prefix, $text, $active_title , $inactive_title, $tip, $active_class,
				$inactive_class, $enabled, $translate, $checkbox);
	}
	
	/**
	 * Method to return a checked-out control
	 * Only required because JGrid doesn't support the single task controllers task.subject syntax
	 * @param   integer       $i           The row index.
	 * @param   string        $item        Row item stdClass Object
	 * @param   array         $config      Associative array of configuration option. Minimal Requirements array('option' => component option, 'subject' => subject of task)
	 * @param   boolean       $action      ACL access action required to enable this control. default = 'core.manage'
	 * @param   string        $checkbox    An optional prefix for checkboxes.
	 *
	 * @return  string  The HTML markup or empty string if $item doesn't support record locking.
	 *
	 * @since   1.6
	 */
	public static function checkedout($i, $item, $config, $action = 'core.manage', $checkbox = 'cb')
	{
		if (isset($item->checked_out) && $item->checked_out)
		{
			$prefix = 'checkin.';
			$task = $config['subject'];
				
			$text = JText::_('JLIB_HTML_CHECKED_OUT');
			$editor = addslashes(htmlspecialchars($item->editor, ENT_COMPAT, 'UTF-8'));
			$date = addslashes(htmlspecialchars(JHtml::_('date', $item->checked_out_time, JText::_('DATE_FORMAT_LC')), ENT_COMPAT, 'UTF-8'));
			$time = addslashes(htmlspecialchars(JHtml::_('date', $item->checked_out_time, 'H:i'), ENT_COMPAT, 'UTF-8'));
				
			$spacer = '::';
				
			$active_title = JText::_('JLIB_HTML_CHECKIN') . $spacer . $editor . '<br />' . $date . '<br />' . $time;
			$inactive_title = JText::_('JLIB_HTML_CHECKED_OUT') . $spacer . $editor . '<br />' . $date . '<br />' . $time;
				
			$tip = true;
			$active_class = 'checkedout';
			$inactive_class = 'checkedout';
			$user = JFactory::getUser();
			$enabled = $user->authorise($action, $config['option']);
				
			return self::action($i, $task, $prefix, $text, $active_title, $inactive_title, $tip, $active_class, $inactive_class, $enabled);
		}
	
		return '';
	}
	
	/**
	 * Method to return a Publish Control 
	 * 
	 * Only required because JGrid doesn't support the single task controllers task.subject syntax
	 * @param   integer       $i           The row index.
	 * @param   string        $item        Row item stdClass Object
	 * @param   array         $config      Associative array of configuration option. Minimal Requirements array('option' => component option, 'subject' => subject of task)
	 * @param   boolean       $action      ACL access action required to enable this control. default = 'core.edit.state'
	 * @param   string        $checkbox    An optional prefix for checkboxes.
	 * @return string HTML control or void if $item doesn't support publishing.
	 */
	public static function publish($i, $item, $config, $action = 'core.edit.state', $checkbox = 'cb')
	{
		if (isset($item->state))
		{
			$statePrefixes = array();
			$statePrefixes[0] = 'statePublish.';
			$statePrefixes[1] = 'stateUnpublish.';
			$statePrefixes[2] = 'stateUnpublish.';
			$statePrefixes[-2]= 'stateUnpublish.';
			$statePrefixes[-3]= 'stateUnpublish.';
				
			$prefix = $statePrefixes[$item->state];
			$task = $config['subject'];
				
			$textStrings = array();
			$textStrings[0] = 'JLIB_HTML_PUBLISH_ITEM';
			$textStrings[1] = 'JLIB_HTML_UNPUBLISH_ITEM';
			$textStrings[2] = 'JLIB_HTML_UNPUBLISH_ITEM';
			$textStrings[-2] = 'JLIB_HTML_UNPUBLISH_ITEM';
			$textStrings[-3] = 'JLIB_HTML_UNPUBLISH_ITEM';
			$text = JText::_($textStrings[$item->state]);
				
			$active_title = $text;
				
			$inactiveTitles = array();
			$inactiveTitles[0] = 'JUNPUBLISHED';
			$inactiveTitles[1] = 'JPUBLISHED';
			$inactiveTitles[2] = 'JARCHIVED';
			$inactiveTitles[-2] = 'JTRASHED';
			$inactiveTitles[-3] = 'JREPORTED';
			$inactive_title = JText::_($inactiveTitles[$item->state]);
				
			$tip = true;
				
			$classes = array();
			$classes[0] = 'unpublish';
			$classes[1] = 'publish';
			$classes[2] = 'archive';
			$classes[-2] = 'trash';
			$classes[-3] = 'warning';
			$active_class = $classes[$item->state];
			$inactive_class = $classes[$item->state];
				
			$user = JFactory::getUser();
			$enabled = $user->authorise($action, $config['option']);
				
			return self::action($i, $task, $prefix, $text, $active_title, $inactive_title, $tip, $active_class, $inactive_class, $enabled);
		}
	
		return '';
	}
}
