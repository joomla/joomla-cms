<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_banners
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Execute the task.
$controller	= JControllerLegacy::getInstance('Banners');
$controller->execute(JRequest::getVar('task', 'click'));
$controller->redirect();
