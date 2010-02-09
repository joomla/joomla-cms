<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');

// Execute the task.
$controller	= &JController::getInstance('Languages');
$controller->execute(JRequest::getVar('task','select'));
$controller->redirect();
