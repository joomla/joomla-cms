<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content component helper.
 *
 * @since  1.6
 */
class ContentHelper extends JHelperContent
{
	public static $extension = 'com_content';

	/**
	 * Configure the Linkbar.
	 *
	 * @param   string  $vName  The name of the active view.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	public static function addSubmenu($vName)
	{
		JHtmlSidebar::addEntry(
			JText::_('JGLOBAL_ARTICLES'),
			'index.php?option=com_content&view=articles',
			$vName == 'articles'
		);
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&extension=com_content',
			$vName == 'categories');
		JHtmlSidebar::addEntry(
			JText::_('COM_CONTENT_SUBMENU_FEATURED'),
			'index.php?option=com_content&view=featured',
			$vName == 'featured'
		);
	}

	/**
	 * Applies the content tag filters to arbitrary text as per settings for current user group
	 *
	 * @param   text  $text  The string to filter
	 *
	 * @return  string  The filtered string
	 *
	 * @deprecated  4.0  Use JComponentHelper::filterText() instead.
	*/
	public static function filterText($text)
	{
		JLog::add('ContentHelper::filterText() is deprecated. Use JComponentHelper::filterText() instead.', JLog::WARNING, 'deprecated');

		return JComponentHelper::filterText($text);
	}

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   JDatabaseQuery  &$query  The query object of com_categories
	 *
	 * @return  JDatabaseQuery
	 *
	 * @since   3.4
	 */
	public static function countItems(&$query)
	{
		// Join articles to categories and count published items
		$query->select('COUNT(DISTINCT cp.id) AS count_published');
		$query->join('LEFT', '#__content AS cp ON cp.catid = a.id AND cp.state = 1');

		// Count unpublished items
		$query->select('COUNT(DISTINCT cu.id) AS count_unpublished');
		$query->join('LEFT', '#__content AS cu ON cu.catid = a.id AND cu.state = 0');

		// Count archived items
		$query->select('COUNT(DISTINCT ca.id) AS count_archived');
		$query->join('LEFT', '#__content AS ca ON ca.catid = a.id AND ca.state = 2');

		// Count trashed items
		$query->select('COUNT(DISTINCT ct.id) AS count_trashed');
		$query->join('LEFT', '#__content AS ct ON ct.catid = a.id AND ct.state = -2');

		return $query;
	}

}
