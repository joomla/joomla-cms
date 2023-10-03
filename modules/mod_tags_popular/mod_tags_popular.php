<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_popular
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\TagsPopular\Site\Helper\TagsPopularHelper;

$cacheparams               = new \stdClass();
$cacheparams->cachemode    = 'safeuri';
$cacheparams->class        = TagsPopularHelper::class;
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = ['id' => 'array', 'Itemid' => 'int'];

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!\count($list) && !$params->get('no_results_text')) {
    return;
}

$display_count = $params->get('display_count', 0);

require ModuleHelper::getLayoutPath('mod_tags_popular', $params->get('layout', 'default'));
