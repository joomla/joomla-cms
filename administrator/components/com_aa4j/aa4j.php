<?php
/**
 * @version		$Id: admin.php 11/05/2011 18.42
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2011 alikonweb.it. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// No access check.

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Aa4j');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();