<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Installer
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

$subMenus = array(
	'Components' => 'components',
	'Modules' => 'modules',
	'Plugins' => 'plugins',
	'Languages' => 'languages',
	'Templates' => 'templates');

JSubMenuHelper::addEntry(JText::_('Install'), '#" onclick="javascript:document.adminForm.type.value=\'\';submitbutton(\'installer\');', !in_array($ext, $subMenus));
foreach ($subMenus as $name => $extension) {
	JSubMenuHelper::addEntry(JText::_($name), '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', ($extension == $ext));
}

require_once(JPATH_COMPONENT.DS.'controller.php');

$controller = new InstallerController(array('default_task' => 'installform'));
//die(JRequest::getCmd('task'));
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
