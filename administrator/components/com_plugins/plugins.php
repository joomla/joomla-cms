<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_plugins
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_plugins'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Load classes
JLoader::registerPrefix('Plugins', JPATH_COMPONENT_ADMINISTRATOR);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

// Set a fallback view
if (!$app->input->get('view'))
{
	$app->input->set('view', 'plugins');
}

// Create the controller
$controllerHelper = new JControllerHelper();
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Plugins';

// Perform the Request task
$controller->execute();
