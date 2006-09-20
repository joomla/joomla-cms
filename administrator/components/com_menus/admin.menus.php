<?php
/**
 * @version $Id$
 * @package Joomla
 * @subpackage Menus
 * @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights
 * reserved.
 * @license GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant to the
 * GNU General Public License, and as distributed it includes or is derivative
 * of works licensed under the GNU General Public License or other free or open
 * source software licenses. See COPYRIGHT.php for copyright notices and
 * details.
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

define( 'COM_MENUS', dirname( __FILE__ ) . DS );
require_once( COM_MENUS . 'controller.php' );
require_once( COM_MENUS . 'helper.php' );

$task = JRequest::getVar( 'task' );

$controller = new JMenuController( 'viewList' );
$controller->setModelPath( COM_MENUS.'models'.DS );
$controller->setViewPath( COM_MENUS.'views'.DS );
$controller->registerTask('apply', 'save');
$controller->execute( $task );
$controller->redirect();
?>