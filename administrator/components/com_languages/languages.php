<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	com_languages
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// no direct access
defined('_JEXEC') or die;

$user = & JFactory::getUser();
if (!$user->authorize('core.languages.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

jimport('joomla.application.component.controller');

$controller	= JController::getInstance('Languages');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();
