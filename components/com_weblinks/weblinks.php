<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.DS.'controller.php';
require_once JPATH_COMPONENT.DS.'router.php';
$controller	= JController::getInstance('Weblinks');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();