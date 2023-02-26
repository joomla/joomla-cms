<?php

/**
 * @package     Joomla.Site
 * @subpackage  mod_tags_similar
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

$cacheparams               = new \stdClass();
$cacheparams->cachemode    = 'safeuri';
$cacheparams->class        = 'Joomla\Module\TagsSimilar\Site\Helper\TagsSimilarHelper';
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = ['id' => 'array', 'Itemid' => 'int'];

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

require ModuleHelper::getLayoutPath('mod_tags_similar', $params->get('layout', 'default'));
