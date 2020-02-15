<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

// Access checks are done internally because of different requirements for the two controllers.

// Tell the browser not to cache this page.
JFactory::getApplication()->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Load classes
JLoader::registerPrefix('Config', JPATH_COMPONENT);
JLoader::registerPrefix('Config', JPATH_ROOT . '/components/com_config');

// Application
$app = JFactory::getApplication();

$controllerHelper = new ConfigControllerHelper;
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Config';

// Perform the Request task
$controller->execute();
