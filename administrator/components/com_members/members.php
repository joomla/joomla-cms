<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Invalid Request.');

require_once(JPATH_COMPONENT.DS.'controller.php');

// Execute the task.
$controller	= &JController::getInstance('Members');
$controller->execute(JRequest::getVar('task'));
$controller->redirect();
