<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   (C) 2006 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContentHelperRoute', JPATH_SITE . '/components/com_content/helpers/route.php');

/**
 * Helper for mod_related_items
 *
 * @since  1.5
 */
abstract class ModRelatedItemsHelper
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
		$db      = JFactory::getDbo();
		$app     = JFactory::getApplication();
		$user    = JFactory::getUser();
		$groups  = implode(',', $user->getAuthorisedViewLevels());
		$date    = JFactory::getDate();
		$maximum = (int) $params->get('maximum', 5);

		// Get an instance of the generic articles model
		JModelLegacy::addIncludePath(JPATH_SITE . '/components/com_content/models');
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		if ($articles === false)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

			return array();
		}

		// Set application parameters in model
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		$option = $app->input->get('option');
		$view   = $app->input->get('view');

		if (!($option === 'com_content' && $view === 'article'))
		{
			return array();
		}

		$temp = $app->input->getString('id');
		$temp = explode(':', $temp);
		$id   = $temp[0];

		$nullDate = $db->getNullDate();
		$now      = $date->toSql();
		$related  = array();
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
			catch (RuntimeException $e)
			{
				JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

				return array();
			}

			// Explode the meta keys on a comma
			$keys  = explode(',', $metakey);
			$likes = array();

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
					->where('a.state = 1')
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
				if (JLanguageMultilang::isEnabled())
				{
					$query->where('a.language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
				}

				$db->setQuery($query, 0, $maximum);

				try
				{
					$articleIds = $db->loadColumn();
				}
				catch (RuntimeException $e)
				{
					JFactory::getApplication()->enqueueMessage(JText::_('JERROR_AN_ERROR_HAS_OCCURRED'), 'error');

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
				$item->slug    = $item->id . ':' . $item->alias;

				/** @deprecated Catslug is deprecated, use catid instead. 4.0 */
				$item->catslug = $item->catid . ':' . $item->category_alias;

				$item->route   = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
		}

		return $related;
	}
}
