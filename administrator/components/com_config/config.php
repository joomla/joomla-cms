<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
  */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$user = & JFactory::getUser();
if (!$user->authorize('core.config.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

jimport('joomla.application.component.controller');

JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

$controller	= JController::getInstance('Config');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();