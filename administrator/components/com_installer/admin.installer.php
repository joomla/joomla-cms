<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Installer
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
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

$ext	= JRequest::getVar('type');

$subMenus = array(
	'Components' => 'components',
	'Modules' => 'modules',
	'Plugins' => 'plugins',
	'Languages' => 'languages',
	'Templates' => 'templates');

JSubMenu::addEntry(JText::_( 'Install' ), '#" onclick="javascript:document.adminForm.type.value=\'\';submitbutton(\'installer\');', !in_array( $ext, $subMenus));
foreach ($subMenus as $name => $extension)
{
	JSubMenu::addEntry(JText::_( $name ), '#" onclick="javascript:document.adminForm.type.value=\''.$extension.'\';submitbutton(\'manage\');', ($extension == $ext));
}

require_once( JPATH_COMPONENT.DS.'controller.php' );

$controller = new InstallerController( array('default_task' => 'installform') );
$controller->execute( JRequest::getVar('task') );
$controller->redirect();
?>