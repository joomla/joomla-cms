<?php
/**
* Community Builder (TM)
* @version $Id: $
* @package CommunityBuilder
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

use CBLib\Language\CBTxt;

if ( ! ( defined( '_VALID_CB' ) || defined( '_JEXEC' ) || defined( '_VALID_MOS' ) ) ) { die( 'Direct Access to this location is not allowed.' ); }

global $_CB_database, $_CB_framework, $_PLUGINS, $ueConfig;

if ( ( ! file_exists( JPATH_SITE . '/libraries/CBLib/CBLib/Core/CBLib.php' ) ) || ( ! file_exists( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' ) ) ) {
	echo 'CB not installed!';
	return;
}

/** @noinspection PhpIncludeInspection */
include_once( JPATH_ADMINISTRATOR . '/components/com_comprofiler/plugin.foundation.php' );

if ( ! defined( 'CBLIB' ) ) {
	echo 'CB version is not 2.0.0+';
	return;
}

cbimport( 'cb.html' );

outputCbTemplate();

require_once( dirname( __FILE__ ) . '/helper.php' );

$cbUser								=	CBuser::getMyInstance();
$user								=	$cbUser->getUserData();
$disabled							=	( (int) cbGetParam( $_REQUEST, 'hidemainmenu' ) ? true : false );
$mode								=	(int) $params->get( 'mode', 1 );
$feedEntries						=	(int) $params->get( 'feed_entries', 5 );
$feedDuration						=	(int) $params->get( 'feed_duration', 12 );
$modalDisplay						=	(int) $params->get( 'modal_display', 1 );
$modalWidth							=	$params->get( 'modal_width', '90%' );
$modalHeight						=	$params->get( 'modal_height', '90vh' );

if ( ! $modalWidth ) {
	$modalWidth						=	'90%';
}

if ( ! $modalHeight ) {
	$modalHeight					=	'90vh';
}

$_CB_framework->document->addHeadStyleSheet( $_CB_framework->getCfg( 'live_site' ) . '/administrator/modules/mod_cbadmin/mod_cbadmin.css' );

if ( in_array( $mode, array( 3, 4 ) ) ) {
	static $JS1_loaded				=	0;

	if ( ! $JS1_loaded++ ) {
		$js							=	"$( '.cbFeedShowMore,.cbFeedShowMoreLink' ).click( function() {"
									.		"var more = $( this ).nextUntil( '.cbFeedShowMore,.cbFeedShowMoreLink' );"
									.		"more.fadeIn().removeClass( 'cbFeedItemDisabled' );"
									.		"more.next( '.cbFeedShowMore,.cbFeedShowMoreLink' ).show();"
									.		"$( this ).remove();"
									.	"});"
									.	"$( '.cbFeed' ).each( function() {"
									.		"$( this ).find( '.cbFeedShowMore,.cbFeedShowMoreLink' ).first().show();"
									.	"});";

		$_CB_framework->outputCbJQuery( $js );
	}
}

switch ( $mode ) {
	case 5:
		$cbVersion					=	modCBAdminHelper::getLatestCBVersion();
		$messages					=	array();

		if ( $cbVersion && ( version_compare( $cbVersion, $ueConfig['version'] ) > 0 ) ) {
			modCBAdminHelper::enableUpdateSite();

			$latestVersion			=	'<span class="cbUpdateVersion label label-danger">' . $cbVersion . '</span>';

			$learnButton			=	'<a href="https://www.joomlapolis.com/?pk_campaign=in-cb&amp;pk_kwd=admin-module-update-button" target="_blank"><button class="btn btn-primary cbLearnButton">' . CBTxt::T( 'Learn More' ) . '</button></a>';

			/** @noinspection HtmlUnknownTarget */
			$updateButton			=	'<a href="index.php?option=com_installer&amp;view=update"><button class="btn btn-primary cbUpdateButton">' . CBTxt::T( 'Update Now' ) . '</button></a>';

			$messages[]				=	'<div class="cbUpdateNotification alert alert-danger">'
									.			CBTxt::T( 'COMMUNITY_BUILDER_VERSION_VERSION_IS_AVAILABLE_BUTTON', 'Community Builder version [version] is available: [learn_button] [update_button]', array( '[version]' => $latestVersion, '[learn_button]' => $learnButton, '[update_button]' => $updateButton ) )
									.	'</div>';
		}

		if ( get_magic_quotes_gpc() ) {
			$phpFunction			=	'<span class="cbDisableFunction label label-danger">magic_quotes_gpc</span>';

			$tutorialButton			=	'<a href="http://docs.joomla.org/How_to_turn_off_magic_quotes_gpc_for_Joomla_3" target="_blank"><button class="btn btn-primary btn-sm cbDisableFunctionButton">' . CBTxt::T( 'Please click here for instructions.' ) . '</button></a>';

			$messages[]				=	'<div class="cbDisableFunctionNotification alert alert-danger">'
									.			CBTxt::T( 'YOUR_HOST_DISABLE_FUNCTION_FOR_CB', 'Your host needs to disable [function] to run this version of Community Builder! [button]', array( '[function]' => $phpFunction, '[button]' => $tutorialButton ) )
									.	'</div>';
		}

		if ( $messages ) {
			$notification			=	'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
									.		implode( '', $messages )
									.	'</div>';

			$_CB_framework->outputCbJQuery( "$( '#system-message-container' ).append( '" . addslashes( $notification ) . "' );" );
		}
		break;
	case 4:
		static $items				=	null;

		if ( ! isset( $items ) ) {
			$items					=	array();
			$plugins				=	array();

			$query					=	'SELECT *'
									.	"\n FROM " . $_CB_database->NameQuote( '#__comprofiler_plugin' );
			$_CB_database->setQuery( $query );
			$rows					=	$_CB_database->loadObjectList();

			if ( $rows ) foreach ( $rows as $row ) {
				$rowVer				=	$_PLUGINS->getPluginVersion( $row, 2, $feedDuration );

				if ( $rowVer[2] === false ) {
					$items[]		=	array( $row, $rowVer, $_PLUGINS->checkPluginCompatibility( $row ), false );
					$plugins[]		=	(int) $row->id;
				}

				if ( ! in_array( $row->id, $plugins ) ) {
					if ( ! $_PLUGINS->checkPluginCompatibility( $row ) ) {
						$items[]	=	array( $row, $rowVer, false, true );
					}
				}
			}
		}

		/** @noinspection PhpIncludeInspection */
		require( JModuleHelper::getLayoutPath( 'mod_cbadmin', 'updates' ) );
		break;
	case 3:
		static $JS2_loaded			=	0;

		if ( ! $JS2_loaded++ ) {
			$js						=	"cbFeedShow = function( element, settings, event, api ) {"
									.		"$( api.elements.target ).addClass( 'cbFeedItemActive' );"
									.	"};"
									.	"cbFeedHide = function( element, settings, event, api ) {"
									.		"$( api.elements.target ).removeClass( 'cbFeedItemActive' );"
									.	"};";

			$_CB_framework->outputCbJQuery( $js );
		}

		$xml						=	modCBAdminHelper::getFeedXML( 'http://www.joomlapolis.com/news?format=feed&type=rss', 'cbnewsfeed.xml', $feedDuration );

		if ( $xml ) {
			$items					=	$xml->xpath( '//channel/item' );

			/** @noinspection PhpIncludeInspection */
			require( JModuleHelper::getLayoutPath( 'mod_cbadmin', 'news' ) );
		}
		break;
	case 2:
	case 1:
	default:
		$menu						=	array();

		/** @noinspection PhpIncludeInspection */
		require( JModuleHelper::getLayoutPath( 'mod_cbadmin', 'menu' ) );

		if ( $mode == 2 ) {
			$return					=	'<div class="cb_template cb_template_' . selectTemplate( 'dir' ) . '">'
									.		modCBAdminHelper::getTable( $menu, $disabled )
									.	'</div>';

			echo $return;
		} else {
			echo modCBAdminHelper::getMenu( $menu, $disabled );
		}
		break;
}
