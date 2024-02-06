<?php

/**
 * Prepares a minimalist framework for unit testing.
 *
 * @package    Joomla.UnitTest
 *
 * @copyright  (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

// phpcs:disable PSR1.Files.SideEffects

define('_JEXEC', 1);

// Maximise error reporting.
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set fixed precision value to avoid round related issues
ini_set('precision', 14);

/*
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */
$rootDirectory = getcwd();

if (!defined('JPATH_BASE')) {
    define('JPATH_BASE', $rootDirectory);
}

if (!defined('JPATH_ROOT')) {
    define('JPATH_ROOT', JPATH_BASE);
}

/**
 * @deprecated 4.4.0 will be removed in 6.0
 **/
if (!defined('JPATH_PLATFORM')) {
    define('JPATH_PLATFORM', JPATH_BASE . DIRECTORY_SEPARATOR . 'libraries');
}

if (!defined('JPATH_LIBRARIES')) {
    define('JPATH_LIBRARIES', JPATH_BASE . DIRECTORY_SEPARATOR . 'libraries');
}

if (!defined('JPATH_CONFIGURATION')) {
    define('JPATH_CONFIGURATION', JPATH_BASE);
}

if (!defined('JPATH_SITE')) {
    define('JPATH_SITE', JPATH_ROOT);
}

if (!defined('JPATH_ADMINISTRATOR')) {
    define('JPATH_ADMINISTRATOR', JPATH_ROOT . DIRECTORY_SEPARATOR . 'administrator');
}

if (!defined('JPATH_CACHE')) {
    define('JPATH_CACHE', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'cache');
}

if (!defined('JPATH_API')) {
    define('JPATH_API', JPATH_ROOT . DIRECTORY_SEPARATOR . 'api');
}

if (!defined('JPATH_INSTALLATION')) {
    define('JPATH_INSTALLATION', JPATH_ROOT . DIRECTORY_SEPARATOR . 'installation');
}

if (!defined('JPATH_MANIFESTS')) {
    define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . DIRECTORY_SEPARATOR . 'manifests');
}

if (!defined('JPATH_PLUGINS')) {
    define('JPATH_PLUGINS', JPATH_BASE . DIRECTORY_SEPARATOR . 'plugins');
}

if (!defined('JPATH_THEMES')) {
    define('JPATH_THEMES', JPATH_BASE . DIRECTORY_SEPARATOR . 'templates');
}

if (!defined('JDEBUG')) {
    define('JDEBUG', false);
}

// Import the library loader if necessary.
if (!class_exists('JLoader')) {
    require_once JPATH_PLATFORM . '/loader.php';

    // If JLoader still does not exist panic.
    if (!class_exists('JLoader')) {
        throw new RuntimeException('Joomla Platform not loaded.');
    }
}

// Setup the autoloaders.
JLoader::setup();

// Create the Composer autoloader
/** @var \Composer\Autoload\ClassLoader $loader */
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';

// We need to pull our decorated class loader into memory before unregistering Composer's loader
class_exists('\\Joomla\\CMS\\Autoload\\ClassLoader');

$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register([new \Joomla\CMS\Autoload\ClassLoader($loader), 'loadClass'], true, true);

// Register the class aliases for Framework classes that have replaced their Platform equivalents
require_once JPATH_LIBRARIES . '/classmap.php';

// Load extension classes
require_once JPATH_LIBRARIES . '/namespacemap.php';
$extensionPsr4Loader = new \JNamespacePsr4Map();
$extensionPsr4Loader->load();

// Define the Joomla version if not already defined.
defined('JVERSION') or define('JVERSION', (new \Joomla\CMS\Version())->getShortVersion());
