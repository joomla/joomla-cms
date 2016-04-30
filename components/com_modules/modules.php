<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_modules
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the required admin language files
$lang = JFactory::getLanguage();
$lang->load('joomla', JPATH_ADMINISTRATOR);
$lang->load('com_modules', JPATH_ADMINISTRATOR);

// Trigger the controller
$controller = JControllerLegacy::getInstance('Modules');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
