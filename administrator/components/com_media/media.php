<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_media
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_media')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$params = JComponentHelper::getParams('com_media');

// Load the admin HTML view
require_once JPATH_COMPONENT.'/helpers/media.php';

// Set the path definitions
$popup_upload = JRequest::getCmd('pop_up',null);
$path = "file_path";

$view = JRequest::getVar('view');
if (substr(strtolower($view),0,6) == "images" || $popup_upload == 1) {
	$path = "image_path";
}

define('COM_MEDIA_BASE',	JPATH_ROOT.'/'.$params->get($path, 'images'));
define('COM_MEDIA_BASEURL', JURI::root().$params->get($path, 'images'));

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Media');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
