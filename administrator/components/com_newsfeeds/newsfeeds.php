<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_newsfeeds
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

$user = & JFactory::getUser();
if (!$user->authorize('com_newsfeeds.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}
//echo JRequest::getCmd('task');exit;
jimport('joomla.application.component.controller');
$controller	= JController::getInstance('Newsfeeds');

$controller->execute(JRequest::getCmd('task'));
$controller->redirect();