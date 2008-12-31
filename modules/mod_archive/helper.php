<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');



class modArchiveHelper
{
	function getList(&$params)
	{
		//get database
		$db =& JFactory::getDBO();

		$query = 'SELECT MONTH( created ) AS created_month, created, id, sectionid, title, YEAR(created) AS created_year' .
			' FROM #__content' .
			' WHERE ( state = -1 AND checked_out = 0 )' .
			' GROUP BY created_year DESC, created_month DESC';
		$db->setQuery($query, 0, intval($params->get('count')));
		$rows = $db->loadObjectList();

		$menu = &JSite::getMenu();
		$item = $menu->getItems('link', 'index.php?option=com_content&view=archive', true);
		$itemid = isset($item) ? '&Itemid='.$item->id : '';

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			$date =& JFactory::getDate($row->created);

			$created_month	= $date->toFormat("%m");
			$month_name		= $date->toFormat("%B");
			$created_year	= $date->toFormat("%Y");

			$lists[$i]->link	= JRoute::_('index.php?option=com_content&view=archive&year='.$created_year.'&month='.$created_month.$itemid);
			$lists[$i]->text	= $month_name.', '.$created_year;
			$i++;
		}
		return $lists;
	}
}
