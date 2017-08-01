<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the required admin language files
/** @var \Joomla\CMS\Language\Language $lang */
$lang   = JFactory::getLanguage();
$app    = JFactory::getApplication();

if ($app->input->get('view') === 'items' && $app->input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', 'com_menus'))
	{
		$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');
		return;
	}
}

$lang->load('joomla', JPATH_ADMINISTRATOR, null, false, true);
$lang->load('com_menus', JPATH_ADMINISTRATOR, null, false, true);

// Trigger the controller
$controller = JControllerLegacy::getInstance('Menus');
$controller->execute($app->input->get('task'));
$controller->redirect();
