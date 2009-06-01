<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_sections
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once (JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php');

class modSectionsHelper
{
	function getList(&$params)
	{
		$app	= &JFactory::getApplication();
		$db		= &JFactory::getDbo();
		$user	= &JFactory::getUser();
		$groups	= implode(',', $user->authorisedLevels());

		$count	= intval($params->get('count', 20));
		$contentConfig 	= &JComponentHelper::getParams('com_content');
		$access	= !$contentConfig->get('show_noauth');

		$now		= date('Y-m-d H:i:s', time() + $app->getCfg('offset') * 60 * 60);
		$nullDate	= $db->getNullDate();


		$query = 'SELECT a.id AS id, a.title AS title, COUNT(b.id) as cnt' .
			' FROM #__sections as a' .
			' LEFT JOIN #__content as b ON a.id = b.sectionid' .
			($access ? ' AND b.access IN ('.$groups.')' : '') .
			' AND (b.publish_up = '.$db->Quote($nullDate).' OR b.publish_up <= '.$db->Quote($now).')' .
			' AND (b.publish_down = '.$db->Quote($nullDate).' OR b.publish_down >= '.$db->Quote($now).')' .
			' WHERE a.scope = "content"' .
			' AND a.published = 1' .
			($access ? ' AND a.access IN ('.$groups.')' : '') .
			' GROUP BY a.id '.
			' HAVING COUNT(b.id) > 0' .
			' ORDER BY a.ordering';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
