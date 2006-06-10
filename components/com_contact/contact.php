<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Contact
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
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

// Load controller class
$controllerType = JRequest::getVar( 'c', 'default', 'post', 'string' );
$controllerType = preg_replace( '#\W#', '', $controllerType );
$controllerPath = JPATH_COM_CONTACT . '/controllers/' . $controllerType . '.php';

if (file_exists( $controllerPath ))
{
	require_once( $controllerPath );
}
else
{
	require( JPATH_COM_CONTACT . '/controllers/default.php' );
}

$controllerName = 'JContactController' . $controllerType;
if (!class_exists( $controllerName ))
{
	require( JPATH_COM_CONTACT . '/controllers/default.php' );
	$controllerName = 'JContactControllerDefault';
}

// Create the controller
$controller = & new $controllerName( $mainframe, 'display' );

// need to tell the controller where to look for views and models
$controller->setViewPath( JPATH_COM_CONTACT . '/views' );
$controller->setModelPath( JPATH_COM_CONTACT . '/models' );

// Register Extra tasks

// Perform the Request task
$controller->execute( $task );

// Redirect if set by the controller
$controller->redirect();
?>