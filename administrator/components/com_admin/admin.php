<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_admin
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// No access check.

$controller	= JControllerLegacy::getInstance('Admin');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
