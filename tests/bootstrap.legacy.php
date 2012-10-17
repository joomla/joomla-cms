<?php
/**
 * Unit test runner bootstrap file for the Joomla Platform [with legacy libraries].
 *
 * @package    Joomla.UnitTest
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 * @link       http://www.phpunit.de/manual/current/en/installation.html
 */

// Fix magic quotes.
@ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.
error_reporting(E_ALL & ~E_STRICT);
ini_set('display_errors', 1);

/*
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */
if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', realpath(__DIR__));
}
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', realpath(dirname(JPATH_TESTS) . '/libraries'));
}
if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', realpath(JPATH_TESTS . '/tmp'));
}
if (!defined('JPATH_ROOT'))
{
	define('JPATH_ROOT', realpath(JPATH_BASE));
}
if (!defined('JPATH_CACHE'))
{
	define('JPATH_CACHE', JPATH_BASE . '/cache');
}
if (!defined('JPATH_CONFIGURATION'))
{
	define('JPATH_CONFIGURATION', JPATH_BASE);
}
if (!defined('JPATH_MANIFESTS'))
{
	define('JPATH_MANIFESTS', JPATH_BASE . '/manifests');
}
if (!defined('JPATH_PLUGINS'))
{
	define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}
if (!defined('JPATH_THEMES'))
{
	define('JPATH_THEMES', JPATH_BASE . '/themes');
}

// Import the platform.
require_once JPATH_PLATFORM . '/import.legacy.php';


// Register the core Joomla test classes.
JLoader::registerPrefix('Test', __DIR__ . '/core');

/*
 * The following classes still depend on `JVersion` so we must load it until they are dealt with.
 *
 * JInstallerHelper
 * JUpdaterCollection
 * JUpdaterExtension
 * JUpdate
 * JFactory
 */
require_once __DIR__ . '/version.php';

/*
 * The PHP garbage collector can be too aggressive in closing circular references before they are no longer needed.  This can cause
 * segfaults during long, memory-intensive processes such as testing large test suites and collecting coverage data.  We explicitly
 * disable garbage collection during the execution of PHPUnit processes so that we (hopefully) don't run into these issues going
 * forwards.  This is only a problem PHP 5.3+.
 */
gc_disable();
