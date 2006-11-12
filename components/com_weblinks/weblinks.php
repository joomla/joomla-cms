<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Weblinks
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

// Require the com_content helper library
require_once (JPATH_COMPONENT . '/controller.php');

$document =& JFactory::getDocument();
JTable::addTableDir(JPATH_BASE.DS.'administrator'.DS.'components'.DS.'com_weblinks'.DS.'tables');
// Create the controller
$controller = new WeblinksController( );

// Set the default view name from the Request
$viewName = JRequest::getVar( 'view' );
$viewType = $document->getType();

$controller->setViewName( $viewName, 'WeblinksView', $viewType );

// Perform the Request task
$controller->execute( JRequest::getVar('task'));

// Redirect if set by the controller
$controller->redirect();

?>