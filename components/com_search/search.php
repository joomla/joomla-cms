<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_search
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Create the controller
$controller = JControllerLegacy::getInstance('Search');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
