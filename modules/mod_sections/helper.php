<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

class modSectionsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$user	=& JFactory::getUser();

		$count	= intval($params->get('count', 20));
		$contentConfig 	= &JComponentHelper::getParams( 'com_content' );
		$access	= !$contentConfig->get('shownoauth');

		$gid 		= $user->get('aid', 0);
		$now		= date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);
		$nullDate	= $db->getNullDate();


		$query = 'SELECT a.id AS id, a.title AS title, COUNT(b.id) as cnt' .
			' FROM #__sections as a' .
			' LEFT JOIN #__content as b ON a.id = b.sectionid' .
			($access ? ' AND b.access <= '.(int) $gid : '') .
			' AND ( b.publish_up = '.$db->Quote($nullDate).' OR b.publish_up <= '.$db->Quote($now).' )' .
			' AND ( b.publish_down = '.$db->Quote($nullDate).' OR b.publish_down >= '.$db->Quote($now).' )' .
			' WHERE a.scope = "content"' .
			' AND a.published = 1' .
			($access ? ' AND a.access <= '.(int) $gid : '') .
			' GROUP BY a.id '.
			' HAVING COUNT( b.id ) > 0' .
			' ORDER BY a.ordering';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		return $rows;
	}
}
