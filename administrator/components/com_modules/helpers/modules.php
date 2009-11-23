<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Modules component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_modules
 * @since		1.6
 */
class ModulesHelper
{
	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		// Not used in this component.
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
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action, $user->authorise($action, 'com_modules'));
		}

		return $result;
	}

	/**
	 * Get a list of filter options for the state of a module.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getStateOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option',	'1',	'JOption_Enabled');
		$options[]	= JHtml::_('select.option',	'0',	'JOption_Disabled');
		$options[]	= JHtml::_('select.option',	'-2',	'JOption_Trash');

		return $options;
	}

	/**
	 * Get a list of filter options for the application clients.
	 *
	 * @return	array	An array of JHtmlOption elements.
	 */
	static function getClientOptions()
	{
		// Build the filter options.
		$options	= array();
		$options[]	= JHtml::_('select.option', '0', JText::_('Modules_Option_Site'));
		$options[]	= JHtml::_('select.option', '1', JText::_('Modules_Option_Administrator'));

		return $options;
	}

	static function getPositions($clientId)
	{
		jimport('joomla.filesystem.folder');
		jimport('joomla.database.query');

		$db = JFactory::getDbo();
		$query = new JQuery;

		$query->select('DISTINCT(position)');
		$query->from('#__modules');
		$query->where('`client_id` = '.(int) $clientId);
		$query->order('position');

		$db->setQuery($query);
		$positions = $db->loadResultArray();
		$positions = (is_array($positions)) ? $positions : array();

		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return;
		}

		// Build the list
		$options = array();
		foreach ($positions as $position) {
			$options[]	= JHtml::_('select.option', $position, $position);
		}
		return $options;
	}

	/**
	 * Get a list of the unique modules installed in the client application.
	 *
	 * @param	int		The client id.
	 *
	 * @return	array
	 */
	public static function getModules($clientId)
	{
		jimport('joomla.database.query');

		$db		= JFactory::getDbo();
		$query	= new JQuery;

		$query->select('DISTINCT(module) AS value, module AS text');
		$query->from('#__modules');
		$query->where('`client_id` = '.(int)$clientId);
		$query->order('module');

		$db->setQuery($query);

		return $db->loadObjectList();
	}

	/**
	 * Get a list of the assignment options for modules to menus.
	 *
	 * @param	int		The client id.
	 *
	 * @return	array
	 */
	public static function getAssignmentOptions($clientId)
	{
		$options = array();
		$options[] = JHtml::_('select.option', '0', 'Modules_Option_Menu_All');
		$options[] = JHtml::_('select.option', '-', 'Modules_Option_Menu_None');

		if ($clientId == 0)
		{
			$options[] = JHtml::_('select.option', '1', 'Modules_Option_Menu_Include');
			$options[] = JHtml::_('select.option', '-1', 'Modules_Option_Menu_Exclude');
		}

		return $options;
	}
}