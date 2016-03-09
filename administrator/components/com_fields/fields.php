<?php
/**
 * @package    Fields
 * @author     Digital Peak http://www.digital-peak.com
 * @copyright  Copyright (C) 2015 - 2016 Digital Peak. All rights reserved.
 * @license    http://www.gnu.org/licenses/gpl.html GNU/GPL
 */
defined('_JEXEC') or die();

$input = JFactory::getApplication()->input;

$parts = explode('.', $input->get('context'));
$component = $parts[0];

if (! JFactory::getUser()->authorise('core.manage', $component))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::import('components.com_fields.helpers.internal', JPATH_ADMINISTRATOR);

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute($input->get('task'));
$controller->redirect();
