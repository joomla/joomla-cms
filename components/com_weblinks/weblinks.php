<?php
/**
 * @version		$Id: weblinks.php 10381 2008-06-01 03:35:53Z pasamio $
 * @package		Joomla.Site
 * @subpackage	com_weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= JController::getInstance('Weblinks');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
