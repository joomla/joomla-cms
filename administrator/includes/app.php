<?php

/**
 * @package    Joomla.Administrator
 *
 * @copyright  (C) 2017 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

\defined('_JEXEC') or die;

// Saves the start time and memory usage.
$startTime = microtime(1);
$startMem  = memory_get_usage();

if (file_exists(\dirname(__DIR__) . '/defines.php')) {
    include_once \dirname(__DIR__) . '/defines.php';
}

require_once __DIR__ . '/defines.php';

// Check for presence of vendor dependencies not included in the git repository
if (!file_exists(JPATH_LIBRARIES . '/vendor/autoload.php') || !is_dir(JPATH_PUBLIC . '/media/vendor')) {
    echo file_get_contents(JPATH_ROOT . '/templates/system/build_incomplete.html');

    exit;
}

require_once JPATH_BASE . '/includes/framework.php';

// Set profiler start time and memory usage and mark afterLoad in the profiler.
JDEBUG ? \Joomla\CMS\Profiler\Profiler::getInstance('Application')->setStart($startTime, $startMem)->mark('afterLoad') : null;

// Boot the DI container
$container = \Joomla\CMS\Factory::getContainer();

/*
 * Alias the session service keys to the web session service as that is the primary session backend for this application
 *
 * In addition to aliasing "common" service keys, we also create aliases for the PHP classes to ensure autowiring objects
 * is supported.  This includes aliases for aliased class names, and the keys for aliased class names should be considered
 * deprecated to be removed when the class name alias is removed as well.
 */
$container->alias('session.web', 'session.web.administrator')
    ->alias('session', 'session.web.administrator')
    ->alias('JSession', 'session.web.administrator')
    ->alias(\Joomla\CMS\Session\Session::class, 'session.web.administrator')
    ->alias(\Joomla\Session\Session::class, 'session.web.administrator')
    ->alias(\Joomla\Session\SessionInterface::class, 'session.web.administrator');

// Instantiate the application.
$app = $container->get(\Joomla\CMS\Application\AdministratorApplication::class);

// Set the application as global app
\Joomla\CMS\Factory::$application = $app;

// Execute the application.
$app->execute();
