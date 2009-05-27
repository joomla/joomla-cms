<?php
/**
 * @version		$Id: admin.modules.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Modules
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
if (!$user->authorize('core.modules.manage')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Require the base controller
require_once (JPATH_COMPONENT.DS.'controller.php');

// Create the controller
$controller	= new ModulesController(array('default_task' => 'view'));

// Perform the Request task
$controller->execute(JRequest::getCmd('task', 'view'));

// Redirect if set by the controller
$controller->redirect();
