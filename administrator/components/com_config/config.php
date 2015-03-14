<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_config
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::_('behavior.tabstate');

// Load classes
JLoader::registerPrefix('Config', JPATH_ADMINISTRATOR . '/components/com_config/');
JLoader::registerPrefix('Config', JPATH_SITE . '/components/com_config/');


// Get construction params
$app = JFactory::getApplication();
$input = $app->input;
$config = array('default_view' => 'application', 'layout' => 'form');

$controller = new ConfigController($input, $app, $config);
$controller->execute();
$controller->redirect();
