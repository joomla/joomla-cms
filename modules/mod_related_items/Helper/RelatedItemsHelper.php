<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RelatedItems\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;
use Joomla\Database\ParameterType;

\JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * Helper for mod_related_items
 *
 * @since  1.5
 */
abstract class RelatedItemsHelper
{
	/**
	 * Get a list of related articles
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 */
	public static function getList(&$params)
	{
		$db        = Factory::getDbo();
		$app       = Factory::getApplication();
		$input     = $app->input;
		$groups    = Factory::getUser()->getAuthorisedViewLevels();
		$maximum   = (int) $params->get('maximum', 5);
		$factory   = $app->bootComponent('com_content')->getMVCFactory();
		$condition = ContentComponent::CONDITION_PUBLISHED;

		// Get an instance of the generic articles model
		$articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

		// Set application parameters in model
		$articles->setState('params', $app->getParams());

		$option = $input->get('option');
		$view   = $input->get('view');

		$temp = $input->getString('id');
		$temp = explode(':', $temp);
		$id   = (int) $temp[0];

		$nullDate = $db->getNullDate();
		$now      = Factory::getDate()->toSql();
		$related  = [];
		$query    = $db->getQuery(true);

		if ($option === 'com_content' && $view === 'article' && $id)
		{
			// Select the meta keywords from the item
			$query->select('metakey')
				->from('#__content')
				->where($db->quoteName('id') . ' = :id')
				->bind(':id', $id, ParameterType::INTEGER);
			$db->setQuery($query);

			try
			{
				$metakey = trim($db->loadResult());
			}
			catch (\RuntimeException $e)
			{
				$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return array();
			}

			// Explode the meta keys on a comma
			$keys  = explode(',', $metakey);
			$likes = [];

			// Assemble any non-blank word(s)
			foreach ($keys as $key)
			{
				$key = trim($key);

				if ($key)
				{
					$likes[] = $db->escape($key);
				}
			}

			if (count($likes))
			{
				// Select other items based on the metakey field 'like' the keys found
				$query->clear()
					->select([
						$db->quoteName(['a.id', 'a.title', 'a.catid', 'a.language']),
						$db->quoteName(['cc.access', 'cc.published'], ['cat_access', 'cat_state']).
						'CAST(' . $db->quoteName('a.created') . ' AS DATE) AS created'
						]);

				$case_when = ' CASE WHEN ';
				$case_when .= $query->charLength($db->quoteName('a.alias'), '!=', '0');
				$case_when .= ' THEN ';
				$a_id      = $query->castAsChar($db->quoteName('a.id'));
				$case_when .= $query->concatenate([$a_id, $db->quoteName('a.alias')], ':');
				$case_when .= ' ELSE ';
				$case_when .= $a_id . ' END AS slug';

				$query->select($case_when)
					->from($db->quoteName('__content', 'a'))
					->leftJoin($db->quoteName('#__categories', 'cc'), $db->quoteName('cc.id') . ' = ' . $db->quoteName('a.catid'))
					->leftJoin($db->quoteName('#__content_frontpage', 'f'), $db->quoteName('f.content_id') . ' = ' . $db->quoteName('a.id'))
					->leftJoin($db->quoteName('#__workflow_associations', 'wa'), $db->quoteName('wa.item_id') . ' = ' . $db->quoteName('a.id'))
					->leftJoin($db->quoteName('#__workflow_stages', 'ws'), $db->quoteName('ws.id') . ' = ' . $db->quoteName('wa.stage_id'))
					->where($db->quoteName('a.id') . ' != :id')
					->where($db->quoteName('ws.condition') . ' = :condition')
					->whereIn($db->quoteName('a.access'), $groups)
					->bind(':id', $id, ParameterType::INTEGER)
					->bind(':condition', $condition, ParameterType::INTEGER);

				$binds  = [];
				$wheres = [];

				foreach ($likes as $keyword)
				{
					$binds[] = '%' . $keyword . '%';
				}

				$bindNames = $query->bindArray($binds, parameterType::STRING);

				foreach ($bindNames as $keyword)
				{
					$wheres[] = 'a.metakey LIKE ' . $keyword;
				}

				$query->where('(' . implode(' OR ', $wheres) . ')')
					->where('(a.publish_up = :nullDate1 OR a.publish_up <= :nowDate1)')
					->where('(a.publish_down = :nullDate2 OR a.publish_down >= :nowDate2)')
					->bind(':nullDate1', $nullDate)
					->bind(':nullDate2', $nullDate)
					->bind(':nowDate1', $now)
					->bind(':nowDate2', $now);

				// Filter by language
				if (Multilanguage::isEnabled())
				{
					$query->whereIn($db->quoteName('a.language'), [Factory::getLanguage()->getTag(), '*'], ParameterType::STRING);
				}

				$query->setLimit($maximum);

				$db->setQuery($query);

				try
				{
					$temp = $db->loadObjectList();
				}
				catch (\RuntimeException $e)
				{
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

					return [];
				}

				if (count($temp))
				{
					$articles_ids = [];

					foreach ($temp as $row)
					{
						$articles_ids[] = $row->id;
					}

					$articles->setState('filter.article_id', $articles_ids);
					$related = $articles->getItems();
				}

				unset($temp);
			}
		}

		if (count($related))
		{
			// Prepare data for display using display options
			foreach ($related as &$item)
			{
				$item->slug  = $item->id . ':' . $item->alias;
				$item->route = Route::_(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
		}

		return $related;
	}
}
