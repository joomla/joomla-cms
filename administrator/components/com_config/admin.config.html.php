<?php
/**
* @version $Id: admin.config.html.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Joomla
* @subpackage Config
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
 * @package Joomla
 * @subpackage Config
 */
class configScreens {
	/**
	 * @param string The main template file to include for output
	 * @param array An array of other standard files to include
	 * @return patTemplate A template object
	 */
	function &createTemplate( $bodyHtml, $files=null) {
		$tmpl =& mosFactory::getPatTemplate( $files );
		$tmpl->setRoot( dirname( __FILE__ ) . '/tmpl' );
		$tmpl->setAttribute( 'body', 'src', $bodyHtml );

		return $tmpl;
	}

	function edit( &$row, &$lists, &$vars ) {
		global $mosConfig_absolute_path, $_LANG;
		$tmpl =& configScreens::createTemplate( 'edit.html', array( 'forms.html' ) );
		$tmpl->readTemplatesFromInput( 'fso_permissions.html' );

		$tmpl->addObject( 'body', $row );

		// file permissions
		$permMasks = array(
			'0400' => 0400,
			'0200' => 0200,
			'0100' => 0100,
			'040' => 040,
			'020' => 020,
			'010' => 010,
			'04' => 04,
			'02' => 02,
			'01' => 01,
		);

		$flags = $row->config_fileperms != '' ? octdec( $row->config_fileperms ) : 0644;
		foreach ($permMasks as $k => $v) {
			if ($flags & $v) {
				$tmpl->addVar( 'fso-perms', $k, 'checked="checked"' );
			}
		}
		$tmpl->addVar( 'fso-perms', 'fso', 'file' );
		$tmpl->addVar( 'fso-perms', 'perm_value', $row->config_fileperms );
		if ($row->config_fileperms == '') {
			$tmpl->addVar( 'fso-perms', 'mode0checked', 'checked="true"' );
			$tmpl->addVar( 'fso-perms', 'mode0style', 'style="display:none"' );
		} else {
			$tmpl->addVar( 'fso-perms', 'mode1checked', 'checked="true"' );
			$tmpl->addVar( 'fso-perms', 'mode1style', 'style="display:none"' );
		}
		$tmpl->parseIntoVar( 'fso-perms', 'body', 'file_perms' );
		$tmpl->clearVars( 'fso-perms' );

		$flags = $row->config_fileperms != '' ? octdec( $row->config_dirperms ) : 0775;
		foreach ($permMasks as $k => $v) {
			if ($flags & $v) {
				$tmpl->addVar( 'fso-perms', $k, 'checked="checked"' );
			}
		}
		$tmpl->addVar( 'fso-perms', 'fso', 'dir' );
		$tmpl->addVar( 'fso-perms', 'perm_value', $row->config_dirperms );
		if ($row->config_dirperms == '') {
			$tmpl->addVar( 'fso-perms', 'mode0checked', 'checked="true"' );
			$tmpl->addVar( 'fso-perms', 'mode0style', 'style="display:none"' );
		} else {
			$tmpl->addVar( 'fso-perms', 'mode1checked', 'checked="true"' );
			$tmpl->addVar( 'fso-perms', 'mode1style', 'style="display:none"' );
		}
		$tmpl->parseIntoVar( 'fso-perms', 'body', 'folder_perms' );

		// SITE

		patHTML::yesNoRadio( $tmpl, 'body', 'config_offline', $row->config_offline, 'config_offline' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_edit_popup', $row->config_offline, 'config_edit_popup' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_internal_templates', $row->config_internal_templates, 'config_internal_templates' );

		patHTML::selectArray( $lists['editor'], $row->config_editor );
		$tmpl->addObject( 'list-editor', $lists['editor'] );

		patHTML::selectArray( $lists['list_limit'], $row->config_list_limit );
		$tmpl->addObject( 'list-list_limit', $lists['list_limit'] );

		patHTML::selectArray( $lists['live_bookmark'], $row->config_live_bookmark );
		$tmpl->addObject( 'list-live_bookmark', $lists['live_bookmark'] );

		patHTML::selectArray( $lists['live_bookmark_show'], $row->config_live_bookmark_show );
		$tmpl->addObject( 'list-live_bookmark_show', $lists['live_bookmark_show'] );

		// DEBUG
		patHTML::yesNoRadio( $tmpl, 'body', 'config_debug', $row->config_debug, 'config_debug' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_debug_db', $row->config_debug_db, 'config_debug_db' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_debug_dblog', $row->config_debug_dblog, 'config_debug_dblog' );

		// DATABASE

		patHTML::selectArray( $lists['dbtype'], $row->config_dbtype );
		$tmpl->addObject( 'list-dbtype', $lists['dbtype'] );

		// SERVER
		patHTML::yesNoRadio( $tmpl, 'body', 'config_gzip', $row->config_gzip, 'config_gzip' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_savestate', $row->config_savestate, 'config_savestate' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_xmlrpc_server', $row->config_xmlrpc_server, 'config_xmlrpc_server' );

		patHTML::selectArray( $lists['error_reporting'], $row->config_error_reporting );
		$tmpl->addObject( 'list-error_reporting', $lists['error_reporting'] );

		// LOCALE
		patHTML::selectArray( $lists['lang'], $row->config_lang );
		$tmpl->addRows( 'list-lang', $lists['lang'] );

		patHTML::selectArray( $lists['offset'], $row->config_offset );
		$tmpl->addObject( 'list-offset', $lists['offset'] );

		// MAIL
		patHTML::selectArray( $lists['mailer'], $row->config_mailer );
		$tmpl->addObject( 'list-mailer', $lists['mailer'] );

		patHTML::yesNoRadio( $tmpl, 'body', 'config_smtpauth', $row->config_smtpauth, 'config_smtpauth' );

		// CACHE

		$vars['cache_writable'] = is_writable( $row->config_cachepath );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_tmpl_caching', $row->config_tmpl_caching, 'config_tmpl_caching' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_caching', $row->config_caching, 'config_caching' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_page_caching', $row->config_page_caching, 'config_page_caching' );

		// USER

		patHTML::yesNoRadio( $tmpl, 'body', 'config_allowUserRegistration', $row->config_allowUserRegistration, 'config_allowUserRegistration' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_useractivation', $row->config_useractivation, 'config_useractivation' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_uniquemail', $row->config_uniquemail, 'config_uniquemail' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_shownoauth', $row->config_shownoauth, 'config_shownoauth' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_name_change', $row->config_name_change, 'config_name_change' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_username_change', $row->config_username_change, 'config_username_change' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_user_params', $row->config_user_params, 'config_user_params' );

		patHTML::selectArray( $lists['new_usertype'], $row->config_new_usertype );
		$tmpl->addObject( 'list-new_usertype', $lists['new_usertype'] );

		// META

		patHTML::yesNoRadio( $tmpl, 'body', 'config_MetaTitle', $row->config_MetaTitle, 'config_MetaTitle' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_MetaAuthor', $row->config_MetaAuthor, 'config_MetaAuthor' );

		// STATISTICS

		patHTML::yesNoRadio( $tmpl, 'body', 'config_enable_stats', $row->config_enable_stats, 'config_enable_stats' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_enable_log_items', $row->config_enable_log_items, 'config_enable_log_items' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_enable_log_searches', $row->config_enable_log_searches, 'config_enable_log_searches' );

		// SEO

		patHTML::selectArray( $lists['pagetitles_format'], $row->config_pagetitles_format );
		$tmpl->addObject( 'list-pagetitles_format', $lists['pagetitles_format'] );

		patHTML::yesNoRadio( $tmpl, 'body', 'config_sef', $row->config_sef, 'config_sef' );
		patHTML::yesNoRadio( $tmpl, 'body', 'config_pagetitles', $row->config_pagetitles, 'config_pagetitles' );

		// Content

		$showHide = array(
			patHTML::makeOption( 1, $_LANG->_( 'Hide' ) ),
			patHTML::makeOption( 0, $_LANG->_( 'Show' ) ),
		);
		$showHideR = array(
			patHTML::makeOption( 0, $_LANG->_( 'Hide' ) ),
			patHTML::makeOption( 1, $_LANG->_( 'Show' ) ),
		);
		$iconText = array(
			patHTML::makeOption( 0, $_LANG->_( 'Text' ) ),
			patHTML::makeOption( 1, $_LANG->_( 'Icons' ) ),
		);

		patHTML::yesNoRadio( $tmpl, 'body', 'config_link_titles', $row->config_link_titles, 'config_link_titles' );

		patHTML::radioSet( $tmpl, 'body', 'config_readmore', $row->config_readmore, $showHideR, 'config_readmore' );
		patHTML::radioSet( $tmpl, 'body', 'config_vote', $row->config_vote, $showHideR, 'config_vote' );

		patHTML::radioSet( $tmpl, 'body', 'config_hideAuthor', $row->config_hideAuthor, $showHide, 'config_hideAuthor' );
		patHTML::radioSet( $tmpl, 'body', 'config_hideCreateDate', $row->config_hideCreateDate, $showHide, 'config_hideCreateDate' );
		patHTML::radioSet( $tmpl, 'body', 'config_hideModifyDate', $row->config_hideModifyDate, $showHide, 'config_hideModifyDate' );

		patHTML::radioSet( $tmpl, 'body', 'config_hits', $row->config_hits, $showHideR, 'config_hits' );
		patHTML::radioSet( $tmpl, 'body', 'config_hidePdf', $row->config_hidePdf, $showHide, 'config_hidePdf' );

		patHTML::radioSet( $tmpl, 'body', 'config_hidePrint', $row->config_hidePrint, $showHide, 'config_hidePrint' );
		patHTML::radioSet( $tmpl, 'body', 'config_hideEmail', $row->config_hideEmail, $showHide, 'config_hideEmail' );
		patHTML::radioSet( $tmpl, 'body', 'config_icons', $row->config_icons, $iconText, 'config_icons' );

		patHTML::radioSet( $tmpl, 'body', 'config_back_button', $row->config_back_button, $showHideR, 'config_back_button' );
		patHTML::radioSet( $tmpl, 'body', 'config_item_navigation', $row->config_item_navigation, $showHideR, 'config_item_navigation' );

		$vars['pdf_cache_writable'] = is_writable( $mosConfig_absolute_path . '/media/' );

		// Display

		$tmpl->addVars( 'body', $vars );
		$tmpl->addGlobalVar( 'colwidth', '20%' );

		$tmpl->displayParsedTemplate( 'body' );
	}
}
?>