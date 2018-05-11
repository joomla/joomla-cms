<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_related_items
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\Module\RelatedItems\Site\Helper\RelatedItemsHelper;

$cacheparams = new \stdClass;
$cacheparams->cachemode    = 'safeuri';
$cacheparams->class        = 'Joomla\Module\RelatedItems\Site\Helper\RelatedItemsHelper';
$cacheparams->method       = 'getList';
$cacheparams->methodparams = $params;
$cacheparams->modeparams   = array('id' => 'int', 'Itemid' => 'int');

$list = ModuleHelper::moduleCache($module, $params, $cacheparams);

if (!count($list))
{
	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
$showDate        = $params->get('showDate', 0);

require ModuleHelper::getLayoutPath('mod_related_items', $params->get('layout', 'default'));
