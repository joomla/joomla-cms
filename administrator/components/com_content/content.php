<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.manage', 'com_content'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

JLoader::register('ContentHelper', __DIR__ . '/helpers/content.php');

$controller = JControllerLegacy::getInstance('Content');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
