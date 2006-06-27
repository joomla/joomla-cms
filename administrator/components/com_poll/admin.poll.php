<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Polls
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_poll', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

define( 'JPATH_COM_POLL', dirname( __FILE__ ));

require_once( JPATH_COM_POLL . '/controllers/index.php' );
require_once( JPATH_COM_POLL . '/views/index.php' );

require_once( JApplicationHelper::getPath( 'class' ) );

$controller = new JPollGlobalController( $mainframe, 'showPolls' );

$controller->performTask( JRequest::getVar( 'task' ) );
$controller->redirect();

?>
