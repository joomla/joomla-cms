<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

if (!JFactory::getUser()->authorise('core.manage', $input->get('extension')))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$task = $input->get('task');

$controller	= JControllerLegacy::getInstance('Categories');
$controller->execute($input->get('task'));
$controller->redirect();
