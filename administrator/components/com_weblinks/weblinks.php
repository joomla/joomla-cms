<?php
/**
 * @version		$Id$
 * @package		Joomla.Administrator
 * @subpackage	Weblinks
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!JAcl::authorise('com_weblinks', 'weblinks.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= JController::getInstance('Weblinks');
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();