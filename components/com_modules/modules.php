<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the required admin language files
$lang   = JFactory::getLanguage();
$app    = JFactory::getApplication();
$config = array();
$lang->load('joomla', JPATH_ADMINISTRATOR);
$lang->load('com_modules', JPATH_ADMINISTRATOR);

if ($app->input->get('view') === 'modules' && $app->input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', 'com_modules'))
	{
		$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}
}

if ($app->input->get('task') === 'module.orderPosition')
{
	$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
}

// Trigger the controller
$controller = JControllerLegacy::getInstance('Modules', $config);
$controller->execute($app->input->get('task'));
$controller->redirect();
