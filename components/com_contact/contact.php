<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Controller\Controller;
use Joomla\CMS\Factory;

defined('_JEXEC') or die;

JLoader::register('ContactHelperRoute', JPATH_COMPONENT . '/helpers/route.php');

$input = Factory::getApplication()->input;

if ($input->get('view') === 'contacts' && $input->get('layout') === 'modal')
{
	if (!Factory::getUser()->authorise('core.create', 'com_contact'))
	{
		Factory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}

	$lang = Factory::getLanguage();
	$lang->load('com_contact', JPATH_ADMINISTRATOR);
}

$controller = Controller::getInstance('Contact');
$controller->execute(Factory::getApplication()->input->get('task'));
$controller->redirect();
