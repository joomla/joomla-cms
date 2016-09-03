<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the mod_popular functions only once.
JLoader::register('ModPopularHelper', __DIR__ . '/helper.php');

// Get module data.
$list = ModPopularHelper::getList($params);

if ($params->get('automatic_title', 0))
{
	$module->title = ModPopularHelper::getTitle($params);
}

// Render the module
require JModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
