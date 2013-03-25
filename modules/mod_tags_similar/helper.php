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
abstract class ModTagssimilarHelper
{
	public static function getList($params)
	{
		$db         = JFactory::getDbo();
		$app        = JFactory::getApplication();
		$user       = JFactory::getUser();
		$groups     = implode(',', $user->getAuthorisedViewLevels());
		$matchtype  = $params->get('matchtype', 'all');
		$maximum    = $params->get('maximum', 5);
		$tagsHelper = new JTags;
		$option     = $app->input->get('option');
		$view       = $app->input->get('view');
		$prefix     = $option . '.' . $view;
		$id         = $app->input->getString('id');

		// Strip off any slug data.
		if (substr_count($id, ':') > 0)
		{
			$idexplode = explode(':', $id);
			$id        = $idexplode[0];
		}

		// For now assume com_tags and com_users do not have tags.
		// This module does not apply to list views in general at this point.
		if ($option != 'com_tags' && $view != 'category'  && $option != 'com_users')
		{
			$tagsToMatch = $tagsHelper->getTagIds($id, $prefix);
			if (!$tagsToMatch || is_null($tagsToMatch))
			{
				return $results = false;
			}

			$tagCount = substr_count($tagsToMatch, ',') + 1;

			$query = $db->getQuery(true);

			$query->select(
				array(
					$db->quoteName('m.tag_id'),
					$db->quoteName('m.core_content_id'),
					$db->quoteName('m.content_item_id'),
					$db->quoteName('m.type_alias'),
					'COUNT( '  . $db->quoteName('tag_id') . ') AS ' . $db->quoteName('count'),
					$db->quoteName('t.access'),
					$db->quoteName('t.id'),
					$db->quoteName('cc.core_title'),
					$db->quoteName('cc.core_alias'),
					$db->quoteName('cc.core_catid'),
					$db->quoteName('cc.core_language')
				)
			);
			$query->group($db->quoteName(array('tag_id', 'm.content_item_id', 'm.type_alias', 't.access')))
				->from($db->quoteName('#__contentitem_tag_map', 'm'))
				->having('t.access IN (' . $groups . ')')
				->having($db->quoteName('m.tag_id') . ' IN (' . $tagsToMatch . ')')
				->having($db->quoteName('m.content_item_id') . ' <> ' . $id);

			if ($matchtype == 'all' && $tagCount > 0)
			{
				$query->having('COUNT( '  . $db->quoteName('tag_id') . ')  = ' . $tagCount);
			}
			elseif ($matchtype == 'half' && $tagCount > 0)
			{
				$tagCountHalf = ceil($tagCount / 2);
				$query->having('COUNT( '  . $db->quoteName('tag_id') . ')  >= ' . $tagCountHalf);
			}

			// Only return published tags
			$query->where($db->quoteName('cc.core_state') . ' = 1 ');

			// Optionally filter on language
			$language = JComponentHelper::getParams('com_tags')->get('tag_list_language_filter', 'all');

			if ($language != 'all')
			{
				if ($language == 'current_language')
				{
					$language = JHelperContent::getCurrentLanguage();
				}
				$query->where($db->quoteName('cc.core_language') . ' IN (' . $db->quote($language) . ', ' . $db->quote('*') . ')');
			}

			$query->join('INNER', $db->quoteName('#__tags', 't') . ' ON ' . $db->quoteName('m.tag_id') . ' = ' . $db->quoteName('t.id'))
				->join('INNER', $db->quoteName('#__core_content', 'cc') . ' ON ' . $db->quoteName('m.core_content_id') . ' = ' . $db->quoteName('cc.core_content_id'))

				->order($db->quoteName('count') . ' DESC');
			$db->setQuery($query, 0, $maximum);
			$results = $db->loadObjectList();

			foreach ($results as $result)
			{
				$explodedAlias = explode('.', $result->type_alias);
				$result->link = 'index.php?option=' . $explodedAlias[0] . '&view=' . $explodedAlias[1] . '&id=' . (int) $result->content_item_id . '-' . $result->core_alias;
			}

			return $results;
		}
		else
		{
			return;
		}
	}
}
