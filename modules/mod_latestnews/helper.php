<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_latestnews
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

class modLatestNewsHelper
{
	function getList(&$params)
	{
		$db			= &JFactory::getDbo();
		$user		= &JFactory::getUser();
		$userId		= (int) $user->get('id');

		$count		= (int) $params->get('count', 5);
		$catid		= trim($params->get('catid'));
		$show_front	= $params->get('show_front', 1);
		$groups		= $user->authorisedLevels();
		$groupsA	= implode(',', $groups);

		$contentConfig = &JComponentHelper::getParams('com_content');
		$access		= !$contentConfig->get('show_noauth');

		$nullDate	= $db->getNullDate();

		$date = &JFactory::getDate();
		$now = $date->toMySQL();

		$where		= 'a.state = 1'
			. ' AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).')'
			. ' AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')'
			;

		// User Filter
		switch ($params->get('user_id'))
		{
			case 'by_me':
				$where .= ' AND (created_by = ' . (int) $userId . ' OR modified_by = ' . (int) $userId . ')';
				break;
			case 'not_me':
				$where .= ' AND (created_by <> ' . (int) $userId . ' AND modified_by <> ' . (int) $userId . ')';
				break;
		}

		// Ordering
		switch ($params->get('ordering'))
		{
			case 'm_dsc':
				$ordering		= 'a.modified DESC, a.created DESC';
				break;
			case 'mc_dsc':
				$ordering 		= 'dateslug DESC';
				break; 
			case 'c_dsc':
			default:
				$ordering		= 'a.created DESC';
				break;
		}

		if ($catid)
		{
			$ids = explode(',', $catid);
			JArrayHelper::toInteger($ids);
			$catCondition = ' AND (cc.id=' . implode(' OR cc.id=', $ids) . ')';
		}

		// Content Items only
		$query = 'SELECT a.*, ' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug,'.
			' CASE WHEN (a.modified = "0000-00-00 00:00:00") THEN a.created ELSE a.modified END AS dateslug'. 
			' FROM #__content AS a' .
			($show_front == '0' ? ' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' : '') .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' WHERE '. $where .' AND s.id > 0' .
			($access ? ' AND a.access IN ('.$groups.') AND cc.access IN ('.$groups.') AND s.access IN ('.$groups.')' : '').
			($catid ? $catCondition : '').
			($show_front == '0' ? ' AND f.content_id IS NULL ' : '').
			' AND cc.published = 1' .
			' ORDER BY '. $ordering;
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ($rows as $row)
		{
			if (in_array($row->access, $groups))
			{
				$lists[$i]->link = JRoute::_(ContentRoute::article($row->slug, $row->catslug, $row->sectionid));
			}
			else {
				$lists[$i]->link = JRoute::_('index.php?option=com_users&view=login');
			}
			$lists[$i]->text = htmlspecialchars($row->title);
			$i++;
		}

		return $lists;
	}
}
