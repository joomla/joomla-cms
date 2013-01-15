<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Helper for mod_tags_popular
 *
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 * @since       3.1
 */
abstract class modTagssimilarHelper
{
	public static function getList($params)
	{
		$db			= JFactory::getDbo();
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$groups		= implode(',', $user->getAuthorisedViewLevels());
		$date		= JFactory::getDate();
		$matchtype 	= $params->get('matchtype', 'all');
		$maximum 	= $params->get('maximum', 5);
		$tagsHelper  = new JTagsHelper;
		$option		= $app->input->get('option');
		$view		= $app->input->get('view');
		$prefix		= $option . '.' . $view;
		$id			= $app->input->getString('id');
		$name		= $prefix . '.' . $id;

		$tagsToMatch= $tagsHelper->getTagIds($id, $prefix);
		if (!$tagsToMatch || is_null($tagsToMatch))
		{
			return $results = false;
		}

		$tagCount	= substr_count($tagsToMatch, ',') + 1;

		$query		= $db->getQuery(true);

			$query->select(array($db->quoteName('tag_id'), $db->quoteName('item_name'), ' COUNT(*) AS count', 't.title', 't.access'));
			$query->group($db->quoteName('item_name'));
			$query->from($db->quoteName('#__contentitem_tag_map'));
			$query->where('t.access IN (' . $groups . ')');
			$query->where($db->quoteName('tag_id') . ' IN (' . $tagsToMatch . ')');
			$query->where($db->quoteName('item_name') .  ' <> ' . $db->q($name));

			if ($matchtype == 'all' && $tagCount > 0)
			{
				$query->having('count = ' . $tagCount  );
			}
			elseif ($matchtype == 'half'  && $tagCount > 0)
			{
				$tagCountHalf = ceil($tagCount/2);
				$query->having('count >= ' . $tagCountHalf );
			}

			$query->join('LEFT','#__tags AS t ON tag_id=t.id');
			$query->order('count DESC LIMIT 0,' . $maximum);
			$db->setQuery($query);
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				// Get the data for the matching item. We have to get it  all because we don't know if it uses name or title.
				$tagHelper = new JTagsHelper;
				$explodedItemName = $tagHelper->explodeTagItemName($result->item_name);
				$result->itemUrl = $tagHelper->getContentItemUrl($result->item_name, $explodedItemName);
				$table = $tagsHelper->getTableName($result->item_name, $explodedItemName);

				$queryi = $db->getQuery(true);
				$queryi->select('*');
				$queryi->from($table);
				$queryi->where($db->qn('id') . '= ' . $explodedItemName[2]);
				$db->setQuery($queryi);
				$result->itemData = $db->loadAssoc();
			}

		return $results;
	}
}
