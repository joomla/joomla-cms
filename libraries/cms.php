<?php
/**
 * @package     Joomla.Libraries
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
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

// Register location of form fields and rules
JForm::addFieldPath(JPATH_PLATFORM . '/cms/form/field');
JForm::addRulePath(JPATH_PLATFORM . '/cms/form/rule');
