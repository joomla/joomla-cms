<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$params = modWrapperHelper::getParams($params);

$load	= $params->get('load');
$url	= htmlspecialchars($params->get('url'));
$target = htmlspecialchars($params->get('target'));
$width	= htmlspecialchars($params->get('width'));
$height = htmlspecialchars($params->get('height'));
$scroll = htmlspecialchars($params->get('scrolling'));
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
$frameborder = htmlspecialchars($params->get('frameborder'));

require JModuleHelper::getLayoutPath('mod_wrapper', $params->get('layout', 'default'));
