<?php
/**
 * @version		$Id: content.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	Content
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

// Require the com_content helper library
require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'query.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'route.php');

// Component Helper
jimport('joomla.application.component.helper');

// Create the controller
$controller = new ContentController();

// Register Extra tasks
$controller->registerTask('new'  , 	'edit');
$controller->registerTask('apply', 	'save');
$controller->registerTask('apply_new', 'save');

// Perform the Request task
$controller->execute(JRequest::getVar('task', null, 'default', 'cmd'));
$controller->redirect();
