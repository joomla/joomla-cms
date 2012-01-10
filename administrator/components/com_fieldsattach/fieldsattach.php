<?php

/**
 * @version		$Id: fieldsattach.php 15 2011-09-02 18:37:15Z cristian $
 * @package		fieldsattach
 * @subpackage		Components
 * @copyright		Copyright (C) 2011 - 2020 Open Source Cristian Grañó, Inc. All rights reserved.
 * @author		Cristian Grañó
 * @link		http://joomlacode.org/gf/project/fieldsattach_1_6/
 * @license		License GNU General Public License version 2 or later
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// Access check.
if (!JFactory::getUser()->authorise('core.manage', 'com_fieldsattach')) 
{
	return JError::raiseWarning(404, JText::_('JERROR_ALERTNOAUTHOR'));
}

if(JRequest::getVar("view") == "fieldsattachimages" || JRequest::getVar("view") == "fieldsattachimage") JRequest::setVar('tmpl','component');
// require helper file
JLoader::register('fieldsattachHelper', dirname(__FILE__) . DS . 'helpers' . DS . 'fieldsattach.php');

/*
$document = &JFactory::getDocument();
$document->addStyleSheet('administrator/components/com_fieldsattach/css/Jquery.jquery.Jcrop.css');
$document->addScript( 'administrator/components/com_fieldsattach/css/jquery.Jcrop.js' );
$document->addScript( 'administrator/components/com_fieldsattach/css/Jquery.attachfield.js' );
 *
 * 
 */
              

// import joomla controller library
jimport('joomla.application.component.controller');
 

// Get an instance of the controller prefixed by fieldsattach
$controller = JController::getInstance('fieldsattach'); 

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();
