<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_login
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

defined('_JEXEC') or die;

$task = JRequest::getCmd('task');
if ($task != 'login' && $task != 'logout')
{
	JRequest::setVar('task', '');
	$task = '';
}

$controller	= JControllerLegacy::getInstance('Login');
$controller->execute($task);
$controller->redirect();
