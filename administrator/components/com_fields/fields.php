<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

$app       = JFactory::getApplication();
$context   = $app->getUserStateFromRequest('com_fields.groups.context', 'context', '', 'CMD');
$component = '';

if (!$context)
{
	$parts     = explode('.', $app->getUserStateFromRequest('com_fields.fields.context', 'context', '', 'CMD'), 2);
	$component = $parts[0];
}

if (!JFactory::getUser()->authorise('core.manage', $component))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');
JLoader::register('FieldsHelperInternal', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/internal.php');

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute($app->input->get('task'));
$controller->redirect();
