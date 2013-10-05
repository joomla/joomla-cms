<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  mod_popular
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include the mod_popular functions only once.
require_once __DIR__ . '/helper.php';

// Get module data.
$list = ModPopularHelper::getList($params);

// Render the module
require JModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
