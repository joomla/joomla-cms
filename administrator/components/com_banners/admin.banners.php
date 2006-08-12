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

define( 'JPATH_COM_BANNERS', dirname( __FILE__ ));

// Make sure the user is authorized to view this page
$user = & JFactory::getUser();
if (!$user->authorize( 'com_banners', 'manage' )) {
	$mainframe->redirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

// Set the table directory
JTable::addTableDir(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_banners'.DS.'tables');

require_once( JPATH_COM_BANNERS . '/controllers/banner.php' );
require_once( JPATH_COM_BANNERS . '/controllers/bannerclient.php' );

$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch (JRequest::getVar( 'task' )) 
{
	// Banners
	case 'new':
	case 'edit':
		BannerController::edit( );
		break;

	case 'cancel':
		BannerController::cancelEditBanner();
		break;

	case 'save':
	case 'resethits':
	case 'apply':
		BannerController::saveBanner( $task );
		break;

	case 'remove':
		BannerController::removeBanner( $cid );
		break;

	case 'publish':
		BannerController::publishBanner( $cid,1 );
		break;

	case 'unpublish':
		BannerController::publishBanner( $cid, 0 );
		break;

	case 'saveorder':
		BannerController::saveOrder( $cid );
		break;

	// Clients
	case 'newclient':
	case 'editclient':
		BannerClientController::editBannerClient( );
		break;

	case 'saveclient':
	case 'applyclient':
		BannerClientController::saveBannerClient( $task );
		break;

	case 'removeclients':
		BannerClientController::removeBannerClients( $cid, $option );
		break;

	case 'cancelclient':
		BannerClientController::cancelEditClient( $option );
		break;

	case 'listclients':
		BannerClientController::viewBannerClients( $option );
		break;

	// Default
	default:
		BannerController::viewBanners( $option );
		break;
}
?>