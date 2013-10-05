<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_ajax
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Init the base path, for allow to call the same controllers from back end
define('COM_AJAX_PATH_COMPONENT', JPATH_ROOT . '/components/com_ajax');

// Register classes to the autoload list
JLoader::register('AjaxError', COM_AJAX_PATH_COMPONENT . '/helpers/error.php');
JLoader::register('AjaxModuleHelper', COM_AJAX_PATH_COMPONENT . '/helpers/module.php');

// Use own exception handler
set_exception_handler(array('AjaxError', 'display'));

// Get Application
$app = JFactory::getApplication();

if(!$app->input->get('format'))
{
	throw new InvalidArgumentException('No format given. Please specify response format.', 500);
}

$controller = JControllerLegacy::getInstance('Ajax', array('base_path' => COM_AJAX_PATH_COMPONENT));
$controller->execute($app->input->get('task'));
$controller->redirect();

