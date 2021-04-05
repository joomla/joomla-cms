<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_articles_categories
 *
 * @copyright   (C) 2010 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$cacheid = md5($module->id);

$cacheparams               = new \stdClass;
$cacheparams->cachemode    = 'id';
$cacheparams->class        = 'Joomla\Module\ArticlesCategories\Site\Helper\ArticlesCategoriesHelper';
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = $cacheid;

$list       = ModuleHelper::moduleCache($module, $params, $cacheparams);
$startLevel = $list ? reset($list)->getParent()->level : null;

require ModuleHelper::getLayoutPath('mod_articles_categories', $params->get('layout', 'default'));

