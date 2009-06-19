<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_mostread
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modMostReadHelper
{
	function getList(&$params)
	{
		$db			= &JFactory::getDbo();
		$user		= &JFactory::getUser();

		$count		= intval($params->get('count', 5));
		$catid		= trim($params->get('catid'));
		$show_front	= $params->get('show_front', 1);
		$groups		= implode(',', $user->authorisedLevels());

		$contentConfig = &JComponentHelper::getParams('com_content');
		$access		= !$contentConfig->get('show_noauth');

		$nullDate	= $db->getNullDate();
		$date = &JFactory::getDate();
		$now  = $date->toMySQL();

		if ($catid) {
			$ids = explode(',', $catid);
			JArrayHelper::toInteger($ids);
			$catCondition = ' AND (cc.id=' . implode(' OR cc.id=', $ids) . ')';
		}

		//Content Items only
		$query = 'SELECT a.*,' .
			' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' WHERE (a.state = 1 AND s.id > 0)' .
			' AND (a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).')' .
			' AND (a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')'.
			($access ? ' AND a.access IN ('.$groups.') AND cc.access IN ('.$groups.') AND s.access IN ('.$groups.')' : '').
			($catid ? $catCondition : '').
			($show_front == '0' ? ' AND f.content_id IS NULL' : '').
			' AND cc.published = 1' .
			' ORDER BY a.hits DESC';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ($rows as $row)
		{
			if (!$user->get('guest'))
			{
				$lists[$i]->link = JRoute::_(ContentRoute::article($row->slug, $row->catslug, $row->sectionid));
			} else {
				$lists[$i]->link = JRoute::_('index.php?option=com_users&view=login');
			}
			$lists[$i]->text = htmlspecialchars($row->title);
			$i++;
		}

		return $lists;
	}
}
