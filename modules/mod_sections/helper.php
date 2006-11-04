<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

/// no direct access
defined('_JEXEC') or die('Restricted access');

require_once (JPATH_SITE . '/components/com_content/helpers/content.php');

class modSectionsHelper
{
	function getList(&$params)
	{
		global $mainframe;

		$db		=& JFactory::getDBO();
		$user   =& JFactory::getUser();

		$count	= intval($params->get('count', 20));
		$contentConfig 	= &JComponentHelper::getParams( 'com_content' );
		$access	= !$contentConfig->get('shownoauth');

		$gid 		= $user->get('gid');
		$now		= date('Y-m-d H:i:s', time() + $mainframe->getCfg('offset') * 60 * 60);
		$nullDate	= $db->getNullDate();


		$query = "SELECT a.id AS id, a.title AS title, COUNT(b.id) as cnt" .
			"\n FROM #__sections as a" .
			"\n LEFT JOIN #__content as b ON a.id = b.sectionid" .
			($access ? "\n AND b.access <= $gid" : '') .
			"\n AND ( b.publish_up = '$nullDate' OR b.publish_up <= '$now' )" .
			"\n AND ( b.publish_down = '$nullDate' OR b.publish_down >= '$now' )" .
			"\n WHERE a.scope = 'content'" .
			"\n AND a.published = 1" .
			($access ? "\n AND a.access <= $gid" : '') .
			"\n GROUP BY a.id" .
			"\n HAVING COUNT( b.id ) > 0" .
			"\n ORDER BY a.ordering";
		$db->setQuery($query, 0, $count);
		$rows = $db->loadObjectList();

		return $rows;
	}
}