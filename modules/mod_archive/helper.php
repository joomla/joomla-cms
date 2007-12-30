<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.utilities.date');

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
		$itemid = isset($item) ? $item->id : 0;

		$i		= 0;
		$lists	= array();
		foreach ( $rows as $row )
		{
			$date = new JDate($row->created);

			$created_month	= $date->toFormat("%m");
			$month_name		= $date->toFormat("%B");
			$created_year	= $date->toFormat("%Y");

			$lists[$i]->link	= JRoute::_('index.php?option=com_content&view=archive&year='.$created_year.'&month='.$created_month.'&Itemid='.$itemid);
			$lists[$i]->text	= $month_name.', '.$created_year;
			$i++;
		}
		return $lists;
	}
}
