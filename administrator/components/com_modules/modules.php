<?php
/**
* @version		$Id$
* @package		Joomla
* @subpackage	Modules
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!JAcl::authorise('core', 'modules.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

// Helper classes
JHtml::addIncludePath(JPATH_COMPONENT.DS.'classes');

// Require the base controller
require_once JPATH_COMPONENT.DS.'controller.php';

$controller	= new ModulesController();

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));
$controller->redirect();