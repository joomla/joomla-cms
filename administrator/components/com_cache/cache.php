<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_cache
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load classes
JLoader::registerPrefix('Cache', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$app = JFactory::getApplication();

// Set a fallback view
if (!$app->input->get('view'))
{
	$app->input->set('view', 'cache');
}

// Create the controller
$controllerHelper = new JControllerHelper();
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Cache';

// Perform the Request task
$controller->execute();
