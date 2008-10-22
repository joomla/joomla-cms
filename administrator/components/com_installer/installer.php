<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
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