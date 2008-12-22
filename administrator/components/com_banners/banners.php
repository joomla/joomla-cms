<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!JAcl::authorise('com_banners', 'banners.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

$controllerName = JRequest::getCmd('c', 'banner');

if ($controllerName == 'client') {
	JSubMenuHelper::addEntry(JText::_('Banners'), 'index.php?option=com_banners');
	JSubMenuHelper::addEntry(JText::_('Clients'), 'index.php?option=com_banners&c=client', true);
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_categories&section=com_banner');
} else {
	JSubMenuHelper::addEntry(JText::_('Banners'), 'index.php?option=com_banners', true);
	JSubMenuHelper::addEntry(JText::_('Clients'), 'index.php?option=com_banners&c=client');
	JSubMenuHelper::addEntry(JText::_('Categories'), 'index.php?option=com_categories&section=com_banner');
}

switch ($controllerName)
{
	default:
		$controllerName = 'banner';
		// allow fall through

	case 'banner' :
	case 'client':
		// Temporary interceptor
		$task = JRequest::getCmd('task');
		if ($task == 'listclients') {
			$controllerName = 'client';
		}

		require_once JPATH_COMPONENT.DS.'controllers'.DS.$controllerName.'.php';
		$controllerName = 'BannerController'.$controllerName;

		// Create the controller
		$controller = new $controllerName();

		// Perform the Request task
		$controller->execute(JRequest::getCmd('task'));

		// Redirect if set by the controller
		$controller->redirect();
		break;
}