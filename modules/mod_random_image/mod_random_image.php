<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helper.php';

$link	= $params->get('link');

$folder	= modRandomImageHelper::getFolder($params);
$images	= modRandomImageHelper::getImages($params, $folder);

if (!count($images)) {
	echo JText::_('MOD_RANDOM_IMAGE_NO_IMAGES');
	return;
}

$image = modRandomImageHelper::getRandomImage($params, $images);
$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_random_image', $params->get('layout', 'default'));
