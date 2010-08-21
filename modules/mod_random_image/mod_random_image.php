<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_random_image
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
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
	echo JText::_('MOD_RANDOMIMAGE_NO_IMAGES');
	return;
}

$image = modRandomImageHelper::getRandomImage($params, $images);
require JModuleHelper::getLayoutPath('mod_random_image', $params->get('layout', 'default'));
