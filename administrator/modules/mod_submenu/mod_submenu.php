<?php
/**
 * @version		$Id:mod_menu.php 2463 2006-02-18 06:05:38Z webImagery $
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include dependancies.
require_once dirname(__FILE__).'/helper.php';

if ($list = modSubmenuHelper::getItems()) {
	require JModuleHelper::getLayoutPath('mod_submenu', $params->get('layout', 'default'));
}