<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_members
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @copyright	Copyright (C) 2008 - 2009 JXtended, LLC. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

defined('_JEXEC') or die('Restricted Access');

require_once JPATH_COMPONENT.DS.'controller.php';
jimport('joomla.application.component.controller');

// Launch the controller.
$controller = &JController::getInstance('Members');
$controller->execute(JRequest::getCmd('task', 'display'));
$controller->redirect();