<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\ArticlesCategory\Site\Helper\ArticlesCategoryHelper;
use Joomla\CMS\Factory;

$input = Factory::getApplication()->input;

// Prep for Normal or Dynamic Modes
$mode   = $params->get('mode', 'normal');
$idbase = null;

switch ($mode)
{
	case 'dynamic' :
		$option = $input->get('option');
		$view   = $input->get('view');

		if ($option === 'com_content')
		{
			switch ($view)
			{
				case 'category' :
					$idbase = $input->getInt('id');
					break;
				case 'categories' :
					$idbase = $input->getInt('id');
					break;
				case 'article' :
					if ($params->get('show_on_article_page', 1))
					{
						$idbase = $input->getInt('catid');
					}
					break;
			}
		}
		break;
	case 'normal' :
	default:
		$idbase = $params->get('catid');
		break;
}

$cacheid = md5(serialize(array ($idbase, $module->module, $module->id)));

$cacheparams               = new stdClass;
$cacheparams->cachemode    = 'id';
$cacheparams->class        = 'Joomla\Module\ArticlesCategory\Site\Helper\ArticlesCategoryHelper';
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = $cacheid;

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!empty($list))
{
	$grouped                    = false;
	$article_grouping           = $params->get('article_grouping', 'none');
	$article_grouping_direction = $params->get('article_grouping_direction', 'ksort');
	$item_heading               = $params->get('item_heading');

	if ($article_grouping !== 'none')
	{
		$grouped = true;

		switch ($article_grouping)
		{
			case 'year' :
			case 'month_year' :
				$list = ArticlesCategoryHelper::groupByDate($list, $article_grouping_direction, $article_grouping, $params->get('month_year_format', 'F Y'));
				break;
			case 'author' :
			case 'category_title' :
				$list = ArticlesCategoryHelper::groupBy($list, $article_grouping, $article_grouping_direction);
				break;
			default:
				break;
		}
	}

	require ModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default'));
}
