<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML Grid Helper
 *
 * @package		Joomla.Administrator
 * @subpackage	com_members
 */
class JHTMLJxGrid
{
	/**
	 * Display the access setting and change link
	 *
	 * @param	int		The row index
	 * @param	int		The access value
	 * @param	int		The current access name
	 * @param	string	Optional task prefix
	 *
	 * @return	string
	 */
	function access($i, $accessValue, $accessName, $prefix='')
	{
		if (!$accessValue)  {
			$color_access = 'green';
		}
		else if ($accessValue == 1) {
			$color_access = 'red';
		}
		else {
			$color_access = 'black';
		}
		$href	= '<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''.$prefix.'access\')" style="color:'. $color_access .'">
			'. JText::_($accessName) .'</a>';

		return $href;
	}

	/**
	 * Display the published setting and icon
	 *
	 * @param	int		The value of the published field
	 * @param	int		The row index
	 * @param	string	Optional task prefix
	 *
	 * @return	string
	 */
	function published($i, $value, $prefix='')
	{
		$images	= array(-2 => 'components/com_members/media/images/icon-16-trash.png', 0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(-2 => 'Trash', 0 => 'Unpublished', 1 => 'Published');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'unpublish' : 'publish';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = $value == 1 ? JText::_('Unpublish Item') : JText::_('Publish item');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $prefix.$task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>';

		return $href;
	}

	/**
	 * Display the checked out icon
	 *
	 * @param	string	The editor name
	 * @param	string	The checked out time
	 *
	 * @return	string
	 */
	function checkedout($editor, $time)
	{
		$text	= addslashes(htmlspecialchars($editor));
		$date 	= JHtml::_('date',  $time, '%A, %d %B %Y');
		$time	= JHtml::_('date',  $time, '%H:%M');

		$hover = '<span class="editlinktip hasTip" title="'. JText::_('Checked Out') .'::'. $text .'<br />'. $date .'<br />'. $time .'">';
		$checked = $hover .'<img src="components/com_members/media/images/checked_out.png" alt="" /></span>';

		return $checked;
	}

	function enabled($value, $i)
	{
		$images	= array(0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(0 => 'Disabled', 1 => 'Enabled');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'rule.disable' : 'rule.enable';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('JX Click to toggle setting');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}

	function allowed($value, $i)
	{
		$images	= array(0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(0 => 'Denied', 1 => 'Allowed');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'rule.deny' : 'rule.allow';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('JX Click to toggle setting');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}

}