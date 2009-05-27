<?php
/**
 * @version		$Id: admin.menus.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Administrator
 * @subpackage	Menus
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once(JPATH_COMPONENT.DS.'controller.php');
require_once(JPATH_COMPONENT.DS.'helpers'.DS.'helper.php');

$controller = new MenusController(array('default_task' => 'viewMenus'));
$controller->registerTask('apply', 'save');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();