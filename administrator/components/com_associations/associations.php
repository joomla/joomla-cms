<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_associations
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');

if (!JFactory::getUser()->authorise('core.manage', 'com_associations'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('AssociationsHelper', __DIR__ . '/helpers/associations.php');

// Check if user has permission to access the component item type.
$itemtype = JFactory::getApplication()->input->get('itemtype', '', 'string');

if ($itemtype !== '')
{
	list($extensionName, $typeName) = explode('.', $itemtype);

	if (!AssociationsHelper::hasSupport($extensionName))
	{
		throw new Exception(JText::sprintf('COM_ASSOCIATIONS_COMPONENT_NOT_SUPPORTED', JText::_($extensionName)), 404);
	}

	if (!JFactory::getUser()->authorise('core.manage', $extensionName))
	{
		throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
	}
}

$controller = JControllerLegacy::getInstance('Associations');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
