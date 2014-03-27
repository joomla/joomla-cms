<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_categories
 * @copyright	Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
// FIXME (GWK) ACLs are set on com_content, not on com_category!
if (!JFactory::getUser()->authorise('core.manage', 'com_content'))
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Execute the task.
$controller	= JControllerLegacy::getInstance('Categories');
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
