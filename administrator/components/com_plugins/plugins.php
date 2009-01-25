<?php
/**
* @version		$Id$
* @package		Joomla.Administrator
* @subpackage	Plugins
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!JAcl::authorise('core', 'plugins.manage')) {
	JFactory::getApplication()->redirect('index.php', JText::_('ALERTNOTAUTH'));
}

require_once JPATH_COMPONENT.DS.'controller.php';

// Create the controller
$controller	= new PluginsController();

$controller->execute(JRequest::getCmd('task'));
$controller->redirect();