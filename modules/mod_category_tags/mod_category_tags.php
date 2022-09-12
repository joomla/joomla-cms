<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$cacheparams = new \stdClass;
$cacheparams->cachemode = 'safeuri';
$cacheparams->class = 'Joomla\Module\CategoryTags\Site\Helper\CategoryTagsHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = array('id' => 'array', 'Itemid' => 'int');

$params['cache'] = 0;

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!count($list) && !$params->get('no_results_display'))
{
	return;
}

foreach ($list as &$tag)
{
	$tag->childs = [];
}
if($params->get('tree_display')):

	$tag_ids = array_column($list, 'tag_id');
	$cat_ids = array_column($list, 'cat_id');
	$parents = [];

	foreach ($list as &$tag)
	{
		// Ключи родителей
		$parent_keys = array_keys($tag_ids, $tag->parent_id);
		if($parent_keys){
			// Категории родителей
			$c_ids = array_intersect_key($cat_ids, array_flip($parent_keys));
			if(in_array($tag->cat_id, $c_ids)){
				$cat_id = $tag->cat_id;
			}else{
				$cat_id = reset($c_ids);
			}
			$cat_key = array_search($cat_id, $c_ids);
			$tag->parent = &$list[$cat_key];
			$list[$cat_key]->childs[] = &$tag;
		}else{
			$parents[] = $tag;
		}
	}
	$list = $parents;
endif;

$count_display = $params->get('count_display', 0);
$categories_titles = $params->get('categories_titles', 0);

$image_display = $params->get('image_display', 0);
$title_display = $params->get('title_display', 1);

require ModuleHelper::getLayoutPath('mod_category_tags', $params->get('layout', 'default'));

