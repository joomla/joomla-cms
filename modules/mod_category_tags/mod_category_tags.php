<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_category_tags
 *
 * @copyright   (C) 2024 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

$Itemid = $params->get('Itemid');

if ($Itemid) {
    $itemMenu = Factory::getApplication()->getMenu()->getItem($Itemid);
    $params->set('catid', $itemMenu->query['id'] ?? []);
}

if (empty($params->get('catid')) && empty($params->get('no_results_display'))) {
    return;
}

$cacheparams = new \stdClass();
$cacheparams->cachemode = 'safeuri';
$cacheparams->class = 'Joomla\Module\CategoryTags\Site\Helper\CategoryTagsHelper';
$cacheparams->method = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams = array('id' => 'array', 'Itemid' => 'int');

$params['cache'] = 0;

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (empty($list) && !$params->get('no_results_display')) {
    return;
}

foreach ($list as &$tag) {
    $tag->items = [];
}

if ($params->get('tree_display')) :
    $tag_ids = array_column($list, 'tag_id');
    $cat_ids = array_column($list, 'cat_id');
    $parents = [];

    foreach ($list as &$tag) {
        // Keys parents
        $parent_keys = array_keys($tag_ids, $tag->parent_id);

        if ($parent_keys) {
            // Categories parents
            $c_ids = array_intersect_key($cat_ids, array_flip($parent_keys));

            $cat_key = array_search($tag->cat_id, $c_ids);

            if ($cat_key !== false) {
                $cat_id = $tag->cat_id;
                $tag->parent = &$list[$cat_key];
                $list[$cat_key]->items[] = &$tag;
            } else {
                $parents[] = $tag;
            }
        } else {
            $parents[] = $tag;
        }
    }

    $list = $parents;
endif;

$tree_display = $params->get('tree_display', 1);
$title_display = $params->get('title_display', 1);
$image_display = $params->get('image_display', 0);
$count_display = $params->get('count_display', 0);
$categories_titles = $params->get('categories_titles', 0);


require ModuleHelper::getLayoutPath('mod_category_tags', $params->get('layout', 'default'));
