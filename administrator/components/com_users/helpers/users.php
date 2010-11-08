<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Users component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_weblinks
 * @since		1.6
 */
class UsersHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('COM_USERS_SUBMENU_USERS'),
			'index.php?option=com_users&view=users',
			$vName == 'users'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_USERS_SUBMENU_GROUPS'),
			'index.php?option=com_users&view=groups',
			$vName == 'groups'
		);
		JSubMenuHelper::addEntry(
			JText::_('COM_USERS_SUBMENU_LEVELS'),
			'index.php?option=com_users&view=levels',
			$vName == 'levels'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @return	JObject
	 */
	public static function getActions()
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.own', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, 'com_users'));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the blocked state of a user.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getStateOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '0', JText::_('JENABLED'));
		$options[]	= JHtml::_('select.option', '1', JText::_('JDISABLED'));

		return $options;
	}

	/**
	 * Get a list of filter options for the activated state of a user.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getActiveOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '0', JText::_('COM_USERS_ACTIVATED'));
		$options[]	= JHtml::_('select.option', '1', JText::_('COM_USERS_UNACTIVATED'));

		return $options;
	}

	/**
	 * Get a list of the user groups for filtering.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getGroups()
	{
		$db = JFactory::getDbo();
		$db->setQuery(
			'SELECT a.id AS value, a.title AS text, COUNT(DISTINCT b.id) AS level' .
			' FROM #__usergroups AS a' .
			' LEFT JOIN `#__usergroups` AS b ON a.lft > b.lft AND a.rgt < b.rgt' .
			' GROUP BY a.id' .
			' ORDER BY a.lft ASC'
		);
		$options = $db->loadObjectList();

		// Check for a database error.
		if ($db->getErrorNum()) {
			JError::raiseNotice(500, $db->getErrorMsg());
			return null;
		}

		foreach ($options as &$option) {
			$option->text = str_repeat('- ',$option->level).$option->text;
		}

		return $options;
	}

}
