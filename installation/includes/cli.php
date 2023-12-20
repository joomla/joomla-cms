<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2022 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Define the base path and require the other defines
define('JPATH_BASE', dirname(__DIR__));

require_once __DIR__ . '/defines.php';

// Check for presence of vendor dependencies not included in the git repository
if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor')) {
    echo 'It looks like you are trying to run Joomla! from our git repository.' . PHP_EOL;
    echo 'To do so requires you complete a couple of extra steps first.' . PHP_EOL;
    echo 'Please see https://docs.joomla.org/Special:MyLanguage/J4.x:Setting_Up_Your_Local_Environment for further details.' . PHP_EOL;

    exit;
}

// Get the framework.
require_once __DIR__ . '/framework.php';

// Check if the default log directory can be written to, add a logger for errors to use it
if (is_writable(JPATH_ADMINISTRATOR . '/logs')) {
    \Joomla\CMS\Log\Log::addLogger(
        [
            'format'    => '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}',
            'text_file' => 'error.php',
        ],
        \Joomla\CMS\Log\Log::ALL,
        ['error']
    );
}

// Register the Installation application
JLoader::registerNamespace('Joomla\\CMS\\Installation', JPATH_INSTALLATION . '/src', false, false);

// Get the dependency injection container
$container = \Joomla\CMS\Factory::getContainer();
$container->registerServiceProvider(new \Joomla\CMS\Installation\Service\Provider\Application());

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

// Instantiate and execute the application
$container->get(\Joomla\CMS\Installation\Application\CliInstallationApplication::class)->execute();
