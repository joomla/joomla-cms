<?php

/**
* @version $Id:  $
* @package Joomla
* @copyright Copyright (C) 2005 Open Source Matters. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined( '_VALID_MOS' ) or die( 'Restricted access' );

/**
* Languages/translation handler class
* @package Joomla
* @subpackage Language
*/
class mosLanguage {
	/** @var boolean If true, highlights string not found */
	var $_debug = false;

	/** @var string Official element name of the language */
	var $_name=null;
	/** @var string language locale for the locale formating */
	var $_locale=null;
	/** @var string iso charset of html files */
	var $_iso=null;
	/** @var string iso code of the languge */
	var $_isocode=null;
	/** @var boolean True if language is displayed right-to-left */
	var $_rtl=null;
	/** @var string The default language to load */
	var $_defaultLang=null;
	/** @var string The user language to load */
	var $_userLang=null;
	/** @var string Identifying string of the language */
	var $_identifyer=null;

	/** @var array Transaltions */
	var $_strings=null;

	/**
	* Constructor activating the default information of the language
	*/
	function mosLanguage( $userLang='' ) {
		$this->_strings = array();

		if( isset( $this->_locale ) ) {
			setlocale (LC_TIME, $this->_locale);
		}
		$this->_defaultLang = 'english';
		$this->_userLang = $userLang;
	}

	/**
	* Translator function, mimics the php gettext (alias _) function
	*/
	function _( $string, $jsSafe=false ) {
		//$key = str_replace( ' ', '_', strtoupper( trim( $string ) ) );echo '<br>'.$key;
		$key = strtoupper( $string );
		$key = substr( $key, 0, 1) == '_' ? substr( $key, 1 ) : $key;
		if (isset( $this->_strings[$key] )) {
			$string = $this->_debug ? "&bull;". $this->_strings[$key] ."&bull;" : $this->_strings[$key];
		} else {
			if( defined( $string ) ) {
				$string = $this->_debug ? "!!". constant( $string ) ."!!" : constant( $string );
			} else {
				$string = $this->_debug ? "??". $string ."??" : $string;
			}
		}
		if ($jsSafe) {
			$string = str_replace( "\n", '\\n', $string );
			$string = str_replace( '"', '&quot;', $string );
			$string = str_replace( '\'', '&#39;', $string );
		}
		return $string;
	}

	/**
	 * Passes a string thru an sprintf
	 * @param format The format string
	 * @param mixed Mixed number of arguments for the sprintf function
	 */
	function sprintf( $string ) {
		$args = func_get_args();
		if (count( $args ) > 0) {
			$args[0] = $this->_( $args[0] );
			return call_user_func_array( 'sprintf', $args );
		}
		return '';
	}
	/**
	 * Passes a string thru an printf
	 * @param format The format string
	 * @param mixed Mixed number of arguments for the sprintf function
	 */
	function printf( $string ) {
		$args = func_get_args();
		if (count( $args ) > 0) {
			$args[0] = $this->_( $args[0] );
			return call_user_func_array( 'printf', $args );
		}
		return '';
	}
	/**
	* Loads a language file and appends the results to the existing strings
	* @param string The name of the file
	* @return boolean True if successful, false is failed
	*/
	function _load( $filename ) {
		if (file_exists( $filename )) {
			if ($content = file_get_contents( $filename )) {
				if( $this->_identifyer === null ) {
					$this->_identifyer = basename( $filename, '.ini' );
				}

				$this->_strings = array_merge( $this->_strings, mosParameters::parse( $content, false, true ) );

				if (isset( $this->_strings['__NAME'] )) {
					$this->name( $this->_strings['__NAME'] );
				}
				if (isset( $this->_strings['__ISO'] )) {
					$this->iso( $this->_strings['__ISO'] );
				}
				if (isset( $this->_strings['__LOCALE'] )) {
					$this->locale( $this->_strings['__LOCALE'] );
				}
				if (isset( $this->_strings['__ISOCODE'] )) {
					$this->isoCode( $this->_strings['__ISOCODE'] );
				}
				if (isset( $this->_strings['__RTL'] )) {
					$this->rtl( $this->_strings['__RTL'] );
				}

				return true;
			}
		}
		return false;
	}

	/**
	 * Loads a single langauge file
	 * @param string The option
	 * @param mixed The client id: 0=site, 1=admin, 2=installation
	 */
	function load( $option='', $client=0 ) {
		$basePath = mosLanguage::getLanguagePath( $client, $this->_userLang );

		if (empty( $option )) {
			$filename = $basePath . $this->_userLang . '.ini';
			if (!file_exists( $filename ) ) {
				// roll back to default language
				$filename = $basePath . $this->_defaultLang . '.ini';
			}
		} else {
			$filename = $basePath . $this->_userLang . '.' . $option . '.ini';
			if (!file_exists( $filename ) ) {
				// roll back to default language
				$filename = $basePath . $this->_defaultLang . '.' . $option . '.ini';
			}
		}

		$this->_load( $filename );
	}

	/**
	 * Loads the main and component language files
	 * @param string The option
	 * @param mixed The client id: 0=site, 1=admin, 2=installation
	 */
	function loadAll( $option='', $client=0 ) {
		// load primary language file
		$this->load( '', $client );

		// load 'option'(al) language file
		$option = trim( $option );
		if ($option) {
			$this->load( $option, $client );
		}
	}
	/**
	 * Is this a primary language file
	 */
	function isPrimary( $lang, $client, $file ) {
		return (($client == 0 || $client == 2) && $file == $lang.'.ini' );
	}
	/**
	* Getter for Name
	* @param string An optional value
	* @return string Official name element of the language
	*/
	function name( $value=null ) {
		return $value !== null ? $this->_name = $value : $this->_name;
	}

	/**
	* Getter for ISO
	* @param string An optional value
	* @return string ISO charset for the html files
	*/
	function iso( $value=null ) {
		return $value !== null ? $this->_iso = $value : $this->_iso;
	}

	/**
	* Getter for ISO code
	* @param string An optional value
	* @return string iso code of the languge
	*/
	function isoCode( $value=null ) {
		return $value !== null ? $this->_isocode = $value : $this->_isocode;
	}

	/**
	* Getter for Locale information of the language
	* @param string An optional value
	* @return string locale string
	*/
	function locale( $value=null ) {
		return $value !== null ? $this->_locale = $value : $this->_locale;
	}
	/**
	* Sets/gets the RTL property
	* @param string An optional value
	* @return string locale string
	*/
	function rtl( $value=null ) {
		return $value !== null ? $this->_rtl = $value : $this->_rtl;
	}
	/**
	* Sets/gets the Debug property
	* @param string An optional value
	* @return string locale string
	*/
	function debug( $value=null ) {
		return $value !== null ? $this->_debug = $value : $this->_debug;
	}

	/**
	 * @param int The client number
	 * @return string	language related path or null
	 */
	function getLanguagePath( $client=0, $language=null, $addTrailingSlash=true ) {
		$dir = mosMainFrame::getBasePath( $client ) . 'language' . DIRECTORY_SEPARATOR;
		if ($client != mosMainFrame::getClientID( 'installation' ) && isset( $language )) {
			$dir .= $language .DIRECTORY_SEPARATOR;
		}

		return mosFS::getNativePath( $dir, $addTrailingSlash );
	}
	/**
	 * Determines is a key exists
	 */
	function hasKey( $key ) {
		return isset( $this->_strings[strtoupper( $key )] );
	}

	/** Returns a list of known languages for an area
	 *
	 * @param string	key of the area (front, admin, install)
	 * @return array	key/value pair with the language file and real name
	 */
	function getKnownLanguages( $client=2 ) {
		static $knownLanguages=null;

		if (is_string( $client )) {
			$client = mosMainFrame::getClientID( $client );
		}
		$dir = mosLanguage::getLanguagePath( $client );

		if( !isset( $knownLanguages[$client] ) ) {
			$knownLanguages[$client] = mosLanguage::_parseLanguageFiles( $dir, $client );
		}

		return $knownLanguages[$client];
	}

	/** Searches for language directories within a certain base dir
	 * @param string	directory of files
	 * @return array	with found languages as filename => real name pairs
	 */
	function _parseLanguageFiles( $dir=null, $client ) {
		$languages = array();

		if ($client == 2) {
			// Installation without subdirs!
			$languages = mosLanguage::_parseINILanguageFiles( $dir );
		} else {
			$subdirs = mosFS::listFolders( $dir );
			foreach ($subdirs as $path) {
				$langs = mosLanguage::_parseXMLLanguageFiles( $dir . $path . DIRECTORY_SEPARATOR );
				$languages = array_merge( $languages, $langs );
			}
		}

		return $languages;
	}

	/** parses INI type of files for language information
	 * @param string	directory of files
	 * @return array	with found languages as filename => real name pairs
	 */
	function _parseINILanguageFiles( $dir=null ) {
		if( $dir == null ) return null;

		$languages = array();
		$files = mosFS::listFiles( $dir, '^([_A-Za-z]*)\.ini$' );
		foreach ($files as $file) {
			if ($content = file_get_contents( $dir . $file )) {

				$langContent = mosParameters::parse( $content, false, true );

				$lang = str_replace( '.ini', '', $file );
				$name = $lang;
				if (isset( $langContent['__NAME'] )) {
					$name = $langContent['__NAME'];
				}

				$languages[$lang] = $name;
			}
		}
		return $languages;
	}

	/** parses XML type of files for language information
	 * @param string	directory of files
	 * @return array	with found languages as filename => real name pairs
	 */
	function _parseXMLLanguageFiles( $dir=null ) {
		mosFS::load( '@domit' );

		if ($dir == null ) {
			return null;
		}

		$languages = array();
		$files = mosFS::listFiles( $dir, '^([A-Za-z]*)\.xml$' );
		foreach ($files as $file) {
			if ($content = file_get_contents( $dir . $file )) {
				$xmlDoc = new DOMIT_Lite_Document();
				$xmlDoc->resolveErrors( true );
				if (!$xmlDoc->loadXML( $dir . $file, false, true )) {
					continue;
				}
				$language = &$xmlDoc->documentElement;

				// Check that it's am installation file
				if ($language->getTagName() != 'mosinstall') {
					continue;
				}

				$lang = str_replace( '.xml', '', $file );
				if ($language->getAttribute( 'type' ) == 'language') {
					$nameElement =& $language->getElementsByPath( 'name', 1 );
					$name = $nameElement->getText();
				}

				$languages[$lang] = $name;
			}
		}
		return $languages;
	}
}

/**
 * @package Joomla
 * @subpackage Language
 */
class mosLanguageFactory {
	/**
	 * Builds a list of the system languages which can be used in a select option
	 * @param string	client key for the area
	 * @param array	An array of arrays ( text, value, selected )
	 */
	function buildLanguageList( $client=2, $actualLanguage ) {
		global $_LANG;

		$list = array();

		if( is_string( $client ) ) {
			$client = mosMainFrame::getClientID( $client );
		}

		// cache activation
		if( class_exists( 'mosCache' ) ) {
			$cache =& mosCache::getCache( 'mosLanguage' );
			$langs = $cache->call( 'mosLanguage::getKnownLanguages', $client );
		} else {
			$langs = mosLanguage::getKnownLanguages( $client );
		}
		
		foreach ($langs as $lang=>$name) {
			$option = array();

			$option['text'] = $_LANG->_( $name );
			$option['value'] = $lang;
			if( $lang == $actualLanguage ) {
				$option['selected'] = 'selected="true"';
			}
			$list[] = $option;
		}

		return $list;
	}

	/**
	 * @return object A template installer object
	 */
	function &createInstaller() {
		mosFS::load( '/administrator/components/com_installer/installer.class.php' );
		mosFS::load( '/administrator/components/com_languages/languages.installer.php' );
		return new mosLanguageInstaller();
	}
}
?>