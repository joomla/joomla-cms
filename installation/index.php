<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// This file must retain PHP 5.2 compatible syntax.

if (version_compare(PHP_VERSION, '5.3.1', '<'))
{
	die('Your host needs to use PHP 5.3.1 or higher to run this version of Joomla!');
}

// Application environment setup.
error_reporting(E_ALL);
const JDEBUG = false;
@ini_set('magic_quotes_runtime', 0);

// This is a valid Joomla! entry point.
define('_JEXEC', 1);
define('JAPPLICATION_CONFIG', dirname(__DIR__) . '/configuration.php');

// Check if a configuration file already exists.
if (file_exists(JAPPLICATION_CONFIG) && (filesize(JAPPLICATION_CONFIG) > 10))
{
	header('Location: ../index.php');
	exit();
}

// Define the base path and require the other defines
// TODO - Try and live without JPATH_BASE
define('JPATH_BASE', __DIR__);

require_once __DIR__ . '/src/defines.php';

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Register the filesystem classes since they aren't placed for autoloading. (This is faster than jimport)
JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');
JLoader::register('JPath', JPATH_LIBRARIES . '/joomla/filesystem/path.php');
JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

// Register our app namespace
JLoader::registerNamespace('Installation\\', JPATH_INSTALLATION . '/src');

// Instantiate the application
$app = new Installation\Application\WebApplication;
$app->set('configurationPath', JAPPLICATION_CONFIG);

// Execute
$app->execute();

