<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_menus
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Load the required admin language files
$lang   = JFactory::getLanguage();
$app    = JFactory::getApplication();
$config = array();

$lang->load('joomla', JPATH_ADMINISTRATOR);
$lang->load('com_menus', JPATH_ADMINISTRATOR);

// Trigger the controller
$controller = JControllerLegacy::getInstance('Menus', $config);
$controller->execute($app->input->get('task'));
$controller->redirect();
