<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_image_slider
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$slideSet = ModImageSliderHelper::getSlides($params);

$id	= $module->id;

$interval = (int) $params->get('interval', 5000);
$autostart = $params->get('autostart', 1);
$navigation = $params->get('navigation', 1);
$controls = $params->get('controls', 1);

// Carousel interval also defines autostart
if (!$autostart) {
	$interval = 'false';
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_image_slider', $params->get('layout', 'default'));
