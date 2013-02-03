<?php
/**
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include dependancies.
require_once dirname(__FILE__).'/helper.php';

if ($list = modSubmenuHelper::getItems()) {
	require JModuleHelper::getLayoutPath('mod_submenu', $params->get('layout', 'default'));
}
