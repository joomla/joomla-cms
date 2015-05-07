<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_single_image
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the syndicate functions only once
require_once __DIR__ . '/helper.php';

$link	= $params->get('link');

$image	= ModSingleImageHelper::getImage($params);

if ($image === false)
{
	echo JText::_('MOD_SINGLE_IMAGE_NO_IMAGE');

	return;
}

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
require JModuleHelper::getLayoutPath('mod_single_image', $params->get('layout', 'default'));
