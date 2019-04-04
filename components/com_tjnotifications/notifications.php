<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_tjnotification
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

$controller = JControllerLegacy::getInstance('Notifications');

// Perform the Request task

$input = JFactory::getApplication()->input;
$controller->execute($input->getCmd('task'));

// Redirect if set by the controller

$controller->redirect();
