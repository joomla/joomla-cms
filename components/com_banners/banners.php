<?php
/**
 * @version		$Id$
 * @package  Joomla
 * @subpackage	Banners
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the com_content helper library
require_once JPATH_COMPONENT.DS.'controller.php';

// Create the controller
$controller = new BannersController( array('default_task' => 'click') );

// Perform the Request task
$controller->execute(JRequest::getVar('task', null, 'default', 'cmd'));

// Redirect if set by the controller
$controller->redirect();