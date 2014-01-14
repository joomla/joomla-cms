<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('_JEXEC') or die;

// Get the login modules
// If you want to use a completely different login module change the value of name
// in your layout override.

$loginmodule =LoginModelLogin::getLoginModule('mod_login');
	echo JModuleHelper::renderModule($loginmodule, array('style' => 'rounded', 'id' => 'section-box'));


//Get any other modules in the login position.
//If you want to use a different position for the modules, change the name here in your override.
$modules = JModuleHelper::getModules('login');

foreach ($modules as $module)
// Render the login modules

if ($module->module != 'mod_login'){
	echo JModuleHelper::renderModule($module, array('style' => 'rounded', 'id' => 'section-box'));
}
