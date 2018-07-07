<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_logged
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependencies.
JLoader::register('ModLoggedHelper', __DIR__ . '/helper.php');

$users = ModLoggedHelper::getList($params);

if ($params->get('automatic_title', 0))
{
	$module->title = ModLoggedHelper::getTitle($params);
}

require JModuleHelper::getLayoutPath('mod_logged', $params->get('layout', 'default'));
