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

	public static function getAssociations($pk)
	{
		$associations = array();
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->from('#__weblinks as c');
		$query->innerJoin('#__associations as a ON a.id = c.id AND a.context='.$db->quote('com_weblinks.item'));
		$query->innerJoin('#__associations as a2 ON a.key = a2.key');
		$query->innerJoin('#__weblinks as c2 ON a2.id = c2.id');
		$query->innerJoin('#__categories as ca ON c2.catid = ca.id AND ca.extension = '.$db->quote('com_weblinks'));
		$query->where('c.id =' . (int) $pk);
		$select = array(
				'c2.language',
				$query->concatenate(array('c2.id', 'c2.alias'), ':') . ' AS id',
				$query->concatenate(array('ca.id', 'ca.alias'), ':') . ' AS catid'
		);
		$query->select($select);
		$db->setQuery($query);
		$weblinksitems = $db->loadObjectList('language');

		// Check for a database error.
		if ($error = $db->getErrorMsg())
		{
			JError::raiseWarning(500, $error);
			return false;
		}

		foreach ($weblinksitems as $tag => $item)
		{
			$associations[$tag] = $item;
		}

		return $associations;
	}
}
