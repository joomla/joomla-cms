<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Banners
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

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
if (!$user->authorize( 'com_banners', 'manage' )) {
	$mainframe->redirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

// Set the table directory
JTable::addTableDir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'tables');

require_once( JPATH_COMPONENT.DS.'controllers'.DS.'banner.php' );
require_once( JPATH_COMPONENT.DS.'controllers'.DS.'bannerclient.php' );

$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch (JRequest::getVar( 'task' ))
{
	// Banners
	case 'add' :
	case 'edit':
		BannerController::edit();
		break;

	case 'copy':
		BannerController::copy();
		break;

	case 'cancel':
		BannerController::cancel();
		break;

	case 'save':
	case 'resethits':
	case 'apply':
		BannerController::save( $task );
		break;

	case 'remove':
		BannerController::remove();
		break;

	case 'publish':
		BannerController::publish( $cid,1 );
		break;

	case 'unpublish':
		BannerController::publish( $cid, 0 );
		break;

	case 'saveorder':
		BannerController::saveOrder( $cid );
		break;

	// Clients
	case 'newclient':
	case 'editclient':
		BannerClientController::edit();
		break;

	case 'saveclient':
	case 'applyclient':
		BannerClientController::save();
		break;

	case 'removeclients':
		BannerClientController::remove();
		break;

	case 'cancelclient':
		BannerClientController::cancel();
		break;

	case 'listclients':
		BannerClientController::display();
		break;

	// Default
	default:
		BannerController::display();
		break;
}
?>