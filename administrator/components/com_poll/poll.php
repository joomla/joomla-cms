<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Polls
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!JAcl::authorise('com_poll', 'poll.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once JPATH_COMPONENT.DS.'controller.php';

// Set the table directory
JTable::addIncludePath(JPATH_COMPONENT.DS.'tables');

// Create the controller
$controller	= new PollController();

$controller->execute(JRequest::getCmd( 'task' ) );
$controller->redirect();