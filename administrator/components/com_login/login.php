<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Login
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= new LoginController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();