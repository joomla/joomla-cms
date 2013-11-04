<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Application environment setup.
error_reporting(E_ALL);
const JDEBUG = false;
@ini_set('magic_quotes_runtime', 0);

// Check if a configuration file already exists.
if (file_exists(JAPPLICATION_CONFIG) && (filesize(JAPPLICATION_CONFIG) > 10))
{
	header('Location: ../index.php');
	exit();
}

// Define the base path and require the other defines

// Global definitions
$parts = explode(DIRECTORY_SEPARATOR, JPATH_BASE);
array_pop($parts);

// Defines
define('JPATH_ROOT', implode(DIRECTORY_SEPARATOR, $parts));
define('JPATH_LIBRARIES', JPATH_ROOT . '/libraries');
define('JPATH_THEMES', JPATH_BASE);

// Load the Composer autoloader.
require_once JPATH_ROOT . '/vendor/autoload.php';

/*
 * Notes:
 *
 * JPATH_THEMES is hard coupled to (and probably others)
 * /libraries/joomla/document/html/renderer/message.php
 * /libraries/cms/html/html.php
 */

// Import the Joomla Platform.
require_once JPATH_LIBRARIES . '/import.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Register the filesystem classes since they aren't placed for autoloading. (This is faster than jimport)
JLoader::register('JFile', JPATH_LIBRARIES . '/joomla/filesystem/file.php');
JLoader::register('JPath', JPATH_LIBRARIES . '/joomla/filesystem/path.php');
JLoader::register('JFolder', JPATH_LIBRARIES . '/joomla/filesystem/folder.php');

// Register our app namespace
JLoader::registerNamespace('Installation\\', __DIR__);

// Instantiate the application
$app = new Installation\Application\WebApplication;

// Set system paths.
$app->set('configurationPath', JAPPLICATION_CONFIG);
$app->set('administratorPath', JPATH_ROOT . '/administrator');
$app->set('installationPath', JPATH_ROOT . '/installation');
$app->set('themesPath', JPATH_BASE);
$app->set('sitePath', JPATH_ROOT);

// Execute
$app->execute();
