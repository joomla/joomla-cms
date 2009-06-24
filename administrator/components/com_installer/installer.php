<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_installer
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('core.installer.manage')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

$ext	= JRequest::getWord('type');
$task 	= JRequest::getWord('task');
$subMenus = array(
	'Install' => 'install',
	'Update' => 'update',
	'Manage' => 'manage',
	'Discover' => 'discover',
	'Warnings' => 'warnings');

foreach ($subMenus as $name => $extension)
{
	// TODO: Rewrite this extension so it acts normally and doesn't require this sort of a hack below
	JSubMenuHelper::addEntry(JText::_($name), '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', (($task != 'manage' && $task == $extension) || ($task == 'manage' && $extension == $ext)));
}

require_once(JPATH_COMPONENT.DS.'controller.php');

$controller = new InstallerController(array('default_task' => 'installform'));
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
