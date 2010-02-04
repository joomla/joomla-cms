<?php
/**
 * @version		$Id: helper.php 12152 2009-06-19 23:32:12Z eddieajau $
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_weblinks'.DS.'helpers'.DS.'route.php');

class modWeblinksHelper
{
	function getList($params)
	{
		$db	= &JFactory::getDbo();
		$user	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		$catid	= (int) $params->get('catid', 0);

		$limit = $params->get('count', 5);
		$ordering = $params->get('ordering', 'title');
		$direction = $params->get('direction', 'asc');

		$query	= $db->getQuery(true);

		$query->select('a.id');
		$query->select('a.title');
		$query->select('DATE_FORMAT(a.date, "%Y-%m-%d") AS created');
		$query->select('a.catid');
		$query->select('cc.access AS cat_access');
		$query->select('cc.published AS cat_state');
		$query->select('a.url');
		$query->select('a.description');
		$query->select('a.hits');
		$query->select('a.ordering');
		$query->select('CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug');
		$query->select('CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug');
		$query->from('#__weblinks AS a');
		$query->innerJoin('#__categories AS cc ON cc.id = a.catid');
		$query->where('a.state = 1');
		$query->where('cc.published = 1');
		$query->where('a.archived = 0');
		$query->where('a.approved = 1');
		$query->where('(a.checked_out = 0 OR a.checked_out = '.$user->id.')');
		$query->where('a.access IN (' . $groups . ')');
		$query->where('cc.access IN (' . $groups . ')');
		$query->where('cc.id = '. (int) $catid);
		$query->where('cc.published = 1');
		$query->order($ordering . ' ' . $direction);

		$db->setQuery($query, 0, $limit);
		$rows = $db->loadObjectList();

		return $rows;

	}
}
