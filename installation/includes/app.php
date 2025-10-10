<?php

/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Define the base path and require the other defines
\define('JPATH_BASE', \dirname(__DIR__));

require_once __DIR__ . '/defines.php';

// Check for presence of vendor dependencies not included in the git repository
if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_ROOT . '/media/vendor')) {
    echo file_get_contents(JPATH_ROOT . '/templates/system/build_incomplete.html');

    exit;
}

// Launch the application
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

JLoader::registerAlias('JRouterInstallation', \Joomla\CMS\Installation\Router\InstallationRouter::class);

// Get the dependency injection container
$container = \Joomla\CMS\Factory::getContainer();
$container->registerServiceProvider(new \Joomla\CMS\Installation\Service\Provider\Application());

/*
 * Alias the session service keys to the web session service as that is the primary session backend for this application
 *
 * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
 * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
 * deprecated to be removed when the class name alias is removed as well.
 */
$container->alias('session.web', 'session.web.installation')
    ->alias('session', 'session.web.installation')
    ->alias('JSession', 'session.web.installation')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.installation')
    ->alias(\Joomla\Session\Session::class, 'session.web.installation')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.installation');

/** @var \Joomla\CMS\Installation\Application\InstallationApplication $app */
$app = $container->get(\Joomla\CMS\Installation\Application\InstallationApplication::class);

\Joomla\CMS\Factory::$application = $app;

// Instantiate and execute the application
$app->execute();
