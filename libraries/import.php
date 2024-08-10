<?php

/**
 * Bootstrap file for the Joomla Platform.  Including this file into your application will make Joomla
 * Platform libraries available for use.
 *
 * @copyright  (C) 2011 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

trigger_error(
    sprintf(
        'Bootstrapping Joomla using the %1$s file is deprecated.  Use %2$s instead.',
        __FILE__,
        __DIR__ . '/bootstrap.php'
    ),
    E_USER_DEPRECATED
);

/**
 * Set the platform root path as a constant if necessary.
 *
 * @deprecated 4.4.0 will be removed in 6.0
 *             Use defined('_JEXEC') or die; to detect if the CMS is loaded correctly
 **/
if (!defined('JPATH_PLATFORM')) {
    define('JPATH_PLATFORM', __DIR__);
}

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

if (!defined('IS_WIN')) {
    define('IS_WIN', $os === 'WIN');
}

if (!defined('IS_UNIX')) {
    define('IS_UNIX', IS_WIN === false);
}

// Import the library loader if necessary.
if (!class_exists('JLoader')) {
    require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded.
if (!class_exists('JLoader')) {
    throw new RuntimeException('Joomla Platform not loaded.');
}

// Setup the autoloaders.
JLoader::setup();

// Register the PasswordHash lib
JLoader::register('PasswordHash', JPATH_PLATFORM . '/phpass/PasswordHash.php');
