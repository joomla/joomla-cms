<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_config
 *
 * @copyright   (C) 2013 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load classes
JLoader::registerPrefix('Config', JPATH_COMPONENT);

// Application
$app = JFactory::getApplication();

// Tell the browser not to cache this page.
$app->setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$controllerHelper = new ConfigControllerHelper;
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Config';

// Perform the Request task
$controller->execute();
