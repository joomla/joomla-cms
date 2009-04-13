<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$user = & JFactory::getUser();
if (!$user->authorize('core.installer.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

$ext	= JRequest::getWord('type');

$subMenus = array(
	'Install' => 'install',
	'Update' => 'update',
	'Manage' => 'manage',
	'Discover' => 'discover',
	'Warnings' => 'warnings');

foreach ($subMenus as $name => $extension) {
	// TODO: Rewrite this extension so it acts normally and doesn't require this sort of a hack below
	JSubMenuHelper::addEntry(JText::_( $name ), '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', (($task != 'manage' && $task == $extension) || ($task == 'manage' && $extension == $ext)));
}

require_once JPATH_COMPONENT.DS.'controller.php';

$controller = new InstallerController( array('default_task' => 'installform') );
//die(JRequest::getCmd('task'));
$controller->execute( JRequest::getCmd('task') );
$controller->redirect();