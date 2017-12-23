<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\RelatedItems\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Language\Multilanguage;

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
		$db      = Factory::getDbo();
		$app     = Factory::getApplication();
		$input   = $app->input;
		$groups  = implode(',', Factory::getUser()->getAuthorisedViewLevels());
		$maximum = (int) $params->get('maximum', 5);

		// Get an instance of the generic articles model
		BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_content/Model');
		$articles = BaseDatabaseModel::getInstance('ArticlesModel', 'Joomla\\Component\\Content\\Site\\Model\\', array('ignore_request' => true));

		if ($articles === false)
		{
			$app->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return array();
		}

		// Set application parameters in model
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		$option = $input->get('option');
		$view   = $input->get('view');

		$temp = $input->getString('id');
		$temp = explode(':', $temp);
		$id   = $temp[0];

		$nullDate = $db->getNullDate();
		$now      = \JFactory::getDate()->toSql();
		$related  = [];
		$query    = $db->getQuery(true);

		if ($option === 'com_content' && $view === 'article' && $id)
		{
			// Select the meta keywords from the item
			$query->select('metakey')
				->from('#__content')
				->where('id = ' . (int) $id);
			$db->setQuery($query);

			try
			{
				$metakey = trim($db->loadResult());
			}
			catch (\RuntimeException $e)
			{
				$app->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

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
					->select('a.id')
					->select('a.title')
					->select('CAST(a.created AS DATE) as created')
					->select('a.catid')
					->select('a.language')
					->select('cc.access AS cat_access')
					->select('cc.published AS cat_state');

				$case_when = ' CASE WHEN ';
				$case_when .= $query->charLength('a.alias', '!=', '0');
				$case_when .= ' THEN ';
				$a_id = $query->castAsChar('a.id');
				$case_when .= $query->concatenate(array($a_id, 'a.alias'), ':');
				$case_when .= ' ELSE ';
				$case_when .= $a_id . ' END as slug';

				$query->select($case_when)
					->from('#__content AS a')
					->join('LEFT', '#__content_frontpage AS f ON f.content_id = a.id')
					->join('LEFT', '#__categories AS cc ON cc.id = a.catid')
					->join('LEFT', '#__workflow_states AS ws ON ws.id = a.state')
					->where('a.id != ' . (int) $id)
					->where('ws.condition = 1')
					->where('a.access IN (' . $groups . ')');

				$wheres = array();

				foreach ($likes as $keyword)
				{
					$wheres[] = 'a.metakey LIKE ' . $db->quote('%' . $keyword . '%');
				}

				$query->where('(' . implode(' OR ', $wheres) . ')')
					->where('(a.publish_up = ' . $db->quote($nullDate) . ' OR a.publish_up <= ' . $db->quote($now) . ')')
					->where('(a.publish_down = ' . $db->quote($nullDate) . ' OR a.publish_down >= ' . $db->quote($now) . ')');

				// Filter by language
				if (Multilanguage::isEnabled())
				{
					$query->where('a.language in (' . $db->quote(Factory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				$db->setQuery($query, 0, $maximum);

				try
				{
					$temp = $db->loadObjectList();
				}
				catch (\RuntimeException $e)
				{
					$app->enqueueMessage(\JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

					return array();
				}

				if (count($temp))
				{
					$articles_ids = [];

					foreach ($temp as $row)
					{
						$articles_ids[] = $row->id;
					}

					$articles->setState('filter.article_id', $articles_ids);
					$articles->setState('filter.published', 1);
					$related = $articles->getItems();
				}

				unset ($temp);
			}
		}

		if (count($related))
		{
			// Prepare data for display using display options
			foreach ($related as &$item)
			{
				$item->slug  = $item->id . ':' . $item->alias;
				$item->route = \JRoute::_(\ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
		}

		return $related;
	}
}
