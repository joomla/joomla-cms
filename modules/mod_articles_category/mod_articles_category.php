<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_articles_category
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the helper functions only once
require_once dirname(__FILE__).DS.'helper.php';

$list = modArticlesCategoryHelper::getList($params);
if (!empty($list)) {
	$grouped = false;
	$article_grouping = $params->get('article_grouping', 'none');
	$article_grouping_direction = $params->get('article_grouping_direction', 'ksort');
	if ($article_grouping !== 'none') {
		$grouped = true;
		switch($article_grouping)
		{
			case 'year':
			case 'month_year':
				$list = modArticlesCategoryHelper::groupByDate($list, $article_grouping, $article_grouping_direction, $params->get('month_year_format', 'F Y'));
				break;
			case 'author_name':
			case 'category_title':
				$list = modArticlesCategoryHelper::groupBy($list, $article_grouping, $article_grouping_direction);
				break;
			default:
				break;
		}
	}
    require JModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default'));
}
