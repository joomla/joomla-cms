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
use Joomla\CMS\Router\Route;
use Joomla\Component\Content\Administrator\Extension\ContentComponent;

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
		$factory = $app->bootComponent('com_content')->getMVCFactory();

		// Get an instance of the generic articles model
		/** @var \Joomla\Component\Content\Site\Model\ArticlesModel $articles */
		$articles = $factory->createModel('Articles', 'Site', ['ignore_request' => true]);

		// Set application parameters in model
		$articles->setState('params', $app->getParams());

		$option = $input->get('option');
		$view   = $input->get('view');

		if (!($option === 'com_content' && $view === 'article'))
		{
			return array();
		}

		$temp = $input->getString('id');
		$temp = explode(':', $temp);
		$id   = $temp[0];

		$nullDate = $db->getNullDate();
		$now      = Factory::getDate()->toSql();
		$related  = [];
		$query    = $db->getQuery(true);

		if ($id)
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
					->select('a.id')
					->from('#__content AS a')
					->where('a.id != ' . (int) $id)
					->where('ws.condition = ' . ContentComponent::CONDITION_PUBLISHED)
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
					$articleIds = $db->loadColumn();
				}
				catch (\RuntimeException $e)
				{
					$app->enqueueMessage(Text::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

					return array();
				}

				if (count($articleIds))
				{
					$articles->setState('filter.article_id', $articleIds);
					$articles->setState('filter.published', 1);
					$related = $articles->getItems();
				}

				unset($articleIds);
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
