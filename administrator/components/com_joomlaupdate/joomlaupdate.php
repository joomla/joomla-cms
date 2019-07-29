<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_joomlaupdate
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

if (!JFactory::getUser()->authorise('core.admin'))
{
	throw new JAccessExceptionNotallowed(JText::_('JERROR_ALERTNOAUTHOR'), 403);
}

$controller = JControllerLegacy::getInstance('Joomlaupdate');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
