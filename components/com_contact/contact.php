<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$config = array();
$input = JFactory::getApplication()->input;

if ($input->get('view') === 'contacts' && $input->get('layout') === 'modal')
{
	$config['base_path'] = JPATH_COMPONENT_ADMINISTRATOR;
	$lang   = JFactory::getLanguage();
	$lang->load('joomla', JPATH_ADMINISTRATOR);
	$lang->load('com_contact', JPATH_ADMINISTRATOR);
}
else
{
	JLoader::register('ContactHelperRoute', JPATH_COMPONENT . '/helpers/route.php');
}

$controller = JControllerLegacy::getInstance('Contact', $config);
$controller->execute($input->get('task'));
$controller->redirect();
