<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

$controller = JController::getInstance('Wrapper');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
