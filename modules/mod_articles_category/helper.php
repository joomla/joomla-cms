<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\String\StringHelper;

$comPath = JPATH_SITE . '/components/com_content/';
JLoader::register('ContentHelperRoute', $comPath . 'helpers/route.php');
JModelLegacy::addIncludePath($comPath . 'models', 'ContentModel');

/**
 * Helper for mod_articles_category
 *
 * @since  1.6
 */
abstract class ModArticlesCategoryHelper
{
	/**
	 * Get a list of articles from a specific category
	 *
	 * @param   \Joomla\Registry\Registry  &$params  object holding the models parameters
	 *
	 * @return  array
	 *
	 * @since  1.6
	 */
	public static function getList(&$params)
	{
		// Get an instance of the generic articles model
		$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));

		// Set application parameters in model
		$app       = JFactory::getApplication();
		$appParams = $app->getParams();
		$articles->setState('params', $appParams);

		// Set the filters based on the module params
		$articles->setState('list.start', 0);
		$articles->setState('list.limit', (int) $params->get('count', 0));
		$articles->setState('filter.published', 1);

		// This module does not use tags data
		$articles->setState('load_tags', $params->get('show_tags', 0) || $params->get('article_grouping', 'none') === 'tags' ? true : false);

		// Access filter
		$access     = !JComponentHelper::getParams('com_content')->get('show_noauth');
		$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
		$articles->setState('filter.access', $access);

		// Prep for Normal or Dynamic Modes
		$mode = $params->get('mode', 'normal');

		switch ($mode)
		{
			case 'dynamic' :
				$option = $app->input->getCmd('option');
				$view   = $app->input->getCmd('view');

				if ($option === 'com_content')
				{
					switch ($view)
					{
						case 'category' :
							$catIds = array($app->input->getInt('id'));
							break;
						case 'categories' :
							$catIds = array($app->input->getInt('id'));
							break;
						case 'article' :
							if ($params->get('show_on_article_page', 1))
							{
								$articleId = $app->input->getInt('id');
								$catId     = $app->input->getInt('catid');

								if (!$catId)
								{
									// Get an instance of the generic article model
									$article = JModelLegacy::getInstance('Article', 'ContentModel', array('ignore_request' => true));

									$article->setState('params', $appParams);
									$article->setState('filter.published', 1);
									$article->setState('article.id', (int) $articleId);
									$item   = $article->getItem();
									$catIds = array($item->catid);
								}
								else
								{
									$catIds = array($catId);
								}
							}
							else
							{
								// Return right away if show_on_article_page option is off
								return array();
							}
							break;

						case 'featured' :
						default:
							// Return right away if not on the category or article views
							return array();
					}
				}
				else
				{
					// Return right away if not on a com_content page
					return array();
				}

				break;

			case 'normal' :
			default:
				$catIds = $params->get('catid');
				$articles->setState('filter.category_id.include', (bool) $params->get('category_filtering_type', 1));
				break;
		}

		// Category filter
		if ($catIds)
		{
			if ($params->get('show_child_category_articles', 0) && (int) $params->get('levels', 1) > 0)
			{
				// Get an instance of the generic categories model
				$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
				$categories->setState('params', $appParams);
				$levels = $params->get('levels', 1) ?: 9999;
				$categories->setState('filter.get_children', $levels);
				$categories->setState('filter.published', 1);
				$categories->setState('filter.access', $access);
				$additionalCatIds = array();

				foreach ($catIds as $catId)
				{
					$categories->setState('filter.parentId', $catId);
					$recursive = true;
					$items     = $categories->getItems($recursive);

					if ($items)
					{
						foreach ($items as $category)
						{
							$condition = (($category->level - $categories->getParent()->level) <= $levels);

							if ($condition)
							{
								$additionalCatIds[] = $category->id;
							}
						}
					}
				}

				$catIds = array_unique(array_merge($catIds, $additionalCatIds));
			}

			$articles->setState('filter.category_id', $catIds);
		}

		// Ordering
		$ordering = $params->get('article_ordering', 'a.ordering');

		switch ($ordering)
		{
			case 'random':
				$articles->setState('list.ordering', JFactory::getDbo()->getQuery(true)->Rand());
				break;

			case 'rating_count':
			case 'rating':
				$articles->setState('list.ordering', $ordering);
				$articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));

				if (!JPluginHelper::isEnabled('content', 'vote'))
				{
					$articles->setState('list.ordering', 'a.ordering');
				}

				break;

			default:
				$articles->setState('list.ordering', $ordering);
				$articles->setState('list.direction', $params->get('article_ordering_direction', 'ASC'));
				break;
		}

		// Filter by multiple tags
		$articles->setState('filter.tag', $params->get('filter_tag', array()));

		$articles->setState('filter.featured', $params->get('show_front', 'show'));
		$articles->setState('filter.author_id', $params->get('created_by', array()));
		$articles->setState('filter.author_id.include', $params->get('author_filtering_type', 1));
		$articles->setState('filter.author_alias', $params->get('created_by_alias', array()));
		$articles->setState('filter.author_alias.include', $params->get('author_alias_filtering_type', 1));
		$excludedArticles = $params->get('excluded_articles', '');

		if ($excludedArticles)
		{
			$excludedArticles = explode("\r\n", $excludedArticles);
			$articles->setState('filter.article_id', $excludedArticles);

			// Exclude
			$articles->setState('filter.article_id.include', false);
		}

		$dateFiltering = $params->get('date_filtering', 'off');

		if ($dateFiltering !== 'off')
		{
			$articles->setState('filter.date_filtering', $dateFiltering);
			$articles->setState('filter.date_field', $params->get('date_field', 'a.created'));
			$articles->setState('filter.start_date_range', $params->get('start_date_range', '1000-01-01 00:00:00'));
			$articles->setState('filter.end_date_range', $params->get('end_date_range', '9999-12-31 23:59:59'));
			$articles->setState('filter.relative_date', $params->get('relative_date', 30));
		}

		// Filter by language
		$articles->setState('filter.language', $app->getLanguageFilter());

		$items = $articles->getItems();

		// Display options
		$showDate       = $params->get('show_date', 0);
		$showDateField  = $params->get('show_date_field', 'created');
		$showDateFormat = $params->get('show_date_format', 'Y-m-d H:i:s');
		$showCategory   = $params->get('show_category', 0);
		$showHits       = $params->get('show_hits', 0);
		$showAuthor     = $params->get('show_author', 0);
		$showIntrotext  = $params->get('show_introtext', 0);
		$introtextLimit = $params->get('introtext_limit', 100);

		// Find current Article ID if on an article page
		$option = $app->input->getCmd('option');
		$view   = $app->input->getCmd('view');

		if ($option === 'com_content' && $view === 'article')
		{
			$activeArticleId = $app->input->getInt('id');
		}
		else
		{
			$activeArticleId = 0;
		}

		// Prepare data for display using display options
		foreach ($items as &$item)
		{
			$item->slug = $item->id . ':' . $item->alias;

			/** @deprecated Catslug is deprecated, use catid instead. 4.0 **/
			$item->catslug = $item->catid . ':' . $item->category_alias;

			if ($access || in_array($item->access, $authorised))
			{
				// We know that user has the privilege to view the article
				$item->link = JRoute::_(ContentHelperRoute::getArticleRoute($item->slug, $item->catid, $item->language));
			}
			else
			{
				$menu      = $app->getMenu();
				$menuItems = $menu->getItems('link', 'index.php?option=com_users&view=login');

				if (isset($menuItems[0]))
				{
					$itemId = $menuItems[0]->id;
				}
				elseif ($app->input->getInt('Itemid') > 0)
				{
					// Use Itemid from requesting page only if there is no existing menu
					$itemId = $app->input->getInt('Itemid');
				}

				$item->link = JRoute::_('index.php?option=com_users&view=login&Itemid=' . $itemId);
			}

			// Used for styling the active article
			$item->active = $item->id === $activeArticleId ? ' active' : '';
	
			$item->displayDate = $showDate ? JHtml::_('date', $item->$showDateField, $showDateFormat) : '';

			if ($item->catid)
			{
				$item->displayCategoryLink  = JRoute::_(ContentHelperRoute::getCategoryRoute($item->catid));
				$item->displayCategoryTitle = $showCategory ? '<a href="' . $item->displayCategoryLink . '">' . $item->category_title . '</a>' : '';
			}
			else
			{
				$item->displayCategoryTitle = $showCategory ? $item->category_title : '';
			}

			$item->displayHits       = $showHits ? $item->hits : '';
			$item->displayAuthorName = $showAuthor ? $item->author : '';

			if ($showIntrotext)
			{
				$item->introtext = JHtml::_('content.prepare', $item->introtext, '', 'mod_articles_category.content');
				$item->introtext = self::_cleanIntrotext($item->introtext);
			}

			$item->displayIntrotext = $showIntrotext ? self::truncate($item->introtext, $introtextLimit) : '';
			$item->displayReadmore  = $item->alternative_readmore;
		}

		return $items;
	}

	/**
	 * Strips unnecessary tags from the introtext
	 *
	 * @param   string  $introtext  introtext to sanitize
	 *
	 * @return mixed|string
	 *
	 * @since  1.6
	 */
	public static function _cleanIntrotext($introtext)
	{
		$introtext = str_replace(array('<p>','</p>'), ' ', $introtext);
		$introtext = strip_tags($introtext, '<a><em><strong>');
		$introtext = trim($introtext);

		return $introtext;
	}

	/**
	 * Method to truncate introtext
	 *
	 * The goal is to get the proper length plain text string with as much of
	 * the html intact as possible with all tags properly closed.
	 *
	 * @param   string   $html       The content of the introtext to be truncated
	 * @param   integer  $maxLength  The maximum number of charactes to render
	 *
	 * @return  string  The truncated string
	 *
	 * @since   1.6
	 */
	public static function truncate($html, $maxLength = 0)
	{
		$baseLength = strlen($html);

		// First get the plain text string. This is the rendered text we want to end up with.
		$ptString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = false);

		for ($maxLength; $maxLength < $baseLength;)
		{
			// Now get the string if we allow html.
			$htmlString = JHtml::_('string.truncate', $html, $maxLength, $noSplit = true, $allowHtml = true);

			// Now get the plain text from the html string.
			$htmlStringToPtString = JHtml::_('string.truncate', $htmlString, $maxLength, $noSplit = true, $allowHtml = false);

			// If the new plain text string matches the original plain text string we are done.
			if ($ptString === $htmlStringToPtString)
			{
				return $htmlString;
			}

			// Get the number of html tag characters in the first $maxlength characters
			$diffLength = strlen($ptString) - strlen($htmlStringToPtString);

			// Set new $maxlength that adjusts for the html tags
			$maxLength += $diffLength;

			if ($baseLength <= $maxLength || $diffLength <= 0)
			{
				return $htmlString;
			}
		}

		return $html;
	}

	/**
	 * Groups items by field
	 *
	 * @param   array   $list       list of items
	 * @param   string  $fieldName  name of field that is used for grouping
	 * @param   string  $direction  ordering direction
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupBy($list, $fieldName, $direction)
	{
		$grouped  = array();
		$noAuthor = JText::_('MOD_ARTICLES_CATEGORY_NO_AUTHOR');

		foreach ($list as $key => $item)
		{
			if ($item->$fieldName !== null)
			{
				$grouped[$item->$fieldName][$key] = $item;
			}
			elseif ($fieldName === 'author')
			{
				$grouped[$noAuthor][$key] = $item;
			}

			unset($list[$key]);
		}

		$direction($grouped);

		return $grouped;
	}

	/**
	 * Groups items by date
	 *
	 * @param   array   $list             list of items
	 * @param   string  $type             type of grouping
	 * @param   string  $direction        ordering direction
	 * @param   string  $monthYearFormat  date format to use
	 * @param   string  $dateField        date field to group by
	 *
	 * @return  array
	 *
	 * @since   1.6
	 */
	public static function groupByDate($list, $type = 'year', $direction, $monthYearFormat = 'F Y', $dateField = 'created')
	{
		$grouped = array();

		foreach ($list as $key => $item)
		{
			switch ($type)
			{
				case 'month_year' :
					$monthYear = StringHelper::substr($item->$dateField, 0, 7);
					$grouped[$monthYear][$key] = $item;
					break;

				case 'year' :
				default:
					$year = StringHelper::substr($item->$dateField, 0, 4);

					if (!isset($grouped[$year]))
					{
						$grouped[$year] = array();
					}

					$grouped[$year][$key] = $item;
					break;
			}

			unset($list[$key]);
		}

		$direction($grouped);

		if ($type === 'month_year')
		{
			foreach ($grouped as $group => $items)
			{
				$date                     = new JDate($group);
				$formattedGroup           = $date->format($monthYearFormat);
				$grouped[$formattedGroup] = $items;

				unset($grouped[$group]);
			}
		}

		return $grouped;
	}

	/**
	 * Groups items by tags
	 *
	 * @param   array   $list       list of items
	 * @param   string  $direction  ordering direction
	 *
	 * @return  array
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function groupByTags($list, $direction)
	{
		$grouped = array();
		$noTag   = JText::_('MOD_ARTICLES_CATEGORY_NO_TAG');

		foreach ($list as $key => $item)
		{
			if (!empty($item->tags->itemTags))
			{
				foreach ($item->tags->itemTags as $tag)
				{
					$grouped[$tag->title][$key] = $item;
				}
				unset($list[$key]);
			}
			else
			{
				$grouped[$noTag][$key] = $item;
			}
		}

		$direction($grouped);

		return $grouped;
	}
}
