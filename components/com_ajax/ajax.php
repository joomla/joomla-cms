<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Init the base path, for allow to call the same controllers from back end
define('COM_AJAX_PATH_COMPONENT', JPATH_ROOT . '/components/com_ajax');

// Load classes
JLoader::registerPrefix('Ajax', COM_AJAX_PATH_COMPONENT);
// Register classes to the autoload list
JLoader::register('AjaxError', COM_AJAX_PATH_COMPONENT . '/helper/error.php');

// Use own exception handler
set_exception_handler(array('AjaxError', 'render'));

// Get Application
$app = JFactory::getApplication();

if(!$app->input->get('format'))
{
	throw new InvalidArgumentException('No format given. Please specify response format.', 500);
}

$action = 'plugin';
if($app->input->get('module'))
{
	$action = 'module';
}
$app->input->set('action', $action);

// Create the controller and execute
$controller = new AjaxControllerAjax();
$controller->execute();

