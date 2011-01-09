<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	MailTo
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
jimport('joomla.application.component.helper');

require_once JPATH_COMPONENT.DS.'controller.php';

$controller = JController::getInstance('Mailto');
$controller->registerDefaultTask('mailto');
$controller->execute(JRequest::getCmd('task'));

//$controller->redirect();