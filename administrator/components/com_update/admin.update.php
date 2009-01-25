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

/*
 * Make sure the user is authorized to view this page
 */
$user = & JFactory::getUser();
if (!$user->authorize('com_installer', 'installer')) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once( JPATH_COMPONENT.DS.'controller.php' );
JToolbarHelper::title('Update Manager');
$controller = new UpdateController();
$controller->execute( JRequest::getCmd('task') );
$controller->redirect();