<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_checkin
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

// Sessions
jimport('joomla.session.session');

// Load classes
JLoader::registerPrefix('Checkin', JPATH_COMPONENT);

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Application
$app = JFactory::getApplication();

// Set a fallback view
if (!$app->input->get('view'))
{
	$app->input->set('view', 'checkin');
}

// Create the controller
$controllerHelper = new JControllerHelper();
$controller = $controllerHelper->parseController($app);

$controller->prefix = 'Checkin';

// Perform the Request task
$controller->execute();
