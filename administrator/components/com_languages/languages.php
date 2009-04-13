<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Languages
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$user = & JFactory::getUser();
if (!$user->authorize('core.languages.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= new LanguagesController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();