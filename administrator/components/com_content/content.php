<?php
/**
 * @package		Joomla.Administrator
 * @subpackage	com_content
 * @copyright	Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_content')) {
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

// Register helper class
JLoader::register('ContentHelper', dirname(__FILE__) . '/helpers/content.php');

$controller = JControllerLegacy::getInstance('Content');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
