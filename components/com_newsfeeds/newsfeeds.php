<?php
/**
* version $Id$
 * @package		Joomla.Site
 * @subpackage	Newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Require the com_content helper library
require_once (JPATH_COMPONENT.DS.'controller.php');

JTable::addIncludePath(JPATH_COMPONENT_ADMINISTRATOR.DS.'tables');

// Create the controller
$controller = new NewsfeedsController();

// Perform the Request task
$controller->execute('');

// Redirect if set by the controller
$controller->redirect();

?>