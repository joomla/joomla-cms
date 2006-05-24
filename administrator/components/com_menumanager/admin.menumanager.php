<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Menus
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

//
// WARNING
// com_menumanager uses new techniques for component structure
// Third party developers should consult with Joomla! before
// using a similar structure to avoid compatibility issues
// with future versions
//

define( 'COM_MENUMANAGER', dirname( __FILE__ ) );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_menumanager', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( COM_MENUMANAGER . '/controller.php' );
require_once( COM_MENUMANAGER . '/model.php' );
require_once( COM_MENUMANAGER . '/views.php' );

$controller = new MenuTypeController( 'listItems' );
$controller->registerTask( 'new', 'edit' );
$controller->registerTask( 'deleteconfirm', 'delete' );

$controller->performTask( JRequest::getVar( 'task' ) );
$controller->redirect();
?>