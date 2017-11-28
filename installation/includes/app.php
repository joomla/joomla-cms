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

// Instantiate the dependency injection container
\Joomla\CMS\Factory::$container = (new \Joomla\DI\Container)
	->registerServiceProvider(new \Joomla\CMS\Installation\Service\Provider\Application)
	->registerServiceProvider(new \Joomla\CMS\Installation\Service\Provider\Session)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Toolbar)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Menu)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Document)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Dispatcher)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Form)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Authentication)
	->registerServiceProvider(new \Joomla\CMS\Service\Provider\Database);

// Instantiate and execute the application
\Joomla\CMS\Factory::getContainer()->get(\Joomla\CMS\Installation\Application\InstallationApplication::class)->execute();
