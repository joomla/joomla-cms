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

/**
* @package Joomla
* @subpackage Config
*/
class mosConfig {
// Site Settings
	/** @var int */
	var $config_offline				= null;
	/** @var string */
	var $config_offline_message		= null;
	/** @var string */
	var $config_error_message		= null;
	/** @var string */
	var $config_sitename			= null;
	/** @var string */
	var $config_editor				='tinymce';
	/** @var int */
	var $config_list_limit			= 30;

// Debug
	/** @var int */
	var $config_debug				=0;
	/** @var int */
	var $config_debug_db			=0;
	/** @var int */
	var $config_log					=0;
	/** @var int */
	var $config_log_db				=0;

// Database Settings
	/** @var string */
	var $config_host				= null;
	/** @var string */
	var $config_user				= null;
	/** @var string */
	var $config_password			= null;
	/** @var string */
	var $config_db					= null;
	/** @var string */
	var $config_dbprefix			= null;
	/** @var string */
	var $config_dbtype				= null;

// Server Settings
	/** @var string */
	var $config_absolute_path		= null;
	/** @var string */
	var $config_live_site			= null;
	/** @var string */
	var $config_secure_site			= null;
	/** @var string */
	var $config_secret				= null;
	/** @var int */
	var $config_gzip				= '0';
	/** @var int */
	var $config_lifetime			= null;
	/** @var int */
	var $config_error_reporting		= '-1';
	/** @var string */
	var $config_helpurl				= 'http://help.joomla.org';
	/** @var string */
	var $config_xmlrpc_server		= '0';
	/** @var string */
	var $config_fileperms			= '0644';
	/** @var string */
	var $config_dirperms			= '0755';
	/** @var string */
	var $config_ftp_user 			= null;
	/** @var string */
	var $config_ftp_pass 			= null;
	/** @var string */
	var $config_ftp_root 			= null;

// Locale Settings
	/** @var string */
	var $config_lang				= null;
	/** @var string */
	var $config_lang_administrator	= null;
	/** @var int */
	var $config_offset				= null;
	/** @var int */
	var $config_offset_user			= null;

// Mail Settings
	/** @var string */
	var $config_mailer				= null;
	/** @var string */
	var $config_mailfrom			= null;
	/** @var string */
	var $config_fromname			= null;
	/** @var string */
	var $config_sendmail			= '/usr/sbin/sendmail';
	/** @var string */
	var $config_smtpauth			= 0;
	/** @var string */
	var $config_smtpuser			= null;
	/** @var string */
	var $config_smtppass			= null;
	/** @var string */
	var $config_smtphost			= null;

// Cache Settings
	/** @var int */
	var $config_caching				= 0;
	/** @var string */
	var $config_cachepath			= null;
	/** @var string */
	var $config_cachetime			= null;

// User Settings
	/** @var int */
	var $config_allowUserRegistration	= 0;
	/** @var int */
	var $config_useractivation			= null;
	/** @var int */
	var $config_uniquemail				= null;
	/** @var int */
	var $config_shownoauth				= 0;

// Meta Settings
	/** @var string */
	var $config_MetaDesc			= null;
	/** @var string */
	var $config_MetaKeys			= null;
	/** @var int */
	var $config_MetaTitle			= null;
	/** @var int */
	var $config_MetaAuthor			= null;

// Statistics Settings
	/** @var int */
	var $config_enable_log_searches	= null;
	/** @var int */
	var $config_enable_stats		= null;
	/** @var int */
	var $config_enable_log_items	= null;

// SEO Settings
	/** @var int */
	var $config_sef=0;
	/** @var int */
	var $config_pagetitles=1;

// Content Settings
	/** @var int */
	var $config_link_titles		= 0;
	/** @var int */
	var $config_readmore		= 1;
	/** @var int */
	var $config_vote			= 0;
	/** @var int */
	var $config_hideAuthor		= 0;
	/** @var int */
	var $config_hideCreateDate	= 0;
	/** @var int */
	var $config_hideModifyDate	= 0;
	/** @var int */
	var $config_hits			= 1;
	/** @var int */
	var $config_hidePdf			= 0;
	/** @var int */
	var $config_hidePrint		= 0;
	/** @var int */
	var $config_hideEmail		= 0;
	/** @var int */
	var $config_icons			= 1;
	/** @var int */
	var $config_back_button		= 0;
	/** @var int */
	var $config_item_navigation	= 0;
	/** @var int */
	var $config_multipage_toc	= 0;
	/** @var int */
	var $config_multilingual_support	= 0;

	/**
	 * @return array An array of the public vars in the class
	 */
	function getPublicVars() {
		$public = array();
		$vars = array_keys( get_class_vars( get_class( $this ) ) );
		sort( $vars );
		foreach ($vars as $v) {
			if ($v{0} != '_') {
				$public[] = $v;
			}
		}
		return $public;
	}

	/**
	 *	binds a named array/hash to this object
	 *	@param array $hash named array
	 *	@return null|string	null is operation was satisfactory, otherwise returns an error
	 */
	function bind( $array, $ignore='' ) {
		if (!is_array( $array )) {
			$this->_error = strtolower(get_class( $this )).'::'. JText::_( 'bind failed.' );
			return false;
		} else {
			return mosBindArrayToObject( $array, $this, $ignore );
		}
	}

	/**
	 * Writes the configuration file line for a particular variable
	 * @return string
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