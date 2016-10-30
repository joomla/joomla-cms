<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 * 
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

$parts     = explode('.', $input->get('context'));
$component = $parts[0];

if (!JFactory::getUser()->authorise('core.manage', $component))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::import('components.com_fields.helpers.internal', JPATH_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute($input->get('task'));
$controller->redirect();
