<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * HTML Grid Helper
 *
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 */
class JHtmlACL
{
	function enabled($value, $i)
	{
		$images	= array(0 => 'images/publish_x.png', 1 => 'images/tick.png');
		$alts	= array(0 => 'Disabled', 1 => 'Enabled');
		$img 	= JArrayHelper::getValue($images, $value, $images[0]);
		$task 	= $value == 1 ? 'acl.disable' : 'acl.enable';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('Click to toggle setting');

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
		$task 	= $value == 1 ? 'acl.deny' : 'acl.allow';
		$alt 	= JArrayHelper::getValue($alts, $value, $images[0]);
		$action = JText::_('Click to toggle setting');

		$href = '
		<a href="javascript:void(0);" onclick="return listItemTask(\'cb'. $i .'\',\''. $task .'\')" title="'. $action .'">
		<img src="'. $img .'" border="0" alt="'. $alt .'" /></a>'
		;

		return $href;
	}

	/**
	 * Build the select list for access level
	 */
	function groups($selected = null, $parentId = 0, $type = 'aro')
	{
		$model = JModel::getInstance('Groups', 'AccessModel', array('ignore_request' => 1));
		$model->setState('list.group_type', $type);

		// Set the model state to get the groups tree
		$model->setState('list.select',		'a.id AS value, a.name AS text');
		$model->setState('list.tree',		1);
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	$parentId);
		// Get a list without resolving foreign keys
		$options = $model->getList(false);

		// Find the level of the parent
		$parentLevel = ($parentId > 0) ? $model->getLevel($parentId, $type) : 0;

		// Pad out the options to create a visual tree
		foreach ($options as $i => $option) {
			$options[$i]->text = str_pad($option->text, strlen($option->text) + 2*($option->level - $parentLevel), '- ', STR_PAD_LEFT);
		}
		//array_unshift($options, JHtml::_('select.option', 0, 'Select Group'));

		return JHtml::_('select.options', $options, 'value', 'text', $selected);
	}
}