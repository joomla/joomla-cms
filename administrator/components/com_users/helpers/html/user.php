<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Utility class for creating different select lists
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	HTML
 * @since		1.5
 */
class JHtmlUser
{
	/**
	* Build the select list for access level
	*/
	function groups($selected = null, $parentId = 0, $type = 'aro')
	{
		JModel::addIncludePath(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_acl'.DS.'models');
		$model = JModel::getInstance('Groups', 'AccessModel', array('ignore_request' => 1));
		$model->setState('type', $type);

		// Set the model state to get the groups tree
		$model->setState('list.select',	'a.id AS value, a.name AS text');
		$model->setState('list.tree',		1);
		$model->setState('list.order',		'a.lft');
		$model->setState('list.parent_id',	$parentId);

		// Get a list without resolving foreign keys
		$options = $model->getList(0);

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