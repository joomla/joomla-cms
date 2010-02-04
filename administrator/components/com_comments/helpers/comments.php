<?php
/**
 * @version		$Id$
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

/**
 * Comments component helper.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_comments
 * @since		1.3
 */
class CommentsHelper
{
	public static $extention = 'com_comments';

	/**
	 * Configure the Linkbar.
	 *
	 * @param	string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JSubMenuHelper::addEntry(
			JText::_('Comments_Submenu_Comments'),
			'index.php?option=com_comments&view=comments',
			$vName == 'comments'
		);
		JSubMenuHelper::addEntry(
			JText::_('Comments_Submenu_Threads'),
			'index.php?option=com_comments&view=threads',
			$vName == 'threads'
		);
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param	int		The category ID.
	 *
	 * @return	JObject
	 */
	public static function getActions($categoryId = 0, $newsfeedId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		$assetName = 'com_comments';

		$actions = array(
			'core.admin', 'core.manage', 'core.create', 'core.edit', 'core.edit.state', 'core.delete'
		);

		foreach ($actions as $action) {
			$result->set($action,	$user->authorise($action, $assetName));
		}

		return $result;
	}

	/**
	 * Get a list of the thread contexts
	 *
	 * @return	mixed	An array if successful, false otherwise and the internal error is set.
	 */
	function getContextOptions()
	{
		$db		= JFactory::getDbo();
		$query	= $db->getQuery(true);

		$query->select('DISTINCT(context) AS value, context AS text');
		$query->from('#__social_threads');
		$query->order('context');

		$db->setQuery($query);
		$result = $db->loadObjectList();

		if ($error = $db->getErrorMsg()) {
			$this->setError($error);
			return false;
		}

		return $result;
	}

}