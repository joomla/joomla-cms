<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	mod_menu
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include the module helper classes.
if (!class_exists('ModMenuHelper')) {
	require dirname(__FILE__).'/helper.php';
}

if (!class_exists('JAdminCssMenu')) {
	require dirname(__FILE__).'/menu.php';
}

// Initialise variables.
$lang		= JFactory::getLanguage();
$user		= JFactory::getUser();
$menu		= new JAdminCSSMenu();
$enabled	= JRequest::getInt('hidemainmenu') ? false : true;

// Render the module layout
require JModuleHelper::getLayoutPath('mod_menu', $params->get('layout', 'default'));
