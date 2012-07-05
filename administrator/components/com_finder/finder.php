<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_finder
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_finder'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$task = JFactory::getApplication()->input->get('task');

$controller	= JControllerLegacy::getInstance('Finder');
$controller->execute($task);
$controller->redirect();
