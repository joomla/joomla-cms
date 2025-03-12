<?php

/**
 * Bootstrap file for the Joomla! CMS [with legacy libraries].
 * Including this file into your application will make Joomla libraries available for use.
 *
 * @copyright  (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

defined('IS_WIN') or define('IS_WIN', ($os === 'WIN'));
defined('IS_UNIX') or define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')));

// Import the library loader if necessary.
if (!class_exists('JLoader')) {
    require_once JPATH_LIBRARIES . '/loader.php';

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

/**
 * Register the global exception handler. And set error level to server default error level.
 * The error level may be changed later in boot up process, after application config will be loaded.
 * Do not remove the variable, to allow to use it further, after including this file.
 */
$errorHandler = \Symfony\Component\ErrorHandler\ErrorHandler::register();
\Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer::setTemplate(__DIR__ . '/../templates/system/fatal.php');

// Register the error handler which processes E_USER_DEPRECATED errors
if (error_reporting() & E_USER_DEPRECATED) {
    set_error_handler(['Joomla\CMS\Exception\ExceptionHandler', 'handleUserDeprecatedErrors'], E_USER_DEPRECATED);
}

// Suppress phar stream wrapper for non .phar files
$behavior = new \TYPO3\PharStreamWrapper\Behavior();
\TYPO3\PharStreamWrapper\Manager::initialize(
    $behavior->withAssertion(new \TYPO3\PharStreamWrapper\Interceptor\PharExtensionInterceptor())
);

if (in_array('phar', stream_get_wrappers())) {
    stream_wrapper_unregister('phar');
    stream_wrapper_register('phar', 'TYPO3\\PharStreamWrapper\\PharStreamWrapper');
}

// Define the Joomla version if not already defined.
defined('JVERSION') or define('JVERSION', (new \Joomla\CMS\Version())->getShortVersion());

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER)) {
    \Joomla\CMS\Log\Log::addLogger(['logger' => 'messagequeue'], \Joomla\CMS\Log\Log::ALL, ['jerror']);
}

// Register the Crypto lib
JLoader::register('Crypto', JPATH_LIBRARIES . '/php-encryption/Crypto.php');

// Register the PasswordHash library.
JLoader::register('PasswordHash', JPATH_LIBRARIES . '/phpass/PasswordHash.php');
