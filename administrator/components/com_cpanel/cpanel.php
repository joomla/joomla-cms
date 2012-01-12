<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_cpanel
 * @copyright		Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die;

// No access check.

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Cpanel');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
