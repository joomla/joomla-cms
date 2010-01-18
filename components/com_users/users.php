<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

require_once JPATH_COMPONENT.'/controller.php';
require_once JPATH_COMPONENT.'/helpers/route.php';

// Launch the controller.
$controller = &JController::getInstance('Users');
$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();