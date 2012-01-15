<?php
/**
 * @package     Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM')) {
	define('JPATH_PLATFORM', dirname(__FILE__));
}

// Import the cms loader if necessary.
if (!class_exists('JCmsLoader')) {
	require_once JPATH_PLATFORM.'/cms/cmsloader.php';
}

// Setup the autoloader.
JCmsLoader::setup();

// Define the Joomla version if not already defined.
if (!defined('JVERSION')) {
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}
// Register the location of renamed classes so they can be autoloaded
// The old name are considered deprecated and this should be removed in 3.0
JLoader::register('JRule', JPATH_PLATFORM . '/joomla/access/rule.php');
JLoader::register('JRules', JPATH_PLATFORM . '/joomla/access/rules.php');
