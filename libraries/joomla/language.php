<?php

/**
* @version $Id$
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

jimport('joomla.classes.object');

/**
* Text handling class
* @package Joomla
* @subpackage Language
* @static
* @since 1.1
*/
class JText
{
	function _($string, $jsSafe=false) {
		global $mainframe;
		$lang =& $mainframe->getLanguage();
		return $lang->_($string, $jsSafe);
	}
}

/**
* Languages/translation handler class
* @package Joomla
* @subpackage Language
* @since 1.1
*/
class JLanguage extends JObject {
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
	function __construct( $userLang='' ) {
		$this->_strings = array();

		if( isset( $this->_locale ) ) {
			setlocale (LC_TIME, $this->_locale);
		}
		
		$this->_defaultLang = 'english';
		$this->_userLang = $userLang;
		
		//load common language files
		$this->load();
	}
	
	/**      
	 * Returns a reference to the global Language object, only creating it      
	 * if it doesn't already exist.   
	 *   
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JLanguage::getInstance([$userLang);</pre>      
	 *      
	 * @param string $userLang  The language to use.      
	 * @return JLanguage  The Language object.      
	 */
	function &getInstance($userLang) 
	{
		static $instances; 
		        
		if (!isset($instances)) {             
			$instances = array();         
		}         
		
		$signature = serialize(array($userLang));         
		
		if (empty($instances[$signature])) {             
			$instances[$signature] = new JLanguage($userLang);         
		}         
		
		return $instances[$signature];
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
	 * @param string The prefix
	 */
	function load( $prefix='') {
		$basePath = JLanguage::getLanguagePath( $this->_userLang);
		
		$filename = empty( $prefix ) ?  $this->_userLang : $this->_userLang . '.' . $prefix ;
		if (!file_exists( $basePath . $filename .'.ini') ) {
			// roll back to default language
			$filename = empty( $prefix ) ?  $this->_defaultLang  : $this->_defaultLang . '.' . $prefix  ;
		}
		
		$this->_load( $basePath . $filename .'.ini' );
	}

	/**
	 * Loads the main and component language files
	 * @param string The option
	 */
	function loadAll( $option='') {
		// load primary language file
		$this->load( '');

		// load 'option'(al) language file
		$option = trim( $option );
		if ($option) {
			$this->load( $option );
		}
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
	function getLanguagePath( $language=null, $addTrailingSlash=true ) {
		
		$dir = JPATH_BASE. DIRECTORY_SEPARATOR. 'language' . DIRECTORY_SEPARATOR;
		if (isset( $language )) {
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
	function getKnownLanguages( ) {
		static $knownLanguages=null;
	
		$dir = JLanguage::getLanguagePath( );
		
		if( !isset( $knownLanguages ) ) {
			$knownLanguages = JLanguage::_parseLanguageFiles( $dir );
		}

		return $knownLanguages;
	}

	/** Searches for language directories within a certain base dir
	 * @param string	directory of files
	 * @return array	with found languages as filename => real name pairs
	 */
	function _parseLanguageFiles( $dir=null ) {
		$languages = array();

		 $subdirs = mosFS::listFolders( $dir );
		foreach ($subdirs as $path) {
			$langs = JLanguage::_parseXMLLanguageFiles( $dir . $path . DIRECTORY_SEPARATOR );
			$languages = array_merge( $languages, $langs );
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
			
		if ($dir == null ) {
			return null;
		}

		$languages = array();
		$files = mosFS::listFiles( $dir, '^([A-Za-z]*)\.xml$' );
		foreach ($files as $file) {
			if ($content = file_get_contents( $dir . $file )) {
				$xmlDoc =& JFactory::getXMLParser();
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
 * @since 1.1
 */
class JLanguageHelper {
	/**
	 * Builds a list of the system languages which can be used in a select option
	 * @param string	client key for the area
	 * @param array	An array of arrays ( text, value, selected )
	 */
	function buildLanguageList( $actualLanguage ) {

		$list = array();

		// cache activation
		$cache =& JFactory::getCache( 'JLanguage' );
		$langs = $cache->call( 'JLanguage::getKnownLanguages');

		foreach ($langs as $lang=>$name) {
			$option = array();

			$option['text'] = JText::_( $name );
			$option['value'] = $lang;
			if( $lang == $actualLanguage ) {
				$option['selected'] = 'selected="true"';
			}
			$list[] = $option;
		}

		return $list;
	}
}
?>