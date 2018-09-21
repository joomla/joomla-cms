<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the wrapper functions only once
JLoader::register('ModWrapperHelper', __DIR__ . '/helper.php');

$params = ModWrapperHelper::getParams($params);

$load            = $params->get('load');
$url             = htmlspecialchars($params->get('url'), ENT_COMPAT, 'UTF-8');
$target          = htmlspecialchars($params->get('target'), ENT_COMPAT, 'UTF-8');
$width           = htmlspecialchars($params->get('width'), ENT_COMPAT, 'UTF-8');
$height          = htmlspecialchars($params->get('height'), ENT_COMPAT, 'UTF-8');
$scroll          = htmlspecialchars($params->get('scrolling'), ENT_COMPAT, 'UTF-8');
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');
$frameborder     = htmlspecialchars($params->get('frameborder'), ENT_COMPAT, 'UTF-8');
$ititle          = $module->title;
$id              = $module->id;

require JModuleHelper::getLayoutPath('mod_wrapper', $params->get('layout', 'default'));
