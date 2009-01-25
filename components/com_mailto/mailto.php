<?php
/**
 * @version		$Id$
 * @package		Joomla
 * @subpackage	MailTo
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.helper');

require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= new MailtoController( );
$controller->registerDefaultTask('mailto');
$controller->execute(JRequest::getCmd('task'));

//$controller->redirect();