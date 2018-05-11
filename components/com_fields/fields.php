<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_fields
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('_JEXEC') or die;

JLoader::register('FieldsHelper', JPATH_ADMINISTRATOR . '/components/com_fields/helpers/fields.php');

$input   = JFactory::getApplication()->input;
$context = JFactory::getApplication()->getUserStateFromRequest('com_fields.fields.context', 'context', 'com_content.article', 'CMD');
$parts   = FieldsHelper::extract($context);

if ($input->get('view') === 'fields' && $input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', $parts[0])
		|| !JFactory::getUser()->authorise('core.edit', $parts[0]))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

		return;
	}
}

$controller = JControllerLegacy::getInstance('Fields');
$controller->execute($input->get('task'));
$controller->redirect();
