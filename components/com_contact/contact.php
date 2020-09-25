<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JLoader::register('ContactHelperRoute', JPATH_COMPONENT . '/helpers/route.php');

$input = JFactory::getApplication()->input;

if ($input->get('view') === 'contacts' && $input->get('layout') === 'modal')
{
	if (!JFactory::getUser()->authorise('core.create', 'com_contact'))
	{
		JFactory::getApplication()->enqueueMessage(JText::_('JERROR_ALERTNOAUTHOR'), 'warning');

		return;
	}

	JFactory::getLanguage()->load('com_contact', JPATH_ADMINISTRATOR);
}

$controller = JControllerLegacy::getInstance('Contact');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
