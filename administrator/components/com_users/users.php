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
if (!JFactory::getUser()->authorise('core.manage', 'com_users')) {
	return JError::raiseWarning(404, JText::_('ALERTNOTAUTH'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= &JController::getInstance('Users');
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
