<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_users')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Register helper class
JLoader::register('UsersHelper', dirname(__FILE__) . '/helpers/users.php');

// Execute the task.
$controller	= JControllerLegacy::getInstance('Users');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
