<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Define the base path and require the other defines
define('JPATH_BASE', dirname(__DIR__));

require_once __DIR__ . '/defines.php';

// Launch the application
require_once __DIR__ . '/framework.php';

// Check if the default log directory can be written to, add a logger for errors to use it
if (is_writable(JPATH_ADMINISTRATOR . '/logs'))
{
	\Joomla\CMS\Log\Log::addLogger(
		[
			'format'    => '{DATE}\t{TIME}\t{LEVEL}\t{CODE}\t{MESSAGE}',
			'text_file' => 'error.php'
		],
		\Joomla\CMS\Log\Log::ALL,
		['error']
	);
}

// Register the Installation application
JLoader::registerNamespace('Joomla\\CMS\\Installation', JPATH_INSTALLATION . '/src', false, false, 'psr4');

JLoader::registerAlias('JRouterInstallation', \Joomla\CMS\Installation\Router\InstallationRouter::class);

// Get the dependency injection container
$container = \Joomla\CMS\Factory::getContainer();
$container->registerServiceProvider(new \Joomla\CMS\Installation\Service\Provider\Application);

// Instantiate and execute the application
$container->get('InstallationApplicationWeb')->execute();
