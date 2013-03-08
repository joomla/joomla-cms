<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
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
		$tagsHelper  = new JTags;
		$option		= $app->input->get('option');
		$view		= $app->input->get('view');
		$prefix		= $option . '.' . $view;
		$id			= $app->input->getString('id');

		// Strip off any slug data.
		if (substr_count($id, ':') > 0)
		{
				$idexplode = explode(':', $id);
				$id = $idexplode[0];
		}

		// For now assume com_tags does not have tags.
		// This module does not apply to list views in general at this point.
		if ($option != 'com_tags' && $view != 'category')
		{
			$tagsToMatch= $tagsHelper->getTagIds($id, $prefix);
			if (!$tagsToMatch || is_null($tagsToMatch))
			{
				return $results = false;
			}

			$tagCount	= substr_count($tagsToMatch, ',') + 1;

			$query		= $db->getQuery(true);

			$query->select(array($db->quoteName('tag_id'), $db->quoteName('content_item_id'), $db->quoteName('type_alias'),
						$query->concatenate(array('type_alias','content_item_id'),'.') . ' AS ' . $db->qn('uniquename') ,
						' COUNT(DISTINCT '. $db->qn('tag_id') .') AS ' . $db->qn('count'), $db->qn('t.access')));
			$query->group($db->qn('tag_id'));
			$query->from($db->quoteName('#__contentitem_tag_map'));
			$query->having('t.access IN (' . $groups . ')');
			$query->having($db->quoteName('tag_id') . ' IN (' . $tagsToMatch . ')');
			$query->having($db->q($prefix . '.' . $id ) .  ' <> ' . $db->quoteName('uniquename'));

				if ($matchtype == 'all' && $tagCount > 0)
				{
					$query->having('count = ' . $tagCount  );
				}
				elseif ($matchtype == 'half'  && $tagCount > 0)
				{
					$tagCountHalf = ceil($tagCount/2);
					$query->having('count >= ' . $tagCountHalf );
				}

				$query->join('LEFT', $db->qn('#__tags', 't') .  ' ON ' . $db->qn('tag_id') . ' = ' . $db->qn('t.id'));
				$query->order('count DESC LIMIT 0,' . $maximum);
				$db->setQuery($query);
				$results = $db->loadObjectList();

				foreach ($results as $i => $result)
				{
					// Get the data for the matching item. We have to get it  all because we don't know if it uses name or title.
					$tagHelper = new JTags;

					//$explodedTypeAlias = $tagHelper->explodeTypeAlias($result->type_alias);
					//$itemUrl = $tagHelper->getContentItemUrl($result->type_alias, $explodedTypeAlias, $result->content_item_id);
					$table = $tagHelper->getTableName($result->type_alias);

					if (!empty($result->content_item_id))
					{
						$queryi = $db->getQuery(true);
						$queryi->select('*');
						$queryi->from($table);
						$queryi->where($db->qn('id') . ' = ' . $result->content_item_id);
						$db->setQuery($queryi);
						$result->itemData[$i] = $db->loadAssoc();
						$explodedTypeAlias = $tagHelper->explodeTypeAlias($result->type_alias);
						$result->itemData[$i]['itemUrl'] = $tagHelper->getContentItemUrl($result->type_alias, $explodedTypeAlias, $result->content_item_id);
					}
					else
					{
						unset($results[$i]);
					}
				}
				return $results;
			}

		else
		{
			return;
		}
	}
}
