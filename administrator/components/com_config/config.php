<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Access checks are done internally because of different requirements for the two controllers.

// Include dependancies
jimport('joomla.application.component.controller');

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

// Execute the controller.
$controller = JController::getInstance('Config');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
