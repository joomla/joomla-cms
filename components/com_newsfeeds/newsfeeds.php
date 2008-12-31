<?php
/**
* version $Id$
* @package		Joomla
* @subpackage	Newsfeeds
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the com_content helper library
require_once JPATH_COMPONENT.DS.'controller.php';

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

// Create the controller
$controller = new NewsfeedsController();

// Perform the Request task
$controller->execute('');

// Redirect if set by the controller
$controller->redirect();