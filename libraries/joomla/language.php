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

jimport('joomla.system.object');

/**
 * Text  handling class
 *  @package  Joomla
 * @subpackage Language
 * @static
 * @since 1.1
 */
class JText
{
	/**
	 * Translates a string into the current language
	 *
	 * @access public
	 * @param string $string The string to translate
	 * @param boolean	$jsSafe		Make the result javascript safe
	 *
	 */
	function _($string, $jsSafe = false) {
		global $mainframe;
		$lang = & $mainframe->getLanguage();
		return $lang->_($string, $jsSafe);
	}

	/**
	 * Passes a string thru an sprintf
	 *
	 * @access public
	 * @param format The format string
	 * @param mixed Mixed number of arguments for the sprintf function
	 */
	function sprintf($string) {
		global $mainframe;
		$lang = & $mainframe->getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('sprintf', $args);
		}
		return '';
	}
	/**
	 * Passes a string thru an printf
	 *
	 * @access public
	 * @param format The format string
	 * @param mixed Mixed number of arguments for the sprintf function
	 */
	function printf($string) {
		global $mainframe;
		$lang = & $mainframe->getLanguage();
		$args = func_get_args();
		if (count($args) > 0) {
			$args[0] = $lang->_($args[0]);
			return call_user_func_array('printf', $args);
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
class JLanguage extends JObject
{
	/** @var boolean If true, highlights string not found */
	var $_debug 	= false;
	/** @var array 	Array holding the language metadata */
	var $_metadata 	= null;
	/** @var string Identifying string of the language */
	var $_identifyer = null;
	/** @var string The language to load */
	var $_lang = null;
	/** @var array Transaltions */
	var $_strings = null;

	/**
	* Constructor activating the default information of the language
	*
	* @access protected
	*/
	function __construct($lang = null) {
		$this->_strings = array ();

		if ($lang == null) {
			$lang = 'eng_GB';
		}

		$this->_lang= $lang;

		$this->_metadata = $this->getMetadata($this->_lang);

		//set locale based on the language tag
		//TODO : add function to display locale setting in configuration
		$locale = setlocale(LC_TIME, $this->getLocale());
		//echo $locale;

		$this->load();
	}

	/**
	 * Returns a reference to the global Language object, only creating it
	 * if it doesn't already exist.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JLanguage::getInstance([$lang);</pre>
	 *
	 * @access public
	 * @param string $lang  The language to use.
	 * @return JLanguage  The Language object.
	 */
	function & getInstance($lang) {
		static $instances;

		if (!isset ($instances)) {
			$instances = array ();
		}

		if (empty ($instances[$lang])) {
			$instances[$lang] = new JLanguage($lang);
		}

		return $instances[$lang];
	}

	/**
	* Translator function, mimics the php gettext (alias _) function
	*
	* @access public
	* @param string		$string 	The string to translate
	* @param boolean	$jsSafe		Make the result javascript safe
	* @return string	The translation of the string
	*/
	function _($string, $jsSafe = false) {

		//$key = str_replace( ' ', '_', strtoupper( trim( $string ) ) );echo '<br>'.$key;
		$key = strtoupper($string);
		$key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;
		if (isset ($this->_strings[$key])) {
			$string = $this->_debug ? "&bull;".$this->_strings[$key]."&bull;" : $this->_strings[$key];
		} else {
			if (defined($string)) {
				$string = $this->_debug ? "!!".constant($string)."!!" : constant($string);
			} else {
				$string = $this->_debug ? "??".$string."??" : $string;
			}
		}
		if ($jsSafe) {
			$string = addslashes($string);
		}
		return $string;
	}

	/**
	 * Loads a single langauge file and appends the results to the existing strings
	 *
	 * @access public
	 * @param string 	$prefix 	The prefix
	 * @param string 	$basePath  	The basepath to use
	 * $return boolean	True, if the file has succesfully loaded.
	 */
	function load( $prefix = '', $basePath = JPATH_BASE )
	{
        $path = JLanguage::getLanguagePath( $basePath, $this->_lang);

		$filename = empty( $prefix ) ?  $this->_lang : $this->_lang . '.' . $prefix ;

		$result = false;
		if (file_exists( $path . $filename .'.ini') ) {

			//NOTE : Caching is slower
			//$langGroup = 'Lang'.$this->_lang;
			//$loadCache = JFactory::getCache($langGroup, 'JCache_Language');
			//$newStrings = $loadCache->load(substr($langGroup, 4), $this, $basePath . $filename .'.ini');

			$newStrings = $this->_load( $path . $filename .'.ini' );

			if (is_array($newStrings)) {
				$this->_strings = array_merge( $this->_strings, $newStrings);
			}

			$result = true;
		}

		return $result;

	}

	/**
	* Loads a language file and returns the parsed values
	*
	* @access private
	* @param string The name of the file
	* @return mixed Array of parsed values if successful, boolean False if failed
	*/
	function _load( $filename ) {
		if (file_exists( $filename )) {
			if ($content = file_get_contents( $filename )) {
				if( $this->_identifyer === null ) {
					$this->_identifyer = basename( $filename, '.ini' );
				}
				return JParameters::parse( $content, false, true );
			}
		}

		return false;
	}

	/**
	 * Get a matadata language property
	 *
	 * @access public
	 * @param string $property	The name of the property
	 * @param mixed  $default	The default value
	 * @return mixed The value of the property
	 */
	function get($property, $default = null) {
		if (isset ($this->_metadata[$property])) {
			return $this->_metadata[$property];
		}
		return $default;
	}

	/**
	* Getter for Name
	*
	* @access public
	* @param string  $value 	An optional value
	* @return string Official name element of the language
	*/
	function getName($value = null) {
		return $this->_metadata['name'];
	}

	/**
	* Get for the langauge tag (as defined in RFC 3066)
	*
	* @access public
	* @return string The language tag
	*/
	function getTag() {
		return $this->_metadata['tag'];
	}

	/**
	* Get locale property
	*
	* @access public
	* @return string The locale property
	*/
	function getLocale() {
		$locales = explode(',', $this->_metadata['locale']);

		for($i = 0; $i < count($locales); $i++ ) {
			$locale = $locales[$i];
			$locale = trim($locale);
			$locales[$i] = $locale;
		}

		//return implode(',', $locales);
		return $locales;
	}

	/**
	* Get the RTL property
	*
	* @access public
	* @return boolean True is it an RTL language
	*/
	function isRTL($value = null) {
		return $this->_metadata['rtl'];
	}

	/**
	* Set the Debug property
	*
	* @access public
	*/
	function setDebug($debug) {
		$this->_debug = $debug;
	}

	/**
	* Get the Debug property
	*
	* @access public
	* @return boolean True is in debug mode
	*/
	function getDebug() {
		return $this->_debug;
	}

	/**
	 * Determines is a key exists
	 *
	 * @access public
	 * @param key $key	The key to check
	 * @return boolean True, if the key exists
	 */
	function hasKey($key) {
		return isset ($this->_strings[strtoupper($key)]);
	}

	/**
	 * Returns a associative array holding the metadata
	 *
	 * @access public
	 * @param string	The name of the language
	 * @return mixed	If $lang exists return key/value pair with the language metadata,
	 *  				otherwise return NULL
	 */

	function getMetadata($lang) {

		$path = JLanguage::getLanguagePath(JPATH_BASE, $lang);
		$file = $lang.'.xml';

		$result = null;
		if(JFile::exists($path.$file)) {
			$result = JLanguage::_parseXMLLanguageFile($path.$file);
		}

		return $result;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @access public
	 * @param string	$basePath 	The basepath to use
	 * @return array	key/value pair with the language file and real name
	 */
	function getKnownLanguages($basePath = JPATH_BASE) {

		$dir = JLanguage::getLanguagePath($basePath);
		$knownLanguages = JLanguage::_parseLanguageFiles($dir);

		return $knownLanguages;
	}

	/**
	 * Get the path to a language
	 *
	 * @access public
	 * @param string $basePath  The basepath to use
	 * @param string $language	The language tag
	 * @param boolean $addTrailingSlash Add a trailing slash to the pathname
	 * @return string	language related path or null
	 */
	function getLanguagePath($basePath = JPATH_BASE, $language = null, $addTrailingSlash = true) {

		$dir = $basePath.DS.'language'.DS;
		if (isset ($language)) {
			$dir .= $language.DS;
		}
		return JPath::clean($dir, $addTrailingSlash);
	}

	/** Searches for language directories within a certain base dir
	 *
	 * @access public
	 * @param string 	$dir 	directory of files
	 * @return array	Array holding the found languages as filename => real name pairs
	 */
	function _parseLanguageFiles($dir = null) {
		$languages = array ();

		$subdirs = JFolder::folders($dir);
		foreach ($subdirs as $path) {
			$langs = JLanguage::_parseXMLLanguageFiles($dir.$path.DIRECTORY_SEPARATOR);
			$languages = array_merge($languages, $langs);
		}

		return $languages;
	}

	/** parses INI type of files for language information
	 *
	 * @access public
	 * @param string	$dir 	Directory of files
	 * @return array	Array holding the found languages as filename => real name pairs
	 */
	function _parseINILanguageFiles($dir = null) {
		if ($dir == null)
			return null;

		$languages = array ();
		$files = JFolder::files($dir, '^([_A-Za-z]*)\.ini$');
		foreach ($files as $file) {
			if ($content = file_get_contents($dir.$file)) {
				$langContent = JParameters::parse($content, false, true);
				$lang = str_replace('.ini', '', $file);
				$name = $lang;
				if (isset ($langContent['__NAME'])) {
					$name = $langContent['__NAME'];
				}

				$languages[$lang] = $name;
			}
		}
		return $languages;
	}

	/** Parses XML files for language information
	 *
	 * @access public
	 * @param string	$dir	 Directory of files
	 * @return array	Array holding the found languages as filename => metadata array
	 */
	function _parseXMLLanguageFiles($dir = null) {

		if ($dir == null) {
			return null;
		}

		$languages = array ();
		$files = JFolder::files($dir, '^([_A-Za-z]*)\.xml$');
		foreach ($files as $file) {
			if ($content = file_get_contents($dir.$file)) {
				if ($metadata = JLanguage::_parseXMLLanguageFile($dir.$file)) {
					$lang = str_replace('.xml', '', $file);
					$languages[$lang] = $metadata;
				}
			}
		}
		return $languages;
	}

	/** Parse XML file for language information
	 *
	 * @access public
	 * @param string	$path	 Path to the xml files
	 * @return array	Array holding the found metadat as a key => value pair
	 */
	function _parseXMLLanguageFile($path) {

		$xmlDoc = & JFactory::getXMLParser();
		$xmlDoc->resolveErrors(true);
		if (!$xmlDoc->loadXML($path, false, true)) {
			return null;
		}
		$language = & $xmlDoc->documentElement;

		// Check that it's am installation file
		if ($language->getTagName() != 'mosinstall') {
			return null;
		}

		$metadata = array ();

		if ($language->getAttribute('type') == 'language') {
			$node = & $language->getElementsByPath('metadata', 1);

			for ($i = 0; $i < count($node->childNodes); $i ++) {
				$currNode = & $node->childNodes[$i];
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
	 *
	 * @access public
	 * @param string	Client key for the area
	 * @param string	Base path to use
	 * @param array	An array of arrays ( text, value, selected )
	 */
	function createLanguageList($actualLanguage, $basePath = JPATH_BASE) {

		$list = array ();

		// cache activation
		$cache = & JFactory::getCache('JLanguage');
		$langs = $cache->call('JLanguage::getKnownLanguages', $basePath);

		foreach ($langs as $lang => $metadata) {
			$option = array ();

			$option['text'] = JText::_( $metadata['name'] );
			$option['value'] = $lang;
			if ($lang == $actualLanguage) {
				$option['selected'] = 'selected="true"';
			}
			$list[] = $option;
		}

		return $list;
	}
}
?>
