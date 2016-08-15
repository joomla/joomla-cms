<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', __DIR__);
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('JLoader'))
{
	throw new RuntimeException('Joomla Platform not loaded.');
}

// Register the library base path for CMS libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

// Create the Composer autoloader
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';
$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register(array(new JClassLoader($loader), 'loadClass'), true, true);

// Register the class aliases for Framework classes that have replaced their Platform equivilents
require_once JPATH_LIBRARIES . '/classmap.php';

// Ensure FOF autoloader included - needed for things like content versioning where we need to get an FOFTable Instance
if (!class_exists('FOFAutoloaderFof'))
{
	include_once JPATH_LIBRARIES . '/fof/include.php';
}

// Register a handler for uncaught exceptions that shows a pretty error page when possible
set_exception_handler(array('JErrorPage', 'render'));

// Define the Joomla version if not already defined.
if (!defined('JVERSION'))
{
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(array('logger' => 'messagequeue'), JLog::ALL, array('jerror'));
}

// Register JArrayHelper due to JRegistry moved to composer's vendor folder
JLoader::register('JArrayHelper', JPATH_PLATFORM . '/joomla/utilities/arrayhelper.php');

// Register the Crypto lib
JLoader::register('Crypto', JPATH_PLATFORM . '/php-encryption/Crypto.php');

// Register classes where the names have been changed to fit the autoloader rules
// @deprecated  4.0
JLoader::register('JToolBar', JPATH_PLATFORM . '/cms/toolbar/toolbar.php');
JLoader::register('JButton',  JPATH_PLATFORM . '/cms/toolbar/button.php');
JLoader::register('JInstallerComponent',  JPATH_PLATFORM . '/cms/installer/adapter/component.php');
JLoader::register('JInstallerFile',  JPATH_PLATFORM . '/cms/installer/adapter/file.php');
JLoader::register('JInstallerLanguage',  JPATH_PLATFORM . '/cms/installer/adapter/language.php');
JLoader::register('JInstallerLibrary',  JPATH_PLATFORM . '/cms/installer/adapter/library.php');
JLoader::register('JInstallerModule',  JPATH_PLATFORM . '/cms/installer/adapter/module.php');
JLoader::register('JInstallerPackage',  JPATH_PLATFORM . '/cms/installer/adapter/package.php');
JLoader::register('JInstallerPlugin',  JPATH_PLATFORM . '/cms/installer/adapter/plugin.php');
JLoader::register('JInstallerTemplate',  JPATH_PLATFORM . '/cms/installer/adapter/template.php');
JLoader::register('JExtension',  JPATH_PLATFORM . '/cms/installer/extension.php');
JLoader::registerAlias('JAdministrator',  'JApplicationAdministrator');
JLoader::registerAlias('JSite',  'JApplicationSite');
