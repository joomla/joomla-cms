<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_categories
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$input = JFactory::getApplication()->input;

if (!JFactory::getUser()->authorise('core.manage', 'com_tags'))
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

//JLoader::register('JHtmlTagsAdministrator', JPATH_ADMINISTRATOR . '/components/com_categories/helpers/categoriesadministrator.php');

$task = $input->get('task');

$controller	= JControllerLegacy::getInstance('Tags');
$controller->execute($input->get('task'));
$controller->redirect();
