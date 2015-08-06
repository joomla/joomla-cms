<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$link	= $params->get('link');

$folder	= ModRandomImageHelper::getFolder($params);
$images	= ModRandomImageHelper::getImages($params, $folder);

if (!count($images))
{
	echo JText::_('MOD_RANDOM_IMAGE_NO_IMAGES');

	return;
}

$image = ModRandomImageHelper::getRandomImage($params, $images);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_random_image', $params->get('layout', 'default'));
