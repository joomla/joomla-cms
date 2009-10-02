<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Templates
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_templates')) {
	return JError::raiseWarning(404, JText::_('ALERTNOTAUTH'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// TODO: Refactor to support latest MVC pattern.

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= new TemplatesController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();