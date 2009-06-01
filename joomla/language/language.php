<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Language
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * Languages/translation handler class
 *
 * @package 	Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JLanguage extends JObject
{
	/**
	 * Debug language, If true, highlights if string isn't found
	 *
	 * @var		boolean
	 * @access	protected
	 * @since	1.5
	 */
	var $_debug 	= false;

	/**
	 * The default language
	 *
	 * The default language is used when a language file in the requested language does not exist.
	 *
	 * @var		string
	 * @access	protected
	 * @since	1.5
	 */
	var $_default	= 'en-GB';

	/**
	 * An array of orphaned text
	 *
	 * @var		array
	 * @access	protected
	 * @since	1.5
	 */
	var $_orphans 	= array();

	/**
	 * Array holding the language metadata
	 *
	 * @var		array
	 * @access	protected
	 * @since	1.5
	 */
	var $_metadata 	= null;

	/**
	 * The language to load
	 *
	 * @var		string
	 * @access	protected
	 * @since	1.5
	 */
	var $_lang = null;

	/**
	 * List of language files that have been loaded
	 *
	 * @var		array of arrays
	 * @access	public
	 * @since	1.5
	 */
	var $_paths	= array();

	/**
	 * Translations
	 *
	 * @var		array
	 * @access	protected
	 * @since	1.5
	 */
	var $_strings = null;

	/**
	 * An array of used text, used during debugging
	 *
	 * @var		array
	 * @access	protected
	 * @since	1.5
	 */
	var $_used		= array();

	/**
	* Constructor activating the default information of the language
	*
	* @access	protected
	*/
	function __construct($lang = null)
	{
		$this->_strings = array ();

		if ($lang == null) {
			$lang = $this->_default;
		}

		$this->setLanguage($lang);

		$this->load();
	}

	/**
	 * Returns a reference to a language object
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JLanguage::getInstance([$lang);</pre>
	 *
	 * @access	public
	 * @param	string $lang  The language to use.
	 * @return	JLanguage  The Language object.
	 * @since	1.5
	 */
	function & getInstance($lang)
	{
		$instance = new JLanguage($lang);
		$reference = & $instance;
		return $reference;
	}

	/**
	* Translate function, mimics the php gettext (alias _) function
	*
	* @access	public
	* @param	string		$string 	The string to translate
	* @param	boolean	$jsSafe		Make the result javascript safe
	* @return	string	The translation of the string
	* @since	1.5
	*/
	function _($string, $jsSafe = false)
	{
		//$key = str_replace(' ', '_', strtoupper(trim($string)));echo '<br />'.$key;
		$key = strtoupper($string);
		$key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;

		if (isset ($this->_strings[$key]))
		{
			$string = $this->_debug ? "&bull;".$this->_strings[$key]."&bull;" : $this->_strings[$key];

			// Store debug information
			if ($this->_debug)
			{
				$caller = $this->_getCallerInfo();

				if (! array_key_exists($key, $this->_used)) {
					$this->_used[$key] = array();
				}

				$this->_used[$key][] = $caller;
			}

		}
		else
		{
			if (defined($string))
			{
				$string = $this->_debug ? '!!'.constant($string).'!!' : constant($string);

				// Store debug information
				if ($this->_debug)
				{
					$caller = $this->_getCallerInfo();

					if (! array_key_exists($key, $this->_used)) {
						$this->_used[$key] = array();
					}

					$this->_used[$key][] = $caller;
				}
			}
			else
			{
				if ($this->_debug)
				{
					$caller	= $this->_getCallerInfo();
					$caller['string'] = $string;

					if (! array_key_exists($key, $this->_orphans)) {
						$this->_orphans[$key] = array();
					}

					$this->_orphans[$key][] = $caller;

					$string = '??'.$string.'??';
				}
			}
		}

		if ($jsSafe) {
			$string = addslashes($string);
		}

		return $string;
	}

	/**
	 * Transliterate function
	 *
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents"
	 *
	 * @access	public
	 * @param	string	$string 	The string to transliterate
	 * @return	string	The transliteration of the string
	 * @since	1.5
	 */
	function transliterate($string)
	{
		$string = htmlentities(utf8_decode($string));
		$string = preg_replace(
			array('/&szlig;/','/&(..)lig;/', '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
			array('ss',"$1","$1".'e',"$1"),
			$string);

		return $string;
	}

	/**
	 * Check if a language exists
	 *
	 * This is a simple, quick check for the directory that should contain language files for the given user.
	 *
	 * @access	public
	 * @param	string $lang Language to check
	 * @param	string $basePath Optional path to check
	 * @return	boolean True if the language exists
	 * @since	1.5
	 */
	function exists($lang, $basePath = JPATH_BASE)
	{
		static	$paths	= array();

		// Return false if no language was specified
		if (! $lang) {
			return false;
		}

		$path	= $basePath.DS.'language'.DS.$lang;

		// Return previous check results if it exists
		if (isset($paths[$path]))
		{
			return $paths[$path];
		}

		// Check if the language exists
		jimport('joomla.filesystem.folder');

		$paths[$path]	= JFolder::exists($path);

		return $paths[$path];
	}

	/**
	 * Loads a single language file and appends the results to the existing strings
	 *
	 * @access	public
	 * @param	string 	$extension 	The extension for which a language file should be loaded
	 * @param	string 	$basePath  	The basepath to use
	 * @param	string	$lang		The language to load, default null for the current language
	 * @param	boolean $reload		Flag that will force a language to be reloaded if set to true
	 * @return	boolean	True, if the file has successfully loaded.
	 * @since	1.5
	 */
	function load($extension = 'joomla', $basePath = JPATH_BASE, $lang = null, $reload = false)
	{
		if (! $lang) {
			$lang = $this->_lang;
		}

		$path = JLanguage::getLanguagePath($basePath, $lang);

		$filename = ($extension == 'joomla' || $extension == '') ?  $lang : $lang . '.' . $extension ;
		$filename = $path.DS.$filename.'.ini';

		$result = false;
		if (isset($this->_paths[$extension][$filename]) && ! $reload)
		{
			// Strings for this file have already been loaded
			$result = true;
		}
		else
		{
			// Load the language file
			$result = $this->_load($filename, $extension);

			// Check if there was a problem with loading the file
			if ($result === false)
			{
				// No strings, which probably means that the language file does not exist
				$path		= JLanguage::getLanguagePath($basePath, $this->_default);
				$filename	= ($extension == 'joomla' || $extension == '') ?  $this->_default : $this->_default . '.' . $extension ;
				$filename	= $path.DS.$filename.'.ini';

				$result = $this->_load($filename, $extension, false);
			}

		}

		return $result;

	}

	/**
	* Loads a language file
	*
	* This method will not note the successful loading of a file - use load() instead
	*
	* @access	private
	* @param	string The name of the file
	* @param	string The name of the extension
	* @return	boolean True if new strings have been added to the language
	* @see		JLanguage::load()
	* @since	1.5
	*/
	function _load($filename, $extension = 'unknown', $overwrite = true)
	{
		$result	= false;

		if ($content = @file_get_contents($filename))
		{

			//Take off BOM if present in the ini file
			if ($content[0] == "\xEF" && $content[1] == "\xBB" && $content[2] == "\xBF")
            {
				$content = substr($content, 3);
		  	}

			$registry	= new JRegistry();
			$registry->loadINI($content);
			$newStrings	= $registry->toArray();

			if (is_array($newStrings))
			{
				$this->_strings = $overwrite ? array_merge($this->_strings, $newStrings) : array_merge($newStrings, $this->_strings);
				$result = true;
			}
		}

		// Record the result of loading the extension's file.
		if (! isset($this->_paths[$extension])) {
			$this->_paths[$extension] = array();
		}

		$this->_paths[$extension][$filename] = $result;

		return $result;
	}

	/**
	 * Get a matadata language property
	 *
	 * @access	public
	 * @param	string $property	The name of the property
	 * @param	mixed  $default	The default value
	 * @return	mixed The value of the property
	 * @since	1.5
	 */
	function get($property, $default = null)
	{
		if (isset ($this->_metadata[$property])) {
			return $this->_metadata[$property];
		}
		return $default;
	}

	/**
	 * Determine who called JLanguage or JText
	 *
	 * @access	private
	 * @return	array Caller information
	 * @since	1.5
	 */
	function _getCallerInfo()
	{
			// Try to determine the source if none was provided
		if (!function_exists('debug_backtrace')) {
			return null;
		}

		$backtrace	= debug_backtrace();
		$info		= array();

		// Search through the backtrace to our caller
		$continue = true;
		while ($continue && next($backtrace))
		{
			$step	= current($backtrace);
			$class	= @ $step['class'];

			// We're looking for something outside of language.php
			if ($class != 'JLanguage' && $class != 'JText') {
				$info['function']	= @ $step['function'];
				$info['class']		= $class;
				$info['step']		= prev($backtrace);

				// Determine the file and name of the file
				$info['file']		= @ $step['file'];
				$info['line']		= @ $step['line'];

				$continue = false;
			}
		}

		return $info;
	}

	/**
	* Getter for Name
	*
	* @access	public
	* @return	string Official name element of the language
	* @since	1.5
	*/
	function getName() {
		return $this->_metadata['name'];
	}

	/**
	 * Get a list of language files that have been loaded
	 *
	 * @access	public
	 * @param	string	$extension	An option extension name
	 * @return	array
	 * @since	1.5
	 */
	function getPaths($extension = null)
	{
		if (isset($extension))
		{
			if (isset($this->_paths[$extension]))
				return $this->_paths[$extension];

			return null;
		}
		else
		{
			return $this->_paths;
		}
	}

	/**
	* Get for the language tag (as defined in RFC 3066)
	*
	* @access	public
	* @return	string The language tag
	* @since	1.5
	*/
	function getTag() {
		return $this->_metadata['tag'];
	}

	/**
	* Get the RTL property
	*
	* @access	public
	* @return	boolean True is it an RTL language
	* @since	1.5
	*/
	function isRTL() {
		return $this->_metadata['rtl'];
	}

	/**
	* Set the Debug property
	*
	* @access	public
	* @return	boolean Previous value
	* @since	1.5
	*/
	function setDebug($debug) {
		$previous	= $this->_debug;
		$this->_debug = $debug;
		return $previous;
	}

	/**
	* Get the Debug property
	*
	* @access	public
	* @return	boolean True is in debug mode
	* @since	1.5
	*/
	function getDebug() {
		return $this->_debug;
	}

	/**
	 * Get the default language code
	 *
	 * @access	public
	 * @return	string Language code
	 * @since	1.5
	 */
	function getDefault() {
		return $this->_default;
	}

	/**
	 * Set the default language code
	 *
	 * @access	public
	 * @return	string Previous value
	 * @since	1.5
	 */
	function setDefault($lang) {
		$previous	= $this->_default;
		$this->_default	= $lang;
		return $previous;
	}

	/**
	* Get the list of orphaned strings if being tracked
	*
	* @access	public
	* @return	array Orphaned text
	* @since	1.5
	*/
	function getOrphans() {
		return $this->_orphans;
	}

	/**
	 * Get the list of used strings
	 *
	 * Used strings are those strings requested and found either as a string or a constant
	 *
	 * @access	public
	 * @return	array	Used strings
	 * @since	1.5
	 */
	function getUsed() {
		return $this->_used;
	}

	/**
	 * Determines is a key exists
	 *
	 * @access	public
	 * @param	key $key	The key to check
	 * @return	boolean True, if the key exists
	 * @since	1.5
	 */
	function hasKey($key) {
		return isset ($this->_strings[strtoupper($key)]);
	}

	/**
	 * Returns a associative array holding the metadata
	 *
	 * @access	public
	 * @param	string	The name of the language
	 * @return	mixed	If $lang exists return key/value pair with the language metadata,
	 *  				otherwise return NULL
	 * @since	1.5
	 */

	function getMetadata($lang)
	{
		$path = JLanguage::getLanguagePath(JPATH_BASE, $lang);
		$file = $lang.'.xml';

		$result = null;
		if (is_file($path.DS.$file)) {
			$result = JLanguage::_parseXMLLanguageFile($path.DS.$file);
		}

		return $result;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @access	public
	 * @param	string	$basePath 	The basepath to use
	 * @return	array	key/value pair with the language file and real name
	 * @since	1.5
	 */
	function getKnownLanguages($basePath = JPATH_BASE)
	{
		$dir = JLanguage::getLanguagePath($basePath);
		$knownLanguages = JLanguage::_parseLanguageFiles($dir);

		return $knownLanguages;
	}

	/**
	 * Get the path to a language
	 *
	 * @access	public
	 * @param	string $basePath  The basepath to use
	 * @param	string $language	The language tag
	 * @return	string	language related path or null
	 * @since	1.5
	 */
	function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		$dir = $basePath.DS.'language';
		if (!empty($language)) {
			$dir .= DS.$language;
		}
		return $dir;
	}

	/**
	 * Set the language attributes to the given language
	 *
	 * Once called, the language still needs to be loaded using JLanguage::load()
	 *
	 * @access	public
	 * @param	string	$lang	Language code
	 * @return	string	Previous value
	 * @since	1.5
	 */
	function setLanguage($lang)
	{
		$previous			= $this->_lang;
		$this->_lang		= $lang;
		$this->_metadata	= $this->getMetadata($this->_lang);

		return $previous;
	}

	/**
	 * Searches for language directories within a certain base dir
	 *
	 * @access	public
	 * @param	string 	$dir 	directory of files
	 * @return	array	Array holding the found languages as filename => real name pairs
	 * @since	1.5
	 */
	function _parseLanguageFiles($dir = null)
	{
		jimport('joomla.filesystem.folder');

		$languages = array ();

		$subdirs = JFolder::folders($dir);
		foreach ($subdirs as $path) {
			$langs = JLanguage::_parseXMLLanguageFiles($dir.DS.$path);
			$languages = array_merge($languages, $langs);
		}

		return $languages;
	}

	/**
	 * Parses XML files for language information
	 *
	 * @access	public
	 * @param	string	$dir	 Directory of files
	 * @return	array	Array holding the found languages as filename => metadata array
	 * @since	1.5
	 */
	function _parseXMLLanguageFiles($dir = null)
	{
		if ($dir == null) {
			return null;
		}

		$languages = array ();
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '^([-_A-Za-z]*)\.xml$');
		foreach ($files as $file) {
			if ($content = file_get_contents($dir.DS.$file)) {
				if ($metadata = JLanguage::_parseXMLLanguageFile($dir.DS.$file)) {
					$lang = str_replace('.xml', '', $file);
					$languages[$lang] = $metadata;
				}
			}
		}
		return $languages;
	}

	/**
	 * Parse XML file for language information
	 *
	 * @access	public
	 * @param	string	$path	 Path to the xml files
	 * @return	array	Array holding the found metadata as a key => value pair
	 * @since	1.5
	 */
	function _parseXMLLanguageFile($path)
	{
		$xml = & JFactory::getXMLParser('Simple');

		// Load the file
		if (!$xml || !$xml->loadFile($path)) {
			return null;
		}

		// Check that it's am metadata file
		if (!$xml->document || $xml->document->name() != 'metafile') {
			return null;
		}

		$metadata = array ();

		//if ($xml->document->attributes('type') == 'language') {

			foreach ($xml->document->metadata[0]->children() as $child) {
				$metadata[$child->name()] = $child->data();
			}
		//}
		return $metadata;
	}
}
