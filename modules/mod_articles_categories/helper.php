<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_SITE.'/components/com_content/helpers/route.php';

/**
 * Helper for mod_articles_categories
 *
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 */
abstract class ModArticlesCategoriesHelper
{
	public static function getList(&$params)
	{
		$options = array();
		$options['countItems'] = $params->get('numitems', 0);

		$categories = JCategories::getInstance('Content', $options);
		$category = $categories->get($params->get('parent', 'root'));

		if ($category != null)
		{
			$items = $category->getChildren();
			if ($params->get('count', 0) > 0 && count($items) > $params->get('count', 0))
			{
				$items = array_slice($items, 0, $params->get('count', 0));
			}
			return $items;
		}
	}

}
