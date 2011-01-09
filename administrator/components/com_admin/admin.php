<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// No access check.

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Admin');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
