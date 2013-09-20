<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Sessions
jimport('joomla.session.session');

// Load classes
JLoader::registerPrefix('Config', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

$view = $app->input->get('view');

if (empty($view))
{
	$app->input->set('view', 'application');
}

$controllerHelper = new JControllerHelper();
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Config';

// Check if component mentioned
$component = $app->input->get('component');

if (!empty($component))
{
	$controller->component = $component;
}

// Perform the Request task
$controller->execute();
