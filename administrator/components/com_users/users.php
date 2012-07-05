<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_users'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('UsersHelper', dirname(__FILE__) . '/helpers/users.php');

$task = JFactory::getApplication()->input->get('task');

$controller	= JControllerLegacy::getInstance('Users');
$controller->execute($task);
$controller->redirect();
