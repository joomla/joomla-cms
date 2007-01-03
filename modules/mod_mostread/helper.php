<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE . '/components/com_content/helpers/content.php');

class modMostReadHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db			=& JFactory::getDBO();
		$user		=& JFactory::getUser();

		$type		= intval($params->get('type', 1));
		$count		= intval($params->get('count', 5));
		$catid		= trim($params->get('catid'));
		$secid		= trim($params->get('secid'));
		$show_front	= $params->get('show_front', 1);
		$aid		= $user->get('aid', 0);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$access		= !$contentConfig->get('shownoauth');

		$nullDate	= $db->getNullDate();
		$now		= date('Y-m-d H:i:s', time());

		// select between Content Items, Static Content or both
		switch ($type)
		{
			case 2 :
				//Static Content only
				$query = "SELECT a.id, a.title, m.id AS my_itemid " .
					"\n FROM #__content AS a " .
					"\n LEFT OUTER JOIN #__menu AS m ON m.componentid = a.id " .
					"\n WHERE ( a.state = 1 AND a.sectionid = 0 )" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )".
					"\n AND m.type = 'content_typed' ".
					($access ? "\n AND a.access <= " .(int) $aid : '').
					"\n ORDER BY a.hits DESC";
				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();
			break;

			case 3 :
				//Both
				$query = "SELECT a.id, a.title, a.sectionid, a.catid, cc.access AS cat_access, s.access AS sec_access, cc.published AS cat_state, s.published AS sec_state" .
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n LEFT JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n WHERE a.state = 1" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )".
					($access ? "\n AND a.access <= " .(int) $aid : '') .
					"\n ORDER BY a.hits DESC";
				$db->setQuery( $query, 0, $count );
				$rows = $db->loadObjectList();
			break;

			case 1 :
			default :
				//Content Items only
				$query = "SELECT a.id, a.title, a.sectionid, a.catid" .
					"\n FROM #__content AS a" .
					"\n LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id" .
					"\n INNER JOIN #__categories AS cc ON cc.id = a.catid" .
					"\n INNER JOIN #__sections AS s ON s.id = a.sectionid" .
					"\n WHERE ( a.state = 1 AND a.sectionid > 0 )" .
					"\n AND ( a.publish_up = '$nullDate' OR a.publish_up <= '$now' )" .
					"\n AND ( a.publish_down = '$nullDate' OR a.publish_down >= '$now' )".
					($access ? "\n AND a.access <= " .(int) $aid. " AND cc.access <= " .(int) $aid. " AND s.access <= " .(int) $aid : '').
					($catid ? "\n AND ( a.catid IN ( $catid ) )" : '').
					($secid ? "\n AND ( a.sectionid IN ( $secid ) )" : '').
					($show_front == '0' ? "\n AND f.content_id IS NULL" : '').
					"\n AND s.published = 1" .
					"\n AND cc.published = 1" .
					"\n ORDER BY a.hits DESC";
				$db->setQuery($query, 0, $count);
				$rows = $db->loadObjectList();
			break;
		}

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			// get Itemid
			switch ( $type )
			{
				case 2:
					$Itemid = $row->my_itemid;
					break;

				case 3:
					if (($row->cat_state == 1 || $row->cat_state == '') && ($row->sec_state == 1 || $row->sec_state == '') && ($row->cat_access <= $user->get('aid', 0) || $row->cat_access == '' || !$access) && ($row->sec_access <= $user->get('aid', 0) || $row->sec_access == '' || !$access))
					{
						if ($row->sectionid) {
							$row->my_itemid = JContentHelper::getItemid($row->id);
						} else {
							$row->my_itemid = null;
						}
					}
					break;

				case 1:
				default:
					$row->my_itemid = JContentHelper::getItemid($row->id);
					break;
			}

			// & xhtml compliance conversion
			$row->title = ampReplace( $row->title );

			$link = sefRelToAbs( 'index.php?option=com_content&amp;view=article&amp;id='. $row->id . '&amp;Itemid='. $row->my_itemid );

			$lists[$i]->link	= $link;
			$lists[$i]->text	= $row->title;
			$i++;
		}

		return $lists;
	}
}