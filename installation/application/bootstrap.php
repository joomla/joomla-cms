<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Define the base path and require the other defines
define('JPATH_BASE', dirname(__DIR__));
require_once __DIR__ . '/defines.php';

// Launch the application
require_once __DIR__ . '/framework.php';

// Register the Installation application
JLoader::registerPrefix('Installation', JPATH_INSTALLATION);

// Register the application's router due to non-standard include
JLoader::register('JRouterInstallation', __DIR__ . '/router.php');

// Instantiate the dependency injection container
JFactory::$container = (new \Joomla\DI\Container)
	->registerServiceProvider(new InstallationServiceProviderApplication)
	->registerServiceProvider(new InstallationServiceProviderSession)
	->registerServiceProvider(new \Joomla\Cms\Service\Provider\Dispatcher)
	->registerServiceProvider(new \Joomla\Cms\Service\Provider\Database);
