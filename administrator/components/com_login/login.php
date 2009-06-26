<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

// Require the base controller
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Login');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();