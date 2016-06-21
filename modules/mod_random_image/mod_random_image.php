<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_random_image
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the random image functions only once
JLoader::register('ModRandomImageHelper', __DIR__ . '/helper.php');

$link   = $params->get('link');
$folder = ModRandomImageHelper::getFolder($params);
$images = ModRandomImageHelper::getImages($params, $folder);

if (!count($images))
{
	echo JText::_('MOD_RANDOM_IMAGE_NO_IMAGES');

	return;
}

$image           = ModRandomImageHelper::getRandomImage($params, $images);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'), ENT_COMPAT, 'UTF-8');

require JModuleHelper::getLayoutPath('mod_random_image', $params->get('layout', 'default'));
