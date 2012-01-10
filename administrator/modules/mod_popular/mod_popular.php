<?php
/**
 * @version		$Id: mod_popular.php 22338 2011-11-04 17:24:53Z github_bot $
 * @package		Joomla.Administrator
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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
