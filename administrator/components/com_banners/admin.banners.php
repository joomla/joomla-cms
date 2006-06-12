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

/*
 * Make sure the user is authorized to view this page
 */
$user = & $mainframe->getUser();
if (!$user->authorize( 'com_banners', 'manage' ))
{
	josRedirect( 'index2.php', JText::_('ALERTNOTAUTH') );
}

require_once( JPATH_COM_BANNERS . '/controllers/banner.php' );
require_once( JPATH_COM_BANNERS . '/controllers/client.php' );
require_once( JPATH_COM_BANNERS . '/views/banner.php' );
require_once( JPATH_COM_BANNERS . '/views/client.php' );

require_once( JApplicationHelper::getPath( 'class' ) );

$cid = JRequest::getVar( 'cid', array(0), '', 'array' );
if (!is_array( $cid )) {
	$cid = array(0);
}

switch ($task) {
	case 'new':
	case 'edit':
		JBannerController::edit( );
		break;	

	case 'cancel':
		JBannerController::cancelEditBanner();
		break;

	case 'save':
	case 'resethits':
	case 'apply':
		JBannerController::saveBanner( $task );
		break;

	case 'remove':
		JBannerController::removeBanner( $cid );
		break;

	case 'publish':
		JBannerController::publishBanner( $cid,1 );
		break;

	case 'unpublish':
		JBannerController::publishBanner( $cid, 0 );
		break;

// Clients

	case 'newclient':
	case 'editclient':
		JBannerClientController::editBannerClient( );
		break;

	case 'saveclient':
	case 'applyclient':
		JBannerClientController::saveBannerClient( $task );
		break;

	case 'removeclients':
		JBannerClientController::removeBannerClients( $cid, $option );
		break;

	case 'cancelclient':
		JBannerClientController::cancelEditClient( $option );
		break;

	case 'listclients':
		JBannerClientController::viewBannerClients( $option );
		break;

// Default

	default:
		JBannerController::viewBanners( $option );
		break;
}
?>