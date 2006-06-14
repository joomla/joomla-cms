<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage MailTo
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

define('JPATH_COM_MAILTO', dirname( __FILE__ ));

jimport('joomla.application.extension.component');

require_once( JPATH_COM_MAILTO . '/controller.php' );

$mParams	= JComponentHelper::getMenuParams();
$view		= JRequest::getVar( 'view', $mParams->get( 'view', 'default' ) );
$controller	= new mailtoController( $mainframe, 'display' );

$controller->setViewPath( JPATH_COM_MAILTO . '/views' );

// get view name from URL or menu params
$controller->setViewName( $view, 'com_mailto', 'JViewMailTo' );
$controller->setVar( 'mParams', $mParams );

$controller->execute( JRequest::getVar( 'task' ) );
$controller->redirect();
?>