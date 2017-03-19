<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/*
 * Joomla system checks.
 */

const JDEBUG = false;

/*
 * Check if a configuration file already exists.
 */

if (file_exists(JPATH_CONFIGURATION . '/configuration.php')
	&& (filesize(JPATH_CONFIGURATION . '/configuration.php') > 10)
	&& !file_exists(JPATH_INSTALLATION . '/index.php'))
{
	header('Location: ../index.php');
	exit();
}

/*
 * Joomla system startup.
 */

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/bootstrap.php';

// Import filesystem and utilities classes since they aren't autoloaded
jimport('joomla.filesystem.file');
jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.path');
