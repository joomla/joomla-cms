<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds component helper.
 *
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 * @since       1.6
 */
class NewsfeedsHelper
{
	public static $extension = 'com_newsfeeds';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string	The name of the active view.
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_NEWSFEEDS_SUBMENU_NEWSFEEDS'),
			'index.php?option=com_newsfeeds&view=newsfeeds',
			$vName == 'newsfeeds'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_NEWSFEEDS_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_newsfeeds',
			$vName == 'categories'
		);
		if ($vName == 'categories')
		{
			JToolbarHelper::title(
				JText::sprintf('COM_CATEGORIES_CATEGORIES_TITLE', JText::_('com_newsfeeds')),
				'newsfeeds-categories');
		}
	}

	/**
	 * Gets a list of the actions that can be performed.
	 *
	 * @param   integer  The category ID.
	 *
	 * @return  JObject
	 */
	public static function getActions($categoryId = 0, $newsfeedId = 0)
	{
		$user	= JFactory::getUser();
		$result	= new JObject;

		if (empty($categoryId))
		{
			$assetName = 'com_newsfeeds';
			$level = 'component';
		}
		else
		{
			$assetName = 'com_newsfeeds.category.'.(int) $categoryId;
			$level = 'category';
		}

		$actions = JAccess::getActions('com_newsfeeds', $level);

		foreach ($actions as $action)
		{
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	public static function getAssociations($pk)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__newsfeeds as c');
		$query->innerJoin('#__associations as a ON a.id = c.id AND a.context='.$db->quote('com_newsfeeds.item'));
		$query->innerJoin('#__associations as a2 ON a.key = a2.key');
		$query->innerJoin('#__newsfeeds as c2 ON a2.id = c2.id');
		$query->innerJoin('#__categories as ca ON c2.catid = ca.id AND ca.extension = '.$db->quote('com_newsfeeds'));
		$query->where('c.id =' . (int) $pk);
		$select = array(
				'c2.language',
				$query->concatenate(array('c2.id', 'c2.alias'), ':') . ' AS id',
				$query->concatenate(array('ca.id', 'ca.alias'), ':') . ' AS catid'
		);
		$query->select($select);
		$db->setQuery($query);
		$contactitems = $db->loadObjectList('language');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}

		foreach ($contactitems as $tag => $item)
		{
			$associations[$tag] = $item;
		}

		return $associations;
	}
}
