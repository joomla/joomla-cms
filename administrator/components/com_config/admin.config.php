<?php
/**
* @version $Id: admin.config.php 137 2005-09-12 10:21:17Z eddieajau $
* @package Mambo
* @subpackage Config
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software and parts of it may contain or be derived from the
* GNU General Public License or other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

if (!$acl->acl_check( 'com_config', 'manage', 'users', $my->usertype )) {
	mosRedirect( 'index2.php', $_LANG->_('NOT_AUTH') );
}

mosFS::load( '@admin_html' );
mosFS::load( '@class' );

/**
 * @package Config
 * @subpackage Config
 */
class configTasks extends mosAbstractTasker {
	/**
	 * Constructor
	 */
	function configTasks() {
		// auto register public methods as tasks, set the default task
		parent::mosAbstractTasker( 'edit' );

		// set task level access control
		//$this->setAccessControl( 'com_templates', 'manage' );

		$this->registerTask( 'apply', 'save' );
	}

	/**
	* List the records
	*/
	function edit() {
		global $database,  $mainframe, $mosConfig_absolute_path;
		global $_LANG;

		$row = new mosConfig();
		$row->bindGlobals();

		$lists = array();


	// Site Settings ----------------
		// compile list of the editors
		$query = "SELECT element AS value, name AS text"
		. "\n FROM #__mambots"
		. "\n WHERE folder = 'editors'"
		. "\n AND published >= 0"
		. "\n ORDER BY ordering, name"
		;
		$database->setQuery( $query );
		$lists['editor'] = $database->loadObjectList();
		if ( !$row->config_editor ) {
			$row->config_editor = 'editor';
		}
		$lists['list_limit']  = array(
			mosHTML::makeOption( 5, 5 ),
			mosHTML::makeOption( 10, 10 ),
			mosHTML::makeOption( 15, 15 ),
			mosHTML::makeOption( 20, 20 ),
			mosHTML::makeOption( 25, 25 ),
			mosHTML::makeOption( 30, 30 ),
			mosHTML::makeOption( 50, 50 ),
		);
		$lists['live_bookmark'] = array(
			mosHTML::makeOption( '0',		$_LANG->_( 'Off' ) ),
			mosHTML::makeOption( 'RSS0.91',	$_LANG->_( 'RSS 0.91' ) ),
			mosHTML::makeOption( 'RSS1.0',	$_LANG->_( 'RSS 1.0' ) ),
			mosHTML::makeOption( 'RSS2.0',	$_LANG->_( 'RSS 2.0' ) ),
			mosHTML::makeOption( 'ATOM0.3',	$_LANG->_( 'ATOM 0.3' ) ),
		);
		$lists['live_bookmark_show'] = array(
			mosHTML::makeOption( 0,	$_LANG->_( 'All pages' ) ),
			mosHTML::makeOption( 1,	$_LANG->_( 'Home page only' ) ),
		);

		//$lists['offline'] 				= mosHTML::yesnoRadioList( 'config_offline', 'class="inputbox"', $row->config_offline );
		//$lists['editor'] 				= mosHTML::selectList( $edits, 'config_editor', 'id="config_editor" class="inputbox" size="1"', 'value', 'text', $row->config_editor );
		//$lists['edit_popup']			= mosHTML::yesnoRadioList( 'config_edit_popup', 'class="inputbox"', $row->config_edit_popup );
		//$lists['list_length'] 			= mosHTML::selectList( $list_length, 'config_list_limit', 'id="config_list_length" class="inputbox" size="1"', 'value', 'text', ( $row->config_list_limit ? $row->config_list_limit : 50 ) );
		//$lists['debug'] 				= mosHTML::yesnoRadioList( 'config_debug', 'class="inputbox"', $row->config_debug );
		//$lists['internal_templates']	= mosHTML::yesnoRadioList( 'config_internal_templates', 'class="inputbox"', $row->config_internal_templates );
		//$lists['live_bookmark']			= mosHTML::selectList( $live_bookmark, 'config_live_bookmark', 'id="config_live_bookmark" class="inputbox" size="1"', 'value', 'text', $row->config_live_bookmark );
		//$lists['live_bookmark_show']	= mosHTML::selectList( $live_bookmark_show, 'config_live_bookmark_show', 'id="config_live_bookmark_show" class="inputbox" size="1"', 'value', 'text', $row->config_live_bookmark_show );
	////////////////////////////////////


	// Database Settings ----------------
		if ( !$row->config_dbtype ) {
			$row->config_dbtype = 'mysql';
		}
		$lists['dbtype'] = mosReadDirectory( $mosConfig_absolute_path . '/includes/adodb/drivers/', '\.php$' );
		foreach ($lists['dbtype'] as $i=>$file) {
			$lists['dbtype'][$i] = mosHTML::makeOption( str_replace( array( 'adodb-', '.inc.php' ), '', $file ) );
		}

		//$lists['dbtype']				= mosHTML::selectList( $files, 'config_dbtype', 'id="config_dbtype" class="inputbox" size="1"', 'value', 'text', $row->config_dbtype );
	////////////////////////////////////


	// Server Settings ----------------
		$lists['error_reporting'] = array(
			mosHTML::makeOption( -1, 						$_LANG->_( 'System Default' ) ),
			mosHTML::makeOption( 0, 						$_LANG->_( 'None' ) ),
			mosHTML::makeOption( E_ERROR|E_WARNING|E_PARSE, $_LANG->_( 'Simple' ) ),
			mosHTML::makeOption( E_ALL , 					$_LANG->_( 'Maximum' ) )
		);
		if ( !$row->config_secure_site ) {
			$row->config_secure_site = str_replace( 'http://', 'https://', $row->config_live_site );
		}

		//$lists['gzip'] 					= mosHTML::yesnoRadioList( 'config_gzip', 'class="inputbox"', $row->config_gzip );
		//$lists['savestate'] 			= mosHTML::yesnoRadioList( 'config_savestate', 'class="inputbox"', $row->config_savestate );
		//$lists['error_reporting'] 		= mosHTML::selectList( $errors, 'config_error_reporting', 'id="config_error_reporting" class="inputbox" size="1"', 'value', 'text', $row->config_error_reporting );
		//$lists['xmlrpc_server'] 		= mosHTML::yesnoRadioList( 'config_xmlrpc_server', 'class="inputbox"', $row->config_xmlrpc_server );
		//$lists['ml_support'] 			= mosHTML::yesnoRadioList( 'config_mbf_content', 'class="inputbox" onclick="javascript: if (document.adminForm.config_mbf_content[1].checked) { alert(\'Remember to install the MambelFish component.\') }"', $row->config_mbf_content );
	////////////////////////////////////


	// Locale Settings ----------------
		// compile list of the languages
		$lists['lang'] = mosLanguageFactory::buildLanguageList( 'front', $row->config_lang);
		// make a generic -24 - 24 list
		for ($i=-24;$i<=24;$i++) {
			$lists['offset'][] = mosHTML::makeOption( $i, $i );
		}

		//$lists['lang'] 					= mosHTML::selectList( $langs, 'config_lang', 'id="config_lang" class="inputbox" size="1"', 'value', 'text', $row->config_lang );
		//$lists['offset'] 				= mosHTML::selectList( $timeoffset, 'config_offset', 'id="config_offset" class="inputbox" size="1"',	'value', 'text', $row->config_offset );
	////////////////////////////////////


	// Mail Settings ----------------
		$lists['mailer'] = array(
			mosHTML::makeOption( 'mail', 		$_LANG->_( 'PHP mail function' ) ),
			mosHTML::makeOption( 'sendmail', 	$_LANG->_( 'Sendmail' ) ),
			mosHTML::makeOption( 'smtp', 		$_LANG->_( 'SMTP Server' ) )
		);

		//$lists['mailer'] 				= mosHTML::selectList( $mailer, 'config_mailer', 'id="config_mailer" class="inputbox" size="1"', 'value', 'text', $row->config_mailer );
		//$lists['smtpauth'] 				= mosHTML::yesnoRadioList( 'config_smtpauth', 'class="inputbox"', $row->config_smtpauth );
	////////////////////////////////////


	// Cache Settings ----------------
		//$lists['tmpl_caching'] 				= mosHTML::yesnoRadioList( 'config_tmpl_caching', 'class="inputbox"', $row->config_tmpl_caching );
		//$lists['caching'] 					= mosHTML::yesnoRadioList( 'config_caching', 'class="inputbox"', $row->config_caching );
		//$lists['page_caching'] 				= mosHTML::yesnoRadioList( 'config_page_caching', 'class="inputbox"', $row->config_page_caching );
	////////////////////////////////////


	// User Settings ----------------
		$lists['new_usertype'] = array(
			mosHTML::makeOption( 'Registered', 	$_LANG->_( 'Registered' ) ),
			mosHTML::makeOption( 'Author', 		$_LANG->_( 'Author' ) ),
			mosHTML::makeOption( 'Editor', 		$_LANG->_( 'Editor' ) ),
			mosHTML::makeOption( 'Publisher', 	$_LANG->_( 'Publisher' ) )
		);
		if ( !$row->config_username_length ) {
			$row->config_username_length = 3;
		}
		if ( !$row->config_password_length ) {
			$row->config_password_length = 6;
		}

		//$lists['allowuserregistration'] = mosHTML::yesnoRadioList( 'config_allowUserRegistration', 'class="inputbox"',	$row->config_allowUserRegistration );
		//$lists['new_usertype'] 			= mosHTML::selectList( $usertypes, 'config_new_usertype', 'id="config_new_usertype" class="inputbox" size="1"', 'value', 'text', ( $row->config_new_usertype ? $row->config_new_usertype : 'Registered' ) );
		//$lists['useractivation'] 		= mosHTML::yesnoRadioList( 'config_useractivation', 'class="inputbox"',	$row->config_useractivation );
		//$lists['uniquemail'] 			= mosHTML::yesnoRadioList( 'config_uniquemail', 'class="inputbox"',	$row->config_uniquemail );
		//$lists['shownoauth'] 			= mosHTML::yesnoRadioList( 'config_shownoauth', 'class="inputbox"', $row->config_shownoauth );
		//$lists['name_change'] 			= mosHTML::yesnoRadioList( 'config_name_change', 'class="inputbox"', $row->config_name_change );
		//$lists['username_change'] 		= mosHTML::yesnoRadioList( 'config_username_change', 'class="inputbox"', $row->config_username_change );
		//$lists['user_params'] 			= mosHTML::yesnoRadioList( 'config_user_params', 'class="inputbox"', $row->config_user_params );
	////////////////////////////////////


	// Meta Settings ----------------
		//$lists['metatitle'] 			= mosHTML::yesnoRadioList( 'config_MetaTitle', 'class="inputbox"', $row->config_MetaTitle );
		//$lists['metaauthor']			= mosHTML::yesnoRadioList( 'config_MetaAuthor', 'class="inputbox"', $row->config_MetaAuthor );
	////////////////////////////////////


	// Statistics Settings ----------------
		//$lists['enable_stats'] 			= mosHTML::yesnoRadioList( 'config_enable_stats', 'class="inputbox"', $row->config_enable_stats );
		//$lists['enable_log_items']	 	= mosHTML::yesnoRadioList( 'config_enable_log_items', 'class="inputbox"', $row->config_enable_log_items );
		//$lists['enable_log_searches'] 	= mosHTML::yesnoRadioList( 'config_enable_log_searches', 'class="inputbox"', $row->config_enable_log_searches );
	////////////////////////////////////


	// SEO Settings ----------------
		$lists['pagetitles_format'] = array(
			mosHTML::makeOption( 1,	$_LANG->_( 'Site - Title' ) ),
			mosHTML::makeOption( 2,	$_LANG->_( 'Title - Site' ) ),
			mosHTML::makeOption( 3,	$_LANG->_( 'Title' ) ),
		);

		//$lists['sef'] 					= mosHTML::yesnoRadioList( 'config_sef', 'class="inputbox" onclick="javascript: if (document.adminForm.config_sef[1].checked) { alert(\''. $_LANG->_( 'Remember to rename' ) .' htaccess.txt '. $_LANG->_( 'to' ) .' .htaccess\') }"', $row->config_sef );
		//$lists['pagetitles'] 			= mosHTML::yesnoRadioList( 'config_pagetitles', 'class="inputbox"', $row->config_pagetitles );
		//$lists['pagetitles_format']		= mosHTML::selectList( $pagetitle, 'config_pagetitles_format', ' id="config_pagetitles_format" class="inputbox" size="1"',	'value', 'text', $row->config_pagetitles_format );
	////////////////////////////////////


	// Content Settings ----------------
		$icon_text = array(
			mosHTML::makeOption( 0, $_LANG->_( 'Text' ) ),
			mosHTML::makeOption( 1, $_LANG->_( 'Icons' ) ),
		);

		//$lists['link_titles'] 			= mosHTML::yesnoRadioList( 'config_link_titles', 'class="inputbox"', $row->config_link_titles );
		//$lists['readmore'] 				= mosHTML::radiolist( $show_hide_r, 'config_readmore', 'class="inputbox"', $row->config_readmore, 'value', 'text' );
		//$lists['vote'] 					= mosHTML::radiolist( $show_hide_r, 'config_vote', 'class="inputbox"', $row->config_vote, 'value', 'text' );
		//$lists['hideauthor'] 			= mosHTML::radiolist( $show_hide, 'config_hideAuthor', 'class="inputbox"', $row->config_hideAuthor, 'value', 'text' );
		//$lists['hideCreateDate'] 		= mosHTML::radiolist( $show_hide, 'config_hideCreateDate', 'class="inputbox"', $row->config_hideCreateDate, 'value', 'text' );
		//$lists['hideModifyDate'] 		= mosHTML::radiolist( $show_hide, 'config_hideModifyDate', 'class="inputbox"', $row->config_hideModifyDate, 'value', 'text' );
		//$lists['hits'] 					= mosHTML::radiolist( $show_hide_r, 'config_hits', 'class="inputbox"', $row->config_hits, 'value', 'text' );
		//if ( is_writable( $mosConfig_absolute_path .'/media/' ) ) {
			//$lists['hidepdf'] 			= mosHTML::radiolist( $show_hide, 'config_hidePdf', 'class="inputbox"', $row->config_hidePdf, 'value', 'text' );
		//} else {
			//$lists['hidepdf'] 			= '<input type="hidden" name="config_hidePdf" value="1" /><strong>'. $_LANG->_( 'Yes' ) .'</strong>';
		//}
		//$lists['hideprint'] 			= mosHTML::radiolist( $show_hide, 'config_hidePrint', 'class="inputbox"', $row->config_hidePrint, 'value', 'text' );
		//$lists['hideemail'] 			= mosHTML::radiolist( $show_hide, 'config_hideEmail', 'class="inputbox"', $row->config_hideEmail, 'value', 'text' );
		//$lists['icons'] 				= mosHTML::radiolist( $icon_text, 'config_icons', 'class="inputbox"', $row->config_icons, 'value', 'text' );
		//$lists['back_button'] 			= mosHTML::radiolist( $show_hide_r, 'config_back_button', 'class="inputbox"', $row->config_back_button, 'value', 'text' );
		//$lists['item_navigation'] 		= mosHTML::radiolist( $show_hide_r, 'config_item_navigation', 'class="inputbox"', $row->config_item_navigation, 'value', 'text' );
	////////////////////////////////////

		$mainframe->set('disableMenu', true);

		$vars = array();

		// configuration file permissions
		$vars['config_write'] = is_writable( $mosConfig_absolute_path . '/configuration.php' );
		$vars['config_chmod'] = is_writable( $mosConfig_absolute_path . '/configuration.php' );

		configScreens::edit( $row, $lists, $vars );
	}

	/**
	* Saves the record from an edit form submit
	*/
	function save() {
		global $database, $mosConfig_absolute_path;
		global $task;
		global $_LANG;

		$row = new mosConfig();
		if (!$row->bind( $_POST )) {
			mosErrorAlert( $row->getError() );
		}

		$editor = intval( mosGetParam( $_POST, 'editor', 0 ) );
		if ($editor > 0) {
			$query = "UPDATE #__mambots"
			. "\n SET published = 0"
			. "\n WHERE published >= 0"
			. "\n AND folder = 'editors'"
			;
			$database->setQuery( $query );
			if ( !$database->query() ) {
				mosErrorAlert( $database->getErrorMsg() );
			}

			$query = "UPDATE #__mambots"
			. "\n SET published = 1"
			. "\n WHERE id = $editor"
			;
			$database->setQuery( $query );
			if ( !$database->query() ) {
				mosErrorAlert( $database->getErrorMsg() );
			}
		}

		$config = "<?php \n";
		$config .= $row->getVarText();
		$config .= "setlocale (LC_TIME, \$mosConfig_locale);\n";
		$config .= '?>';

		$fname = $mosConfig_absolute_path . '/configuration.php';

		$enable_write 	= mosGetParam( $_POST, 'enable_write', 0 );
		$oldperms 		= fileperms( $fname );
		if ( $enable_write ) {
			@chmod( $fname, $oldperms | 0222);
		}

		if ( file_put_contents( $fname, $config ) ) {
			if ( $enable_write ) {
				@chmod( $fname, $oldperms );
			} else {
				if ( mosGetParam( $_POST, 'disable_write', 0 ) ) {
					@chmod($fname, $oldperms & 0777555);
				}
			}

			$msg = $_LANG->_( 'The Configuration Details have been updated' );

			// apply file and directory permissions if requested by user
			$applyFilePerms = mosGetParam( $_POST, 'applyFilePerms', 0 ) && $row->config_fileperms != '';
			$applyDirPerms 	= mosGetParam( $_POST, 'applyDirPerms', 0 ) && $row->config_dirperms != '';
			if ( $applyFilePerms || $applyDirPerms ) {
				$mosrootfiles = array(
					'administrator',
					'cache',
					'components',
					'editor',
					'help',
					'images',
					'includes',
					'installation',
					'language',
					'mambots',
					'media',
					'modules',
					'templates',
					'CHANGELOG',
					'configuration.php-dist',
					'configuration.php',
					'globals.php',
					'htaccess.txt',
					'index.php',
					'index2.php',
					'INSTALL',
					'LICENSE',
					'mainbody.php',
					'offline.php',
					'pathway.php',
					'robots.txt'
				);

				$filemode = NULL;

				if ( $applyFilePerms ) {
					$filemode = octdec( $row->config_fileperms );
				}

				$dirmode = NULL;

				if ( $applyDirPerms ) {
					$dirmode = octdec( $row->config_dirperms );
				}

				foreach ( $mosrootfiles as $file ) {
					mosFS::CHMOD( $mosConfig_absolute_path .'/'. $file, $filemode, $dirmode );
				}
			} // if

			switch ( $task ) {
				case 'apply':
					mosRedirect( 'index2.php?option=com_config', $msg );
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
			mosRedirect( 'index2.php', $_LANG->_( 'ERRORUNABLETOOPENCONFIGFILETOWRITE' ) );
		}
	}

	/**
	* Cancels editing returns to Admin Home page
	*/
	function cancel() {
		$this->setRedirect( 'index2.php' );
	}
}

$tasker = new configTasks();
$tasker->performTask( mosGetParam( $_REQUEST, 'task', '' ) );
$tasker->redirect();
?>