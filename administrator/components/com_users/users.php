<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
$user = JFactory::getUser();
$view = JRequest::getCmd('view');
$layout = JRequest::getCmd('layout');
$task = JRequest::getCmd('task');
$id = JRequest::getInt('id');
if (!(		$user->authorise('core.manage', 'com_users')
		||		($user->authorise('core.edit.own', 'com_users') || $user->authorise('core.edit', 'com_users'))
			&&	(in_array($task, array('user.edit', 'user.save','user.apply','user.cancel')) || $view=='user' && $layout=='edit')
			&&	$id==$user->id))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= JController::getInstance('Users');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
