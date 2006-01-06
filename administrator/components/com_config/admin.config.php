<?php
/**
* @version $Id$
* @package Joomla
* @subpackage Config
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

if (!$acl->acl_check( 'com_config', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php?', JText::_('ALERTNOTAUTH') );
}

require_once( JApplicationHelper::getPath( 'class' ) );
require_once( JApplicationHelper::getPath( 'admin_html' ) );

switch ( $task ) {
	case 'apply':
	case 'save':
		saveconfig( $task );
		break;

	case 'cancel':
		mosRedirect( 'index2.php' );
		break;

	default:
		showconfig( $option );
		break;
}

/**
 * Show the configuration edit form
 * @param string The URL option
 */
function showconfig( $option) {
	global $database, $mosConfig_editor, $mosConfig_helpurl;

	$row = new mosConfig();
	$row->bindGlobals();

	// compile list of the languages
	$langs 		= array();
	$menuitems 	= array();
	$lists 		= array();

// PRE-PROCESS SOME LIST

	// -- Editors --

	// compile list of the editors
	$query = "SELECT element AS value, name AS text"
	. "\n FROM #__mambots"
	. "\n WHERE folder = 'editors'"
	. "\n AND published = 1"
	. "\n ORDER BY ordering, name"
	;
	$database->setQuery( $query );
	$edits = $database->loadObjectList();

	// -- Show/Hide --

	$show_hide = array(
		mosHTML::makeOption( 1, JText::_( 'Hide' ) ),
		mosHTML::makeOption( 0, JText::_( 'Show' ) ),
	);

	$show_hide_r = array(
		mosHTML::makeOption( 0, JText::_( 'Hide' ) ),
		mosHTML::makeOption( 1, JText::_( 'Show' ) ),
	);

	// -- menu items --

	$query = "SELECT id AS value, name AS text FROM #__menu"
	. "\n WHERE ( type='content_section' OR type='components' OR type='content_typed' )"
	. "\n AND published = 1"
	. "\n AND access = 0"
	. "\n ORDER BY name"
	;
	$database->setQuery( $query );
	$menuitems = array_merge( $menuitems, $database->loadObjectList() );


// SITE SETTINGS

	$lists['offline'] = mosHTML::yesnoRadioList( 'config_offline', 'class="inputbox"', $row->config_offline );

	if ( !$row->config_editor ) {
		$row->config_editor = '';
	}
	// build the html select list
	$lists['editor'] = mosHTML::selectList( $edits, 'config_editor', 'class="inputbox" size="1"', 'value', 'text', $row->config_editor );

	$listLimit = array(
		mosHTML::makeOption( 5, 5 ),
		mosHTML::makeOption( 10, 10 ),
		mosHTML::makeOption( 15, 15 ),
		mosHTML::makeOption( 20, 20 ),
		mosHTML::makeOption( 25, 25 ),
		mosHTML::makeOption( 30, 30 ),
		mosHTML::makeOption( 50, 50 ),
	);

	$lists['list_limit'] = mosHTML::selectList( $listLimit, 'config_list_limit', 'class="inputbox" size="1"', 'value', 'text', ( $row->config_list_limit ? $row->config_list_limit : 50 ) );

// DEBUG

	$lists['debug']    = mosHTML::yesnoRadioList( 'config_debug', 'class="inputbox"', $row->config_debug );
	$lists['debug_db'] = mosHTML::yesnoRadioList( 'config_debug_db', 'class="inputbox"', $row->config_debug_db );
	$lists['log']      = mosHTML::yesnoRadioList( 'config_log', 'class="inputbox"', $row->config_log );
	$lists['log_db']   = mosHTML::yesnoRadioList( 'config_log_db', 'class="inputbox"', $row->config_log_db );

// DATABASE SETTINGS


// SERVER SETTINGS

	$lists['gzip'] = mosHTML::yesnoRadioList( 'config_gzip', 'class="inputbox"', $row->config_gzip );

	$errors = array(
		mosHTML::makeOption( -1, JText::_( 'System Default' ) ),
		mosHTML::makeOption( 0, JText::_( 'None' ) ),
		mosHTML::makeOption( E_ERROR|E_WARNING|E_PARSE, JText::_( 'Simple' ) ),
		mosHTML::makeOption( E_ALL , JText::_( 'Maximum' ) )
	);

	$lists['xmlrpc_server'] = mosHTML::yesnoRadioList( 'config_xmlrpc_server', 'class="inputbox"', $row->config_xmlrpc_server );

	$lists['error_reporting'] = mosHTML::selectList( $errors, 'config_error_reporting', 'class="inputbox" size="1"', 'value', 'text', $row->config_error_reporting );

	$lists['enable_ftp'] = mosHTML::yesnoRadioList( 'config_ftp_enable', 'class="inputbox"', $row->config_ftp_enable );

	if ( !$row->config_secure_site ) {
			$row->config_secure_site = str_replace( 'http://', 'https://', $row->config_live_site );
	}

// LOCALE SETTINGS
	jimport('joomla.i18n.help');

	$helpsites = array();
	$helpsites = JHelp::createSiteList( 'http://help.joomla.org/helpsites-11.xml', $mosConfig_helpurl);
	array_unshift( $helpsites, mosHTML::makeOption( '', JText::_('local'))) ;
	$lists['helpsites'] = mosHTML::selectList( $helpsites, 'config_helpurl', ' class="inputbox" id="helpsites"', 'value', 'text', $mosConfig_helpurl);

	$timeoffset = array(
		mosHTML::makeOption( -12, JText::_( '(UTC -12:00) International Date Line West' ) ),
		mosHTML::makeOption( -11, JText::_( '(UTC -11:00) Midway Island, Samoa' ) ),
		mosHTML::makeOption( -10, JText::_( '(UTC -10:00) Hawaii' ) ),
		mosHTML::makeOption( -9.5, JText::_( '(UTC -09:30) Taiohae, Marquesas Islands' ) ),
		mosHTML::makeOption( -9, JText::_( '(UTC -09:00) Alaska' ) ),
		mosHTML::makeOption( -8, JText::_( '(UTC -08:00) Pacific Time (US &amp; Canada)' ) ),
		mosHTML::makeOption( -7, JText::_( '(UTC -07:00) Mountain Time (US &amp; Canada)' ) ),
		mosHTML::makeOption( -6, JText::_( '(UTC -06:00) Central Time (US &amp; Canada), Mexico City' ) ),
		mosHTML::makeOption( -5, JText::_( '(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima' ) ),
		mosHTML::makeOption( -4, JText::_( '(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz' ) ),
		mosHTML::makeOption( -3.5, JText::_( '(UTC -03:30) St. John`s, Newfoundland and Labrador' ) ),
		mosHTML::makeOption( -3, JText::_( '(UTC -03:00) Brazil, Buenos Aires, Georgetown' ) ),
		mosHTML::makeOption( -2, JText::_( '(UTC -02:00) Mid-Atlantic' ) ),
		mosHTML::makeOption( -1, JText::_( '(UTC -01:00) Azores, Cape Verde Islands' ) ),
		mosHTML::makeOption( 0, JText::_( '(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca' ) ),
		mosHTML::makeOption( 1 , JText::_( '(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris' ) ),
		mosHTML::makeOption( 2, JText::_( '(UTC +02:00) Kaliningrad, South Africa' ) ),
		mosHTML::makeOption( 3, JText::_( '(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg' ) ),
		mosHTML::makeOption( 3.5, JText::_( '(UTC +03:30) Tehran' ) ),
		mosHTML::makeOption( 4, JText::_( '(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi' ) ),
		mosHTML::makeOption( 4.5, JText::_( '(UTC +04:30) Kabul' ) ),
		mosHTML::makeOption( 5, JText::_( '(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent' ) ),
		mosHTML::makeOption( 5.5, JText::_( '(UTC +05:30) Bombay, Calcutta, Madras, New Delhi' ) ),
		mosHTML::makeOption( 5.75, JText::_( '(UTC +05:45) Kathmandu' ) ),
		mosHTML::makeOption( 6, JText::_( '(UTC +06:00) Almaty, Dhaka, Colombo' ) ),
		mosHTML::makeOption( 6.30, JText::_( '(UTC +06:30) Yagoon' ) ),
		mosHTML::makeOption( 7, JText::_( '(UTC +07:00) Bangkok, Hanoi, Jakarta' ) ),
		mosHTML::makeOption( 8, JText::_( '(UTC +08:00) Beijing, Perth, Singapore, Hong Kong' ) ),
		mosHTML::makeOption( 8.75, JText::_( '(UTC +08:00) Western Australia' ) ),
		mosHTML::makeOption( 9, JText::_( '(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk' ) ),
		mosHTML::makeOption( 9.5, JText::_( '(UTC +09:30) Adelaide, Darwin, Yakutsk' ) ),
		mosHTML::makeOption( 10, JText::_( '(UTC +10:00) Eastern Australia, Guam, Vladivostok' ) ),
		mosHTML::makeOption( 10.5, JText::_( '(UTC +10:30) Lord Howe Island (Australia)' ) ),
		mosHTML::makeOption( 11, JText::_( '(UTC +11:00) Magadan, Solomon Islands, New Caledonia' ) ),
		mosHTML::makeOption( 11.30, JText::_( '(UTC +11:30) Norfolk Island' ) ),
		mosHTML::makeOption( 12, JText::_( '(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka' ) ),
		mosHTML::makeOption( 12.75, JText::_( '(UTC +12:45) Chatham Island' ) ),
		mosHTML::makeOption( 13, JText::_( '(UTC +13:00) Tonga' ) ),
		mosHTML::makeOption( 14, JText::_( '(UTC +14:00) Kiribati' ) ),
	);

	$lists['offset'] = mosHTML::selectList( $timeoffset, 'config_offset_user', 'class="inputbox" size="1"', 'value', 'text', $row->config_offset_user );

// MAIL SETTINGS

	$mailer = array(
		mosHTML::makeOption( 'mail', JText::_( 'PHP mail function' ) ),
		mosHTML::makeOption( 'sendmail', JText::_( 'Sendmail' ) ),
		mosHTML::makeOption( 'smtp', JText::_( 'SMTP Server' ) )
	);
	$lists['mailer'] 	= mosHTML::selectList( $mailer, 'config_mailer', 'class="inputbox" size="1"', 'value', 'text', $row->config_mailer );

	$lists['smtpauth'] 	= mosHTML::yesnoRadioList( 'config_smtpauth', 'class="inputbox"', $row->config_smtpauth );


// CACHE SETTINGS

	$lists['caching'] 	= mosHTML::yesnoRadioList( 'config_caching', 'class="inputbox"', $row->config_caching );


// USER SETTINGS

	$lists['allowUserRegistration'] = mosHTML::yesnoRadioList( 'config_allowUserRegistration', 'class="inputbox"',	$row->config_allowUserRegistration );

	$lists['useractivation'] 		= mosHTML::yesnoRadioList( 'config_useractivation', 'class="inputbox"',	$row->config_useractivation );

	$lists['uniquemail'] 			= mosHTML::yesnoRadioList( 'config_uniquemail', 'class="inputbox"',	$row->config_uniquemail );

	$lists['shownoauth'] 			= mosHTML::yesnoRadioList( 'config_shownoauth', 'class="inputbox"', $row->config_shownoauth );


// META SETTINGS

	$lists['MetaAuthor']			= mosHTML::yesnoRadioList( 'config_MetaAuthor', 'class="inputbox"', $row->config_MetaAuthor );

	$lists['MetaTitle'] 			= mosHTML::yesnoRadioList( 'config_MetaTitle', 'class="inputbox"', $row->config_MetaTitle );


// STATISTICS SETTINGS

	$lists['log_searches'] 			= mosHTML::yesnoRadioList( 'config_enable_log_searches', 'class="inputbox"', $row->config_enable_log_searches );

	$lists['enable_stats'] 			= mosHTML::yesnoRadioList( 'config_enable_stats', 'class="inputbox"', $row->config_enable_stats );

	$lists['log_items']	 			= mosHTML::yesnoRadioList( 'config_enable_log_items', 'class="inputbox"', $row->config_enable_log_items );


// SEO SETTINGS

	$lists['sef'] 					= mosHTML::yesnoRadioList( 'config_sef', 'class="inputbox" onclick="javascript: if (document.adminForm.config_sef[1].checked) { alert(\''. JText::_( 'Remember to rename htaccess.txt to .htaccess', true ) .'\') }"', $row->config_sef );

	$lists['pagetitles'] 			= mosHTML::yesnoRadioList( 'config_pagetitles', 'class="inputbox"', $row->config_pagetitles );


// CONTENT SETTINGS

	$lists['link_titles'] 			= mosHTML::yesnoRadioList( 'config_link_titles', 'class="inputbox"', $row->config_link_titles );

	$lists['readmore'] 				= mosHTML::RadioList( $show_hide_r, 'config_readmore', 'class="inputbox"', $row->config_readmore, 'value', 'text' );

	$lists['vote'] 					= mosHTML::RadioList( $show_hide_r, 'config_vote', 'class="inputbox"', $row->config_vote, 'value', 'text' );



	$lists['hideAuthor'] 			= mosHTML::RadioList( $show_hide, 'config_hideAuthor', 'class="inputbox"', $row->config_hideAuthor, 'value', 'text' );

	$lists['hideCreateDate'] 		= mosHTML::RadioList( $show_hide, 'config_hideCreateDate', 'class="inputbox"', $row->config_hideCreateDate, 'value', 'text' );

	$lists['hideModifyDate'] 		= mosHTML::RadioList( $show_hide, 'config_hideModifyDate', 'class="inputbox"', $row->config_hideModifyDate, 'value', 'text' );

	$lists['hits'] 					= mosHTML::RadioList( $show_hide_r, 'config_hits', 'class="inputbox"', $row->config_hits, 'value', 'text' );

	if (is_writable( JPATH_SITE . '/media/' )) {
		$lists['hidePdf'] 			= mosHTML::RadioList( $show_hide, 'config_hidePdf', 'class="inputbox"', $row->config_hidePdf, 'value', 'text' );
	} else {
		$lists['hidePdf'] 			= '<input type="hidden" name="config_hidePdf" value="1" /><strong>Hide</strong>';
	}

	$lists['hidePrint'] 			= mosHTML::RadioList( $show_hide, 'config_hidePrint', 'class="inputbox"', $row->config_hidePrint, 'value', 'text' );

	$lists['hideEmail'] 			= mosHTML::RadioList( $show_hide, 'config_hideEmail', 'class="inputbox"', $row->config_hideEmail, 'value', 'text' );

	$lists['icons'] 				= mosHTML::yesnoRadioList( 'config_icons', 'class="inputbox"', $row->config_icons, 'icons', 'text' );

	$lists['back_button'] 			= mosHTML::RadioList( $show_hide_r, 'config_back_button', 'class="inputbox"', $row->config_back_button, 'value', 'text' );

	$lists['item_navigation'] 		= mosHTML::RadioList( $show_hide_r, 'config_item_navigation', 'class="inputbox"', $row->config_item_navigation, 'value', 'text' );

	$lists['ml_support'] 			= mosHTML::yesnoRadioList( 'config_multilingual_support', 'class="inputbox" onclick="javascript: if (document.adminForm.config_multilingual_support[1].checked) { alert(\''. JText::_( 'Remember to install the MambelFish component.', true ) .'\') }"', $row->config_multilingual_support );

	$lists['multipage_toc'] 		= mosHTML::RadioList( $show_hide_r, 'config_multipage_toc', 'class="inputbox"', $row->config_multipage_toc, 'value', 'text' );

// SHOW EDIT FORM

	HTML_config::showconfig( $row, $lists, $option );
}

/**
 * Save the configuration
 */
function saveconfig( $task ) {
	global $database;

	$row = new mosConfig();
	if (!$row->bind( $_POST )) {
		mosRedirect( 'index2.php', $row->getError() );
	}

	$server_time 		= date( 'O' ) / 100;
	$offset 			= $_POST['config_offset_user'] - $server_time;
	$row->config_offset = $offset;

	$config = "<?php \n";
	$config .= $row->getVarText();
	$config .= "?>";

	$fname = JPATH_CONFIGURATION . '/configuration.php';

	$enable_write 	= mosGetParam($_POST,'enable_write',0);
	$oldperms 		= fileperms($fname);
	if ( $enable_write ) {
		@chmod( $fname, $oldperms | 0222);
	}

	if ( $fp = fopen($fname, 'w') ) {
		fputs($fp, $config, strlen($config));
		fclose($fp);
		if ($enable_write) {
			@chmod($fname, $oldperms);
		} else {
			if (mosGetParam($_POST,'disable_write',0))
				@chmod($fname, $oldperms & 0777555);
		} // if

		$msg = JText::_( 'The Configuration Details have been updated' );

		switch ( $task ) {
			case 'apply':
				mosRedirect( 'index2.php?option=com_config&hidemainmenu=1', $msg );
				break;

			case 'save':
			default:
				mosRedirect( 'index2.php', $msg );
				break;
		}
	} else {
		if ($enable_write) {
			@chmod( $fname, $oldperms );
		}
		mosRedirect( 'index2.php', JText::_( 'ERRORCONFIGFILE' ) );
	}
}
?>