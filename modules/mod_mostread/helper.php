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

		$count		= intval($params->get('count', 5));
		$catid		= trim($params->get('catid'));
		$secid		= trim($params->get('secid'));
		$show_front	= $params->get('show_front', 1);
		$aid		= $user->get('aid', 0);

		$contentConfig = &JComponentHelper::getParams( 'com_content' );
		$access		= !$contentConfig->get('shownoauth');

		$nullDate	= $db->getNullDate();
		$now		= date('Y-m-d H:i:s', time());

		//Content Items only
		$query = 'SELECT a.*,' .
			' CASE WHEN CHAR_LENGTH(a.title_alias) THEN CONCAT_WS(":", a.id, a.title_alias) ELSE a.id END as slug,'.
			' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
			' FROM #__content AS a' .
			' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' .
			' INNER JOIN #__categories AS cc ON cc.id = a.catid' .
			' INNER JOIN #__sections AS s ON s.id = a.sectionid' .
			' WHERE ( a.state = 1 AND s.id > 0 )' .
			' AND ( a.publish_up = "'.$nullDate.'" OR a.publish_up <= "'.$now.'" )' .
			' AND ( a.publish_down = "'.$nullDate.'" OR a.publish_down >= "'.$now.'" )'.
			($access ? ' AND a.access <= ' .(int) $aid. ' AND cc.access <= ' .(int) $aid. ' AND s.access <= ' .(int) $aid : '').
			($catid ? ' AND ( cc.id IN ( '.$catid.' ) )' : '').
			($secid ? ' AND ( s.id IN ( '.$secid.' ) )' : '').
			($show_front == '0' ? ' AND f.content_id IS NULL' : '').
			' AND s.published = 1' .
			' AND cc.published = 1' .
			' ORDER BY a.hits DESC';
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			$lists[$i]->link = ContentHelper::getArticleRoute($row->slug, $row->catslug, $row->sectionid);
			$lists[$i]->text = htmlspecialchars( $row->title );
			$i++;
		}

		return $lists;
	}
}
