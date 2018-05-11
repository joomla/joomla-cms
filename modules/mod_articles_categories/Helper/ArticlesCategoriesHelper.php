<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Module\ArticlesCategories\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Categories\Categories;

/**
 * Helper for mod_articles_categories
 *
 * @since  1.5
 */
abstract class ArticlesCategoriesHelper
{
	/**
	 * Get list of articles
	 *
	 * @param   \Joomla\Registry\Registry  &$params  module parameters
	 *
	 * @return  array
	 *
	 * @since   1.5
	 */
	public static function getList(&$params)
	{
		$options               = [];
		$options['countItems'] = $params->get('numitems', 0);

		$categories = Categories::getInstance('Content', $options);
		$category   = $categories->get($params->get('parent', 'root'));

		if ($category !== null)
		{
			$items = $category->getChildren();

			$count = $params->get('count', 0);

			if ($count > 0 && count($items) > $count)
			{
				$items = array_slice($items, 0, $count);
			}

			return $items;
		}
	}
}
