<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Search
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Require the com_content helper library
require_once JPATH_COMPONENT.DS.'controller.php';

// Create the controller
$controller = new SearchController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();