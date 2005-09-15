<?php
/**
* @version $Id: config.class.php 137 2005-09-12 10:21:17Z eddieajau $
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
class mosConfig {
// Site Settings
	/** @var int */
	var $config_offline					= 0;
	/** @var string */
	var $config_offline_message			= null;
	/** @var string */
	var $config_error_message			= null;
	/** @var string */
	var $config_sitename				= null;
	/** @var string */
	var $config_editor					= 'tinymce';
	/** @var int */
	var $config_edit_popup				= 1;
	/** @var int */
	var $config_list_limit				= 0;
	/** @var string */
	var $config_favicon					= '/images/favicon.ico';
	/** @var int */
	var $config_internal_templates		= 0;
	/** @var string */
	var $config_live_bookmark			= 0;
	/** @var int */
	var $config_live_bookmark_show		= 0;
	/** @var string */
	//var $config_live_bookmark_file	= '';

// Debug
	/** @var int */
	var $config_debug					= 0;
	/** @var int */
	var $config_debug_db				= 0;
	/** @var int */
	var $config_debug_dblog				= 0;

// Database Settings
	/** @var string */
	var $config_dbtype					= null;
	/** @var string */
	var $config_host					= null;
	/** @var string */
	var $config_user					= null;
	/** @var string */
	var $config_password				= null;
	/** @var string */
	var $config_db						= null;
	/** @var string */
	var $config_dbprefix				= null;
	/** @var int */
	var $config_zero_date				= '0000-00-00 00:00:00';


// Server Settings
	/** @var string */
	var $config_absolute_path			= null;
	/** @var string */
	var $config_live_site				= null;
	/** @var string */
	var $config_secure_site				= null;
	/** @var string */
	var $config_secret					= null;
	/** @var int */
	var $config_gzip					= 0;
	/** @var int */
	var $config_lifetime				= 900;
	/** @var int */
	var $config_savestate				= 0;
	/** @var int */
	var $config_error_reporting			= 0;
	/** @var string */
	var $config_helpurl					= '';
	/** @var boolean */
	var $config_xmlrpc_server			= false;
	/** @var string */
	var $config_fileperms				= '0644';
	/** @var string */
	var $config_dirperms				= '0755';


// Locale Settings
	/** @var string */
	var $config_locale					= null;
	/** @var string */
	var $config_lang					= null;
	/** @var int */
	var $config_offset					= null;


// Mail Settings
	/** @var string */
	var $config_mailer					= null;
	/** @var string */
	var $config_mailfrom				= null;
	/** @var string */
	var $config_fromname				= null;
	/** @var string */
	var $config_sendmail				= '/usr/sbin/sendmail';
	/** @var string */
	var $config_smtpauth				= 0;
	/** @var string */
	var $config_smtpuser				= null;
	/** @var string */
	var $config_smtppass				= null;
	/** @var string */
	var $config_smtphost				= null;


// Cache Settings
	/** @var int */
	var $config_tmpl_caching			= 0;
	/** @var int */
	var $config_caching					= 0;
	/** @var int */
	var $config_page_caching			= 0;
	/** @var string */
	var $config_cachepath				= null;
	/** @var string */
	var $config_cachetime				= 900;


// User Settings
	/** @var int */
	var $config_allowUserRegistration	= 0;
	/** @var string */
	var $config_new_usertype			= '';
	/** @var int */
	var $config_useractivation			= 1;
	/** @var int */
	var $config_uniquemail				= 1;
	/** @var int */
	var $config_shownoauth				= 0;
	/** @var int */
	var $config_name_change				= 1;
	/** @var int */
	var $config_username_change			= 1;
	/** @var int */
	var $config_password_length			= 6;
	/** @var int */
	var $config_username_length			= 3;
	/** @var int */
	var $config_user_params				= 0;


// Meta Settings
	/** @var string */
	var $config_MetaDesc				= null;
	/** @var string */
	var $config_MetaKeys				= null;
	/** @var int */
	var $config_MetaTitle				= null;
	/** @var int */
	var $config_MetaAuthor				= null;


// Statistics Settings
	/** @var int */
	var $config_enable_stats			= 0;
	/** @var int */
	var $config_enable_log_items		= 0;
	/** @var int */
	var $config_enable_log_searches		= 0;


// SEO Settings
	/** @var int */
	var $config_sef						= 0;
	/** @var int */
	var $config_pagetitles				= 1;
	/** @var int */
	var $config_pagetitles_format		= 1;


// Content Settings
	/** @var int */
	var $config_link_titles				= 0;
	/** @var int */
	var $config_readmore				= 1;
	/** @var int */
	var $config_vote					= 0;
	/** @var int */
	var $config_hideAuthor				= 0;
	/** @var int */
	var $config_hideCreateDate			= 0;
	/** @var int */
	var $config_hideModifyDate			= 0;
	/** @var int */
	var $config_hits					= 1;
	/** @var int */
	var $config_hidePdf					= 0;
	/** @var int */
	var $config_hidePrint				= 0;
	/** @var int */
	var $config_hideEmail				= 0;
	/** @var int */
	var $config_icons					= 1;
	/** @var int */
	var $config_back_button				= 0;
	/** @var int */
	var $config_item_navigation			= 0;
	/** @var int */
	var $config_mbf_content				= 0;


	/**
	 * @return array An array of the public vars in the class
	 */
	function getPublicVars() {
		$vars = array();
		foreach (array_keys( get_class_vars( get_class( $this ) ) ) as $v) {
			if ($v{0} != '_') {
				$vars[] = $v;
			}
		}
		return $vars;
	}
	/**
	 *	binds a named array/hash to this object
	 *	@param array $hash named array
	 *	@return null|string	null is operation was satisfactory, otherwise returns an error
	 */
	function bind( $array, $ignore='' ) {
		if (!is_array( $array )) {
			$this->_error = strtolower(get_class( $this )).'::bind failed.';
			return false;
		} else {
			return mosBindArrayToObject( $array, $this, $ignore );
		}
	}

	/**
	 * Writes the configuration file line for a particular variable
	 */
	function getVarText() {
		$txt = '';
		$vars = $this->getPublicVars();
		foreach ($vars as $v) {
			$k = str_replace( 'config_', 'mosConfig_', $v );
			$txt .= "\$$k = '" . addslashes( $this->$v ) . "';\n";
		}
		return $txt;
	}

	/**
	 * Binds the global configuration variables to the class properties
	 */
	function bindGlobals() {
		$vars = $this->getPublicVars();
		foreach ($vars as $v) {
			$k = str_replace( 'config_', 'mosConfig_', $v );
			if (isset( $GLOBALS[$k] ))
				$this->$v = $GLOBALS[$k];
		}
	}
}
?>