<?php

/**
 * @package    Joomla.Cli
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// We are a valid entry point.
const _JEXEC = 1;

// Define the application's minimum supported PHP version as a constant so it can be referenced within the application.
const JOOMLA_MINIMUM_PHP = '7.2.5';

if (version_compare(PHP_VERSION, JOOMLA_MINIMUM_PHP, '<')) {
    echo 'Sorry, your PHP version is not supported.' . PHP_EOL;
    echo 'Your command line php needs to be version ' . JOOMLA_MINIMUM_PHP . ' or newer to run the Joomla! CLI Tools' . PHP_EOL;
    echo 'The version of PHP currently running this code, at the command line, is PHP version ' . PHP_VERSION . '.' . PHP_EOL;
    echo 'Please note, the version of PHP running your commands here, may be different to the version that is used by ';
    echo 'your web server to run the Joomla! Web Application' . PHP_EOL;

    exit;
}

// Load system defines
if (file_exists(dirname(__DIR__) . '/defines.php')) {
    require_once dirname(__DIR__) . '/defines.php';
}

if (!defined('_JDEFINES')) {
    define('JPATH_BASE', dirname(__DIR__));
    require_once JPATH_BASE . '/includes/defines.php';
}

// Check for presence of vendor dependencies not included in the git repository
if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor')) {
    echo 'It looks like you are trying to run Joomla! from our git repository.' . PHP_EOL;
    echo 'To do so requires you complete a couple of extra steps first.' . PHP_EOL;
    echo 'Please see https://docs.joomla.org/Special:MyLanguage/J4.x:Setting_Up_Your_Local_Environment for further details.' . PHP_EOL;

    exit;
}

// Check if installed
if (
    !file_exists(JPATH_CONFIGURATION . '/configuration.php')
    || (filesize(JPATH_CONFIGURATION . '/configuration.php') < 10)
) {
    echo 'Install Joomla to run cli commands' . PHP_EOL;

    exit;
}

// Get the framework.
require_once JPATH_BASE . '/includes/framework.php';

// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();

/*
 * Alias the session service keys to the CLI session service as that is the primary session backend for this application
 *
 * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
 * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
 * deprecated to be removed when the class name alias is removed as well.
 */
$container->alias('session', 'session.cli')
    ->alias('JSession', 'session.cli')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\Session::class, 'session.cli')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.cli');

$app                              = \Joomla\CMS\Factory::getContainer()->get(\Joomla\Console\Application::class);
\Joomla\CMS\Factory::$application = $app;
$app->execute();
