<?php
// Here you can initialize variables that will be available to your tests

/**
 * Mock for the global application exit.
 *
 * @param   mixed  $message  Exit code or string. Defaults to zero.
 *
 * @return  void
 */
function jexit($message = 0)
{
}

define('_JEXEC', 1);

// Fix magic quotes.
ini_set('magic_quotes_runtime', 0);

// Maximise error reporting.
ini_set('zend.ze1_compatibility_mode', '0');
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set fixed precision value to avoid round related issues
ini_set('precision', 14);

/*
 * Ensure that required path constants are defined.  These can be overridden within the phpunit.xml file
 * if you chose to create a custom version of that file.
 */
if (!defined('JPATH_TESTS'))
{
	define('JPATH_TESTS', realpath(dirname(dirname(__DIR__)) . '/unit'));
}
if (!defined('JPATH_TEST_DATABASE'))
{
	define('JPATH_TEST_DATABASE', JPATH_TESTS . '/stubs/database');
}
if (!defined('JPATH_TEST_STUBS'))
{
	define('JPATH_TEST_STUBS', JPATH_TESTS . '/stubs');
}
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', realpath(dirname(dirname(dirname(__DIR__))) . '/libraries'));
}
if (!defined('JPATH_LIBRARIES'))
{
	define('JPATH_LIBRARIES', JPATH_PLATFORM);
}
if (!defined('JPATH_BASE'))
{
	define('JPATH_BASE', realpath(dirname(dirname(dirname(__DIR__)))));
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
if (!defined('JPATH_SITE'))
{
	define('JPATH_SITE', JPATH_ROOT);
}
if (!defined('JPATH_ADMINISTRATOR'))
{
	define('JPATH_ADMINISTRATOR', JPATH_ROOT . '/administrator');
}
if (!defined('JPATH_INSTALLATION'))
{
	define('JPATH_INSTALLATION', JPATH_ROOT . '/installation');
}
if (!defined('JPATH_MANIFESTS'))
{
	define('JPATH_MANIFESTS', JPATH_ADMINISTRATOR . '/manifests');
}
if (!defined('JPATH_PLUGINS'))
{
	define('JPATH_PLUGINS', JPATH_BASE . '/plugins');
}
if (!defined('JPATH_THEMES'))
{
	define('JPATH_THEMES', JPATH_BASE . '/templates');
}
if (!defined('JDEBUG'))
{
	define('JDEBUG', false);
}

// Import the platform in legacy mode.
require_once JPATH_PLATFORM . '/import.legacy.php';

// Bootstrap the CMS libraries.
require_once JPATH_LIBRARIES . '/cms.php';

// Register the core Joomla test classes.
JLoader::registerPrefix('Test', JPATH_TESTS . '/core');

// Register the deprecation logger
TestHelper::registerDeprecationLogger();

// Register the logger if enabled
TestHelper::registerLogger();
