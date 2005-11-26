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
 * Text  handling class
 *  @package  Joomla
 * @subpackage Language
 * @static
 * @since 1.1
 */
class JText {
	function _($string, $jsSafe=false) {
		global $mainframe;
		$lang =& $mainframe->getLanguage();
		return $lang->_($string, $jsSafe);
	}

	/**
	 * Passes a string thru an sprintf
	 * @param format The format string
	 * @param mixed Mixed number of arguments for the sprintf function
	 */
	function sprintf( $string ) {
		global $mainframe;
		$lang =& $mainframe->getLanguage();
		$args = func_get_args();
		if (count( $args ) > 0) {
			$args[0] = $lang->_( $args[0] );
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
		global $mainframe;
		$lang =& $mainframe->getLanguage();
		$args = func_get_args();
		if (count( $args ) > 0) {
			$args[0] = $lang->_( $args[0] );
			return call_user_func_array( 'printf', $args );
		}
		return '';
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
	/** @var array 	Array holding the language metadata */
	var $_metadata = null;
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
	function __construct( $userLang='' ) 
	{
		$this->_strings  = array();
		$this->_metadata = $this->getMetadata($userLang);
		
		$this->_defaultLang = 'eng_GB';
		$this->_userLang    = $userLang;
		
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
			$string = addslashes($string);
		}
		return $string;
	}

	/**
	 * Loads a single langauge file
	 * @param string The prefix
	 */
	function load( $prefix='') {
		$basePath = JLanguage::getLanguagePath( JPATH_BASE, $this->_userLang);
		
		$filename = empty( $prefix ) ?  $this->_userLang : $this->_userLang . '.' . $prefix ;
		if (!file_exists( $basePath . $filename .'.ini') ) {
			// roll back to default language
			$filename = empty( $prefix ) ?  $this->_defaultLang  : $this->_defaultLang . '.' . $prefix  ;
		}
		$this->_load( $basePath . $filename .'.ini' );
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
				
				return true;
			}
		}
		return false;
	}

	/**
	* @param string The name of the property
	* @param mixed  The default value
	* @return mixed The value of the property
	*/
	function get($property, $default=null) {
		if(isset($this->_metadata[$property])) {
			return $this->_metadata[$property];
		} 
		return $default;
	}
	
	/**
	* Getter for Name
	* @param string An optional value
	* @return string Official name element of the language
	*/
	function getName( $value=null ) {
		return $this->_metadata['name'];
	}

	/**
	* Get for the langauge tag (as defined in RFC 3066)
	* @return string The language tag
	*/
	function getTag( ) {
		return $this->_metadata['tag'];
	}
	/**
	* Get the RTL property
	* @return boolean True is it an RTL language
	*/
	function isRTL( $value=null ) {
		return $this->_metadata['rtl'];
	}
	
	/**
	* Set the Debug property
	*/
	function setDebug( $debug ) {
		$this->_debug = $debug;
	}
	
	/**
	* Get the Debug property
	* @return boolean True is in debug mode
	*/
	function getDebug( ) {
		return $this->_debug;
	}
	
	/**
	 * Determines is a key exists
	 */
	function hasKey( $key ) {
		return isset( $this->_strings[strtoupper( $key )] );
	}
	
	/** 
	 * Returns a associative array holding the metadata
	 *
	 * @param string	The name of the language
	 * @return array	key/value pair with the language metadata
	 */
	
	function getMetadata($lang)	{
		
		$path = JLanguage::getLanguagePath( JPATH_BASE, $lang );
		$file = $lang . '.xml';
		
		return JLanguage::_parseXMLLanguageFile( $path . $file);
	}

	/** 
	 * Returns a list of known languages for an area
	 *
	 * @param string	key of the area (front, admin, install)
	 * @return array	key/value pair with the language file and real name
	 */
	function getKnownLanguages( $basePath = JPATH_BASE ) {
	
		$dir = JLanguage::getLanguagePath( $basePath );
		$knownLanguages = JLanguage::_parseLanguageFiles( $dir );

		return $knownLanguages;
	}
	
	/**
	 * @param int The client number
	 * @return string	language related path or null
	 */
	function getLanguagePath( $basePath = JPATH_BASE, $language=null, $addTrailingSlash=true ) {
		
		$dir = $basePath .DS. 'language' . DS;
		if (isset( $language )) {
			$dir .= $language .DS;
		}
		return JPath::clean( $dir, $addTrailingSlash );
	}

	/** Searches for language directories within a certain base dir
	 * @param string	directory of files
	 * @return array	with found languages as filename => real name pairs
	 */
	function _parseLanguageFiles( $dir=null ) {
		$languages = array();

		$subdirs = JFolder::folders( $dir );
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
		$files = JFolder::files( $dir, '^([_A-Za-z]*)\.ini$' );
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
	 * @return array	with found languages as filename => metadata array
	 */
	function _parseXMLLanguageFiles( $dir=null ) {
			
		if ($dir == null ) {
			return null;
		}

		$languages = array();
		$files = JFolder::files( $dir, '^([A-Za-z]*)\.xml$' );
		foreach ($files as $file) {
			if ($content = file_get_contents( $dir . $file )) {
				if($metadata = JLanguage::_parseXMLLanguageFile($dir . $file)) {
					$lang = str_replace( '.xml', '', $file );
					$languages[$lang] = $metadata;
				}
			}
		}
		return $languages;
	}
	
	function _parseXMLLanguageFile( $path ) {
		
		$xmlDoc =& JFactory::getXMLParser();
		$xmlDoc->resolveErrors( true );
		if (!$xmlDoc->loadXML( $path, false, true )) {
			return null;
		}
		$language = &$xmlDoc->documentElement;

		// Check that it's am installation file
		if ($language->getTagName() != 'mosinstall') {
			return null;
		}

		$metadata = array();
		
		if ($language->getAttribute( 'type' ) == 'language') {
			$node =& $language->getElementsByPath( 'metadata', 1 );
			
			for ($i = 0; $i < count($node->childNodes); $i++)
			{
				$currNode =& $node->childNodes[$i];
				$metadata[$currNode->nodeName] = $currNode->getText();
			}
		}
		
		return $metadata;
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
	 * @param string	Client key for the area
	 * @param string	Base path to use
	 * @param array	An array of arrays ( text, value, selected )
	 */
	function createLanguageList( $actualLanguage, $basePath = JPATH_BASE ) {

		$list = array();

		// cache activation
		$cache =& JFactory::getCache( 'JLanguage' );
		$langs = $cache->call( 'JLanguage::getKnownLanguages', $basePath);
		
		foreach ($langs as $lang=>$metadata) {
			$option = array();

			$option['text'] = JText::_( $metadata['name'] );
			$option['value'] = $lang;
			if( $lang == $actualLanguage ) {
				$option['selected'] = 'selected="true"';
			}
			$list[] = $option;
		}

		return $list;
	}
	
	/**
	 * Builds a list of the help sites which can be used in a select option
	 * @param string	Path to an xml file
	 * @param string	Language tag to select (if exists)
	 * @param array	An array of arrays ( text, value, selected )
	 */
	 function createHelpSiteList($pathToXml, $selected = null) 
	 {
       $list = array ();

       $xmlDoc = JFactory :: getXMLParser();
       $xmlDoc->resolveErrors(true);
       $xml = file_get_contents($pathToXml);

       if ($xmlDoc->parseXML($xml, false, true)) {
           $root = & $xmlDoc->documentElement;
		   

           // Are there any languages??
           $elmSites = & $root->getElementsByPath('sites', 1);
		  
           if (is_object($elmSites )) {
			 
               $sites = $elmSites->childNodes;
               foreach ($sites as $site) {
				   
					$option = array();
					
					$option['text'] = $site->getText();
					$option['value'] = $site->getAttribute('url');
					$list[] = $option;
				}
			}
 
       }

       return $list;
   }

}
?>