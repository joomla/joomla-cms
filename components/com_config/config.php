<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');

// Load classes
JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/');
JLoader::registerPrefix('Config', JPATH_SITE . '/components/com_config/');


// Get construction params
$app = JFactory::getApplication();
$input = $app->input;
$config = array('default_view' => 'application', 'layout' => 'form');

$controller = new ConfigController($input, $app, $config);
$controller->execute();
$controller->redirect();

/*
// Access checks are done internally because of different requirements for the two controllers.

Tell the browser not to cache this page. Removed optimization for now
JFactory::getApplication()->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$controllerHelper = new ConfigControllerHelper;
$controller = $controllerHelper->parseController($app);
$controller->prefix = 'Config';

// Perform the Request task
 $controller->execute();
*/