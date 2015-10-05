<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_templates
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

$app  = JFactory::getApplication();
$user = JFactory::getUser();

if (!$user->authorise('core.manage', 'com_templates'))
{
	$app->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'error');

	return false;
}

JLoader::register('TemplatesHelper', __DIR__ . '/helpers/templates.php');

$controller = JControllerLegacy::getInstance('Templates');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
