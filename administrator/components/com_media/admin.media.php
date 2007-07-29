<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Media
 * @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();

$params =& JComponentHelper::getParams('com_media');

if (!$user->authorize( 'com_media', 'manage' )) {
	$mainframe->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Load the admin HTML view
require_once (JPATH_COMPONENT.DS.'controller.php');

// Set the path definitions
define('COM_MEDIA_BASE', JPATH_SITE.DS.$params->get('file_path', 'images'));
define('COM_MEDIA_BASEURL', $mainframe->getSiteURL().$params->get('file_path', 'images'));

$folder			= JRequest::getVar('folder', '', '', 'path');
$folderCheck	= JRequest::getVar('folder', null, '', 'string', JREQUEST_ALLOWRAW);
if (($folderCheck !== null) && ($folder !== $folderCheck)) {
	JError::raiseWarning(403, JText::_('WARNDIRNAME'));
}

require_once( JPATH_COMPONENT.DS.'helpers'.DS.'media.php' );

switch (JRequest::getCmd('task')) {

	case 'upload' :
		MediaController::upload();
		break;

	case 'uploadbatch' :
		MediaController::batchUpload();
		MediaController::showMedia();
		break;

	case 'createfolder' :
		MediaController::createFolder();
		MediaController::showMedia();
		break;

	case 'delete' :
		MediaController::delete($folder);
		$mainframe->redirect('index.php?option=com_media&task=list&tmpl=component&folder='.$folder);
		//MediaController::listMedia();
		break;

	case 'list' :
		MediaController::listMedia();
		break;

	// popup directory creation interface for use by components
	case 'popupDirectory' :
		MediaController::showFolder();
		break;

	// popup upload interface for use by components
	case 'popupUpload' :
		MediaController::showUpload();
		break;

	// popup upload interface for use by components
	case 'imgManager' :
		MediaController::imgManager(COM_MEDIA_BASE);
		break;

	case 'imgManagerList' :
		MediaController::imgManagerList($folder);
		break;

	default :
		MediaController::showMedia();
		break;
}
?>