<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 * @since		1.5
 */

defined('_JEXEC') or die;

jimport('joomla.application.component.controller');
require_once JPATH_COMPONENT.'/helpers/route.php';

// Launch the controller.
$controller = JController::getInstance('Users');
$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();