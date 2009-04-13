<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Content
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

$user = & JFactory::getUser();
if (!$user->authorize('com_content.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

// Set the helper directory
JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');

// Require specific controller if requested
if ($controllerName = JRequest::getWord('controller', '')) {
	$path = JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php';
	if (file_exists($path)) {
		require_once $path;
	} else {
		$controller = '';
	}
}

// Create the controller
$classname	= 'ContentController'.ucfirst($controllerName);
$controller	= new $classname();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
