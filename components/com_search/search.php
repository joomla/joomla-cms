<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');

// Create the controller
$controller = JController::getInstance('Search');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
