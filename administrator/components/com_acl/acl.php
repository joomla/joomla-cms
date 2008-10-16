<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_acl
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once JPATH_COMPONENT.DS.'controller.php';

// Import library dependencies
jimport('joomla.application.component.model');
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

$controller = &AccessController::getInstance();
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
