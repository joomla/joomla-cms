<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Admin
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= JController::getInstance('Admin');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();