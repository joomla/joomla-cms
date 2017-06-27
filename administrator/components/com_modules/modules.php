<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.tabstate');

$user  = JFactory::getUser();
$input = JFactory::getApplication()->input;

if (($input->get('layout') !== 'modal' && $input->get('view') !== 'modules')
	&& !$user->authorise('core.manage', 'com_modules'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller = JControllerLegacy::getInstance('Modules');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
