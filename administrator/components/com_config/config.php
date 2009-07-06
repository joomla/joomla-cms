<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_config
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

// Make sure the user is authorized to view this page
$user	= & JFactory::getUser();
$app	= &JFactory::getApplication();
if (!$user->authorize('core.config.manage')) {
	$app->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Tell the browser not to cache this page.
JResponse::setHeader('Expires', 'Mon, 26 Jul 1997 05:00:00 GMT', true);

jimport('joomla.application.component.controller');

// Execute the controller.
$controller = JController::getInstance('Config');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
