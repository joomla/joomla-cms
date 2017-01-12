<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$app       = JFactory::getApplication();
$context   = $app->getUserStateFromRequest(
	'com_fields.groups.context',
	'context',
	$app->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD'),
	'CMD'
);

$parts = FieldsHelper::extract($context);

if (!$parts || !JFactory::getUser()->authorise('core.manage', $parts[0]))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute($app->input->get('task'));
$controller->redirect();
