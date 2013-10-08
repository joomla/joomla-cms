<?php
/**
 * @package     Joomla.Installation
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Define the base path and require the other defines
define('JPATH_BASE', dirname(__DIR__));
require_once __DIR__ . '/defines.php';

// Launch the application
require_once __DIR__ . '/framework.php';

JLoader::registerNamespace('Installation\\', JPATH_INSTALLATION . '/src');

// Register the application's router due to non-standard include
JLoader::register('JRouterInstallation', __DIR__ . '/router.php');

/**
 * Register a couple installation controllers that don't exactly follow PSR-0,
 * at least until all the JS for the task is updated.
 */
JLoader::register(
	'Installation\\Controller\\InstallDatabase_backupController',
	JPATH_INSTALLATION . '/src/Installation/Controller/InstallDatabase_backupController.php'
);
JLoader::register(
	'Installation\\Controller\\InstallDatabase_removeController',
	JPATH_INSTALLATION . '/src/Installation/Controller/InstallDatabase_removeController.php'
);

// Alias so JHtml works right, for now.
class_alias('Installation\\Helpers\\HtmlHelper', 'JHtmlInstallation');
