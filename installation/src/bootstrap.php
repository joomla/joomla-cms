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

// Start the ball rolling.
require_once __DIR__ . '/framework.php';

// Register our app namespace
JLoader::registerNamespace('Installation\\', JPATH_INSTALLATION . '/src');

// Instantiate the application
$app = new Installation\Application\WebApplication;

// Execute
$app->execute();
