<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Load classes
JLoader::registerPrefix('Config', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JFactory::getApplication()->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

$controllerHelper = new ConfigControllerHelper();
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Config';

// Perform the Request task
$controller->execute();

