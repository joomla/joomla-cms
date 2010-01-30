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
		$db 	= &JFactory::getDbo();
		$user 	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		$catid 	= (int) $params->get('catid', 0);

		$limit = $params->get('count', 5);
		$ordering = $params->get('ordering', 'title');		
		$direction = $params->get('direction', 'asc');
				
		$query = 'SELECT a.id, a.title, DATE_FORMAT(a.date, "%Y-%m-%d") AS created, '. 
					' a.catid, cc.access AS cat_access, cc.published AS cat_state,' .
				  ' a.url, a.description, a.hits, a.ordering, '.
					' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
					' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.					
					' FROM #__weblinks AS a' .
					' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
					' WHERE a.state = 1 ' .
					' 	AND cc.published = 1' .												
					' 	AND a.archived = 0' .
					' 	AND a.approved = 1' .
					' 	AND (a.checked_out = 0 OR a.checked_out = '.$user->id.')' .								
					'		AND a.access IN (' . $groups . ')' .
					'		AND cc.access IN (' . $groups . ')' . 
					' AND cc.id = '. (int) $catid .
					' AND cc.published = 1' .
					' ORDER BY ' . $ordering . ' ' . $direction;					
		
		$db->setQuery($query, 0, $limit);		
		$rows = $db->loadObjectList();

		return $rows;

	}
}
