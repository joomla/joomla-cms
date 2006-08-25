<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

define( 'JPATH_COM_CONTACT', dirname( __FILE__ ) );

jimport('joomla.application.extension.component');

require_once( JPATH_COM_CONTACT . '/controller.php' );

// Set the table directory
JTable::addTableDir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_contact'.DS.'tables');

$mParams =& JSiteHelper::getMenuParams();

$viewName	= JRequest::getVar( 'view', $mParams->get( 'view', 'category' ) );
$controller	= new ContactController( 'display' );

$controller->setViewPath ( JPATH_COM_CONTACT . '/views'  );
$controller->setModelPath( JPATH_COM_CONTACT . '/models' );

// get view name from URL or menu params
$controller->setViewName( $viewName, 'ContactView' );

$controller->execute( JRequest::getVar( 'task' ) );
$controller->redirect();