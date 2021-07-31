<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_modules
 *
 * @copyright   (C) 2008 Open Source Matters, Inc. <https://www.joomla.org>
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
