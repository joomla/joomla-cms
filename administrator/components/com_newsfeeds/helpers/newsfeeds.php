<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds component helper.
 *
 * @since  1.6
 */
class NewsfeedsHelper extends JHelperContent
{
	public static $extension = 'com_newsfeeds';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
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
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   JDatabaseQuery  $query  The query object of com_categories
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.4
	 */
	public static function countItems($query)
	{
		// Join articles to categories and count published items
		$query->select('COUNT(DISTINCT cp.id) AS count_published');
		$query->join('LEFT', '#__newsfeeds AS cp ON cp.catid = a.id AND cp.published = 1');

		// Count unpublished items
		$query->select('COUNT(DISTINCT cu.id) AS count_unpublished');
		$query->join('LEFT', '#__newsfeeds AS cu ON cu.catid = a.id AND cu.published = 0');

		// Count archived items
		$query->select('COUNT(DISTINCT ca.id) AS count_archived');
		$query->join('LEFT', '#__newsfeeds AS ca ON ca.catid = a.id AND ca.published = 2');

		// Count trashed items
		$query->select('COUNT(DISTINCT ct.id) AS count_trashed');
		$query->join('LEFT', '#__newsfeeds AS ct ON ct.catid = a.id AND ct.published = -2');

		return $query;
	}

}
