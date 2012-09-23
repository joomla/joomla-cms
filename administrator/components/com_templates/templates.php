<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_templates'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('TemplatesHelper', __DIR__ . '/helpers/templates.php');

$controller	= JControllerLegacy::getInstance('Templates');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
