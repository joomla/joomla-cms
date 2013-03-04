<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_related_items
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_content/helpers/route.php';

abstract class modRelatedItemsHelper
{
	public static function getList($params)
	{
		$db			= JFactory::getDbo();
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$userId		= (int) $user->get('id');
		$count		= intval($params->get('count', 5));
		$groups		= implode(',', $user->getAuthorisedViewLevels());
		$date		= JFactory::getDate();

		$option		= JRequest::getCmd('option');
		$view		= JRequest::getCmd('view');

		$temp		= JRequest::getString('id');
		$temp		= explode(':', $temp);
		$id			= $temp[0];

		$showDate	= $params->get('showDate', 0);
		$nullDate	= $db->getNullDate();
		$now		= $date->toSql();
		$related	= array();
		$query		= $db->getQuery(true);

		if ($option == 'com_content' && $view == 'article' && $id)
		{
			// select the meta keywords from the item

			$query->select('metakey');
			$query->from('#__content');
			$query->where('id = ' . (int) $id);
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
						$likes[] = $db->escape($key);
					}
				}

				if (count($likes))
				{
					// select other items based on the metakey field 'like' the keys found
					$query->clear();
					$query->select('a.id');
					$query->select('a.title');
					$query->select('DATE_FORMAT(a.created, "%Y-%m-%d") as created');
					$query->select('a.catid');
					$query->select('cc.access AS cat_access');
					$query->select('cc.published AS cat_state');

		            //sqlsrv changes
			        $case_when = ' CASE WHEN ';
			        $case_when .= $query->charLength('a.alias');
			        $case_when .= ' THEN ';
			        $a_id = $query->castAsChar('a.id');
			        $case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
			        $case_when .= ' ELSE ';
			        $case_when .= $a_id.' END as slug';
					$query->select($case_when);

		            $case_when = ' CASE WHEN ';
		            $case_when .= $query->charLength('cc.alias');
		            $case_when .= ' THEN ';
		            $c_id = $query->castAsChar('cc.id');
		            $case_when .= $query->concatenate(array($c_id, 'cc.alias'), ':');
		            $case_when .= ' ELSE ';
		            $case_when .= $c_id.' END as catslug';
		            $query->select($case_when);
					$query->from('#__content AS a');
					$query->leftJoin('#__content_frontpage AS f ON f.content_id = a.id');
					$query->leftJoin('#__categories AS cc ON cc.id = a.catid');
					$query->where('a.id != ' . (int) $id);
					$query->where('a.state = 1');
					$query->where('a.access IN (' . $groups . ')');
          			$concat_string = $query->concatenate(array('","', ' REPLACE(a.metakey, ", ", ",")', ' ","'));
					$query->where('('.$concat_string.' LIKE "%'.implode('%" OR '.$concat_string.' LIKE "%', $likes).'%")'); //remove single space after commas in keywords)
					$query->where('(a.publish_up = '.$db->Quote($nullDate).' OR a.publish_up <= '.$db->Quote($now).')');
					$query->where('(a.publish_down = '.$db->Quote($nullDate).' OR a.publish_down >= '.$db->Quote($now).')');

					// Filter by language
					if ($app->getLanguageFilter()) {
						$query->where('a.language in (' . $db->Quote(JFactory::getLanguage()->getTag()) . ',' . $db->Quote('*') . ')');
					}

					$db->setQuery($query);
					$qstring = $db->getQuery();
					$temp = $db->loadObjectList();

					if (count($temp))
					{
						foreach ($temp as $row)
						{
							if ($row->cat_state == 1)
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
