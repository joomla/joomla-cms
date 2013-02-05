<?php
/**
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Include the mod_popular functions only once.
require_once dirname(__FILE__).'/helper.php';

// Get module data.
$list = modPopularHelper::getList($params);

// Render the module
require JModuleHelper::getLayoutPath('mod_popular', $params->get('layout', 'default'));
