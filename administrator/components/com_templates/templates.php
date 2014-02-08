<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

$app  = JFactory::getApplication();
$user = JFactory::getUser();

// ACL for hardening the access to the template manager.
if (!$user->authorise('core.manage', 'com_templates')
	|| !$user->authorise('core.edit', 'com_templates')
	|| !$user->authorise('core.create', 'com_templates')
	|| !$user->authorise('core.admin', 'com_templates'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

JLoader::register('TemplatesHelper', __DIR__ . '/helpers/templates.php');

$controller	= JControllerLegacy::getInstance('Templates');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
