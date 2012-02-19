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
