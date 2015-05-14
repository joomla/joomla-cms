<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contentitem component helper.
 *
 * @since  1.6
 */
class ContentitemsHelper
{
	public static $extension = 'com_content';

	/**
	 * Adds Count Items for Category Manager.
	 *
	 * @param   object $query The query object of com_categories
	 *
	 * @return  object
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
