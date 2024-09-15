<?php

/**
 * @package    Joomla.Installation
 *
 * @copyright  (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * NOTE: This file should remain compatible with PHP 5.2 to allow us to run our PHP minimum check and show a friendly error message
 */

/**
 * Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
 */
\define('JOOMLA_MINIMUM_PHP', '8.1.0');

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<')) {
    echo 'Sorry, your PHP version is not supported.' . PHP_EOL;
    echo 'Your command line php needs to be version ' . JOOMLA_MINIMUM_PHP . ' or newer to run the Joomla! CLI Tools' . PHP_EOL;
    echo 'The version of PHP currently running this code, at the command line, is PHP version ' . PHP_VERSION . '.' . PHP_EOL;
    echo 'Please note, the version of PHP running your commands here, may be different to the version that is used by ';
    echo 'your web server to run the Joomla! Web Application' . PHP_EOL;

    exit;
}

/**
 * Constant that is checked in included files to prevent direct access.
 * define() is used rather than "const" to not error for PHP 5.2 and lower
 */
\define('_JEXEC', 1);

// Constant to identify the CLI installation
\define('_JCLI_INSTALLATION', 1);

// Run the application - All executable code should be triggered through this file
require_once \dirname(__FILE__) . '/includes/cli.php';
