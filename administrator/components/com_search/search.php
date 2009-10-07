<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_search')) {
	return JError::raiseWarning(404, JText::_('ALERTNOTAUTH'));
}

// Include dependancies
jimport('joomla.application.component.controller');

// TODO: Refactor to support latest MVC pattern.

require_once JPATH_COMPONENT.DS.'controller.php';

$controller = new SearchController();
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();