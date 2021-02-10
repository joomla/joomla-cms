<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_category
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\ArticlesCategory\Site\Helper\ArticlesCategoryHelper;

$input = $app->input;

// Prep for Normal or Dynamic Modes
$mode   = $params->get('mode', 'normal');
$idbase = null;

switch ($mode)
{
	case 'dynamic':
		$option = $input->get('option');
		$view   = $input->get('view');

		if ($option === 'com_content')
		{
			switch ($view)
			{
				case 'category':
				case 'categories':
					$idbase = $input->getInt('id');
					break;
				case 'article':
					if ($params->get('show_on_article_page', 1))
					{
						$idbase = $input->getInt('catid');
					}
					break;
			}
		}
		break;
	default:
		$idbase = $params->get('catid');
		break;
}

$cacheid = md5(serialize(array ($idbase, $module->module, $module->id)));

$cacheparams               = new \stdClass;
$cacheparams->cachemode    = 'id';
$cacheparams->class        = ArticlesCategoryHelper::class;
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = $cacheid;

$list                       = ModuleHelper::moduleCache($module, $params, $cacheparams);
$article_grouping           = $params->get('article_grouping', 'none');
$article_grouping_direction = $params->get('article_grouping_direction', 'ksort');
$grouped                    = $article_grouping !== 'none';

if ($list && $grouped)
{
	switch ($article_grouping)
	{
		case 'year':
		case 'month_year':
			$list = ArticlesCategoryHelper::groupByDate(
				$list,
				$article_grouping_direction,
				$article_grouping,
				$params->get('month_year_format', 'F Y'),
				$params->get('date_grouping_field', 'created')
			);
			break;
		case 'author':
		case 'category_title':
			$list = ArticlesCategoryHelper::groupBy($list, $article_grouping, $article_grouping_direction);
			break;
		case 'tags':
			$list = ArticlesCategoryHelper::groupByTags($list, $article_grouping_direction);
			break;
	}
}

require ModuleHelper::getLayoutPath('mod_articles_category', $params->get('layout', 'default'));
