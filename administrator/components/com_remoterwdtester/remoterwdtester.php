<?php
/**
 * @version     1.0.0
 * @package     com_remoterwdtester
 * @copyright   Copyright (C) Joostrap 2013. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Philip Locke <fastnetwebdesign@gmail.com> - http://www.joostrap.com
 */


// no direct access
defined('_JEXEC') or die;

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_remoterwdtester')) 
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

// Include dependancies
jimport('joomla.application.component.controller');

$controller	= JControllerLegacy::getInstance('Remoterwdtester');
$controller->execute(JFactory::getApplication()->input->get('task'));
$controller->redirect();
