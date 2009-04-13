<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_SITE.DS.'components'.DS.'com_content'.DS.'helpers'.DS.'route.php';

class modRelatedItemsHelper
{
	function getList($params)
	{
		$mainframe = JFactory::getApplication();

		$db					=& JFactory::getDBO();
		$user				=& JFactory::getUser();

		$option				= JRequest::getCmd('option');
		$view				= JRequest::getCmd('view');

		$temp				= JRequest::getString('id');
		$temp				= explode(':', $temp);
		$id					= $temp[0];

		$showDate			= $params->get('showDate', 0);

		$nullDate			= $db->getNullDate();


		$date =& JFactory::getDate();
		$now  = $date->toMySQL();

		$related			= array();

		if ($option == 'com_content' && $view == 'article' && $id)
		{

			// select the meta keywords from the item
			$query = 'SELECT metakey' .
					' FROM #__content' .
					' WHERE id = '.(int) $id;
			$db->setQuery($query);

			if ($metakey = trim($db->loadResult()))
			{
				// explode the meta keys on a comma
				$keys = explode(',', $metakey);
				$likes = array ();

				// assemble any non-blank word(s)
				foreach ($keys as $key)
				{
					$key = trim($key);
					if ($key) {
						$likes[] = $db->getEscaped($key);
					}
				}

				if (count($likes))
				{
					// select other items based on the metakey field 'like' the keys found
					$query = 'SELECT a.id, a.title, DATE_FORMAT(a.created, "%Y-%m-%d") AS created, a.catid, cc.access AS cat_access, cc.published AS cat_state,' .
							' CASE WHEN CHAR_LENGTH(a.alias) THEN CONCAT_WS(":", a.id, a.alias) ELSE a.id END as slug,'.
							' CASE WHEN CHAR_LENGTH(cc.alias) THEN CONCAT_WS(":", cc.id, cc.alias) ELSE cc.id END as catslug'.
							' FROM #__content AS a' .
							' LEFT JOIN #__content_frontpage AS f ON f.content_id = a.id' .
							' LEFT JOIN #__categories AS cc ON cc.id = a.catid' .
							' WHERE a.id != '.(int) $id .
							' AND a.state = 1' .
							' AND a.access IN (' .implode(',', $user->authorisedLevels('com_content.article.view')). ')'.
							' AND cc.access IN (' .implode(',', $user->authorisedLevels('com_content.category.view')).')'.
							' AND ( a.metakey LIKE "%'.implode('%" OR a.metakey LIKE "%', $likes).'%" )' .
							' AND ( a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).' )' .
							' AND ( a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).' )';
					$db->setQuery($query);
					$temp = $db->loadObjectList();

					if (count($temp))
					{
						foreach ($temp as $row)
						{
							if (($row->cat_state == 1 || $row->cat_state == '') && ($row->cat_access <= $user->get('aid', 0) || $row->cat_access == ''))
							{
								$row->route = JRoute::_(ContentHelperRoute::getArticleRoute($row->slug, $row->catslug));
								$related[] = $row;
							}
						}
					}
					unset ($temp);
				}
			}
		}

		return $related;
	}
}
