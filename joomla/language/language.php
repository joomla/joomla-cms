<?php
/**
 * @version		$Id$
 * @package		Joomla.Framework
 * @subpackage	Language
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access.
defined('JPATH_BASE') or die;

/**
 * Allows for quoting in language .ini files.
 */
define('_QQ_', '"');


// import some libariries
jimport('joomla.filesystem.stream');

/**
 * Languages/translation handler class
 *
 * @package		Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JLanguage extends JObject
{
	/**
	 * Debug language, If true, highlights if string isn't found
	 *
	 * @var		boolean
	 * @since	1.5
	 */
	protected $debug = false;

	/**
	 * The default language
	 *
	 * The default language is used when a language file in the requested language does not exist.
	 *
	 * @var		string
	 * @since	1.5
	 */
	protected $default	= 'en-GB';

	/**
	 * An array of orphaned text
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $orphans = array();

	/**
	 * Array holding the language metadata
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $metadata = null;

	/**
	 * The language to load
	 *
	 * @var		string
	 * @since	1.5
	 */
	protected $lang = null;

	/**
	 * List of language files that have been loaded
	 *
	 * @var		array of arrays
	 * @since	1.5
	 */
	protected $paths = array();

	/**
	 * List of language files that are in error state
	 *
	 * @var		array of string
	 * @since	1.6
	 */
	protected $errorfiles = array();

	/**
	 * Translations
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $strings = null;

	/**
	 * An array of used text, used during debugging
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $used = array();

	/**
	 * Counter for number of loads
	 *
	 * @var		integer
	 * @since	1.6
	 */
	protected $counter = 0;

	/**
	 * An array used to store overrides
	 *
	 * @var		array
	 * @since	1.6
	 */
	protected $override = array();

	/**
	 * Name of the transliterator function for this language
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $transliterator = null;

	/**
	 * Name of the pluralSuffixesFunc function for this language
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $pluralSuffixesFunc = null;

	/**
	 * Name of the ignoreSearchWordsFunc function for this language
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $ignoreSearchWordsFunc = null;

	/**
	 * Constructor activating the default information of the language
	 */
	public function __construct($lang = null, $debug = false)
	{
		$this->strings = array ();

		if ($lang == null) {
			$lang = $this->default;
		}

		$this->setLanguage($lang);
		$this->setDebug($debug);

		$filename = JPATH_BASE . "/language/overrides/$lang.override.ini";
		if (file_exists($filename) && $contents = $this->parse($filename)) {
			if (is_array($contents)) {
				$this->override = $contents;
			}
			unset($contents);
		}

		// Look for a language specific localise class
		$class = str_replace('-', '_', $lang . 'Localise');
		if (!class_exists($class)) {

			// Class does not exist. Try to find it in the Site Language Folder
			$localise = JPATH_SITE . "/language/$lang/$lang.localise.php";
			if (file_exists($localise)) {
				require_once $localise;
			}
		}
		if (!class_exists($class)) {

			// Class does not exist. Try to find it in the Administrator Language Folder
			$localise = JPATH_ADMINISTRATOR . "/language/$lang/$lang.localise.php";
			if (file_exists($localise)) {
				require_once $localise;
			}
		}
		if (class_exists($class)) {
			// Class exists. Try to find a pluralSuffixes method, a transliterate method and an ignoreSearchWords method
			if (method_exists($class, 'pluralSuffixes')) {
				$this->pluralSuffixesFunc = array($class, 'pluralSuffixes');
			}
			if (method_exists($class, 'transliterate')) {
				$this->transliterator = array($class, 'transliterate');
			}
			if (method_exists($class, 'ignoreSearchWords')) {
				$this->ignoreSearchWordsFunc = array($class, 'ignoreSearchWords');
			}
		}

		$this->load();
	}

	/**
	 * Returns a language object
	 *
	 * @param	string $lang  The language to use.
	 * @param	boolean	$debug	The debug mode
	 * @return	JLanguage  The Language object.
	 * @since	1.5
	 */
	public static function getInstance($lang, $debug=false)
	{
		return new JLanguage($lang, $debug);
	}

	/**
	 * Translate function, mimics the php gettext (alias _) function
	 *
	 * @param	string		$string	The string to translate
	 * @param	boolean	$jsSafe		Make the result javascript safe
	 * @return	string	The translation of the string
	 * @since	1.5
	 */
	public function _($string, $jsSafe = false)
	{
		$key = strtoupper($string);
		if (isset ($this->strings[$key])) {
			$string = $this->debug ? '**'.$this->strings[$key].'**' : $this->strings[$key];

			// Store debug information
			if ($this->debug) {
				$caller = $this->getCallerInfo();

				if (! array_key_exists($key, $this->used)) {
					$this->used[$key] = array();
				}

				$this->used[$key][] = $caller;
			}
		} else {
			if ($this->debug) {
				$caller = $this->getCallerInfo();
				$caller['string'] = $string;

				if (! array_key_exists($key, $this->orphans)) {
					$this->orphans[$key] = array();
				}

				$this->orphans[$key][] = $caller;

				$string = '??'.$string.'??';
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
	 * @param	string	$string	The string to transliterate
	 * @return	string	The transliteration of the string
	 * @since	1.5
	 */
	public function transliterate($string)
	{
		include_once dirname(__FILE__) . '/latin_transliterate.php';

		if ($this->transliterator !== null) {
			return call_user_func($this->transliterator, $string);
		}

		$string = JLanguageTransliterate::utf8_latin_to_ascii($string);
		$string = JString::strtolower($string);

		return $string;
	}

	/**
	 * Getter for transliteration function
	 *
	 * @return	string|function Function name or the actual function for PHP 5.3
	 * @since	1.6
	 */
	public function getTransliterator()
	{
		return $this->transliterator;
	}

	/**
	 * Set the transliteration function
	 *
	 * @return	string|function Function name or the actual function for PHP 5.3
	 * @since	1.6
	 */
	public function setTransliterator($function)
	{
		$previous = $this->transliterator;
		$this->transliterator = $function;
		return $previous;
	}

	/**
	 * pluralSuffixes function
	 *
	 * This method return an array of suffixes for plural rules
	 *
	 * @param	int	$count	The count number
	 * @return	array	The array of suffixes
	 * @since	1.6
	 */
	public function pluralSuffixes($count) {
		if ($this->pluralSuffixesFunc !== null) {
			return call_user_func($this->pluralSuffixesFunc, $count);
		}
		else {
			return array((string)$count);
		}
	}

	/**
	 * Getter for pluralSuffixes function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 * @since       1.6
	 */
	public function getPluralSuffixes() {
		return $this->pluralSuffixesFunc;
	}

	/**
	 * Set the pluralSuffixes function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 * @since       1.6
	 */
	public function setPluralSuffixes($function) {
		$previous = $this->pluralSuffixesFunc;
		$this->pluralSuffixesFunc = $function;
		return $previous;
	}

	/**
	 * ignoreSearchWords function
	 *
	 * This method return an array of ignored search words
	 *
	 * @return	array	The array of ignored search words
	 * @since	1.6
	 */
	public function ignoreSearchWords() {
		if ($this->ignoreSearchWordsFunc !== null) {
			return call_user_func($this->ignoreSearchWordsFunc);
		}
		else {
			return array();
		}
	}

	/**
	 * Getter for ignoreSearchWords function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 * @since       1.6
	 */
	public function getIgnoreSearchWords() {
		return $this->ignoreSearchWordsFunc;
	}

	/**
	 * Set the ignoreSearchWords function
	 *
	 * @return      string|function Function name or the actual function for PHP 5.3
	 * @since       1.6
	 */
	public function setIgnoreSearchWords($function) {
		$previous = $this->ignoreSearchWordsFunc;
		$this->ignoreSearchWordsFunc = $function;
		return $previous;
	}

	/**
	 * Check if a language exists
	 *
	 * This is a simple, quick check for the directory that should contain language files for the given user.
	 *
	 * @param	string $lang Language to check
	 * @param	string $basePath Optional path to check
	 * @return	boolean True if the language exists
	 * @since	1.5
	 */
	public static function exists($lang, $basePath = JPATH_BASE)
	{
		static	$paths	= array();

		// Return false if no language was specified
		if (! $lang) {
			return false;
		}

		$path	= "$basePath/language/$lang";

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
	 * @param	string	$extension	The extension for which a language file should be loaded
	 * @param	string	$basePath	The basepath to use
	 * @param	string	$lang		The language to load, default null for the current language
	 * @param	boolean $reload		Flag that will force a language to be reloaded if set to true
	 * @param	boolean	$default	Flag that force the default language to be loaded if the current does not exist
	 * @return	boolean	True, if the file has successfully loaded.
	 * @since	1.5
	 */
	public function load($extension = 'joomla', $basePath = JPATH_BASE, $lang = null, $reload = false, $default = true)
	{
		if (! $lang) {
			$lang = $this->lang;
		}

		$path = self::getLanguagePath($basePath, $lang);

		$internal = $extension == 'joomla' || $extension == '';
		$filename = $internal ? $lang : $lang . '.' . $extension;
		$filename = "$path/$filename.ini";

		$result = false;
		if (isset($this->_paths[$extension][$filename]) && ! $reload) {
			// Strings for this file have already been loaded
			$result = true;
		} else {
			// Load the language file
			$result = $this->loadLanguage($filename, $extension);

			// Check if there was a problem with loading the file
			if ($result === false && $default) {
				// No strings, so either file doesn't exist or the file is invalid
				$oldFilename = $filename;

				// Check the standard file name
				$path		= self::getLanguagePath($basePath, $this->default);
				$filename = $internal ? $this->default : $this->default . '.' . $extension;
				$filename	= "$path/$filename.ini";

				// If the one we tried is different than the new name, try again
				if ($oldFilename != $filename) {
					$result = $this->loadLanguage($filename, $extension, false);
				}
			}
		}
		return $result;
	}

	/**
	 * Loads a language file
	 *
	 * This method will not note the successful loading of a file - use load() instead
	 *
	 * @param	string The name of the file
	 * @param	string The name of the extension
	 * @return	boolean True if new strings have been added to the language
	 * @see		JLanguage::load()
	 * @since	1.5
	 */
	protected function loadLanguage($filename, $extension = 'unknown', $overwrite = true)
	{

		$this->counter++;

		$result	= false;

		$strings = false;
		if (file_exists($filename)) {
			$strings = $this->parse($filename);
		}

		if ($strings) {
			if (is_array($strings)) {
				$this->strings = array_merge($this->strings, $strings);
			}
			if (is_array($strings) && count($strings)) {
				$this->strings = array_merge($this->strings, $this->override);
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
	 * Parses a language file
	 *
	 * @param	string The name of the file
	 * @since	1.6
	 */
	protected function parse($filename)
	{
		$version = phpversion();
		if($version >= "5.3.1") {
			$contents = file_get_contents($filename);
			$contents = str_replace('_QQ_','"\""',$contents);
			$strings = (array) @parse_ini_string($contents);
		} else {
			$strings = (array) @parse_ini_file($filename);
			if ($version == "5.3.0") {
				foreach($strings as $key => $string) {
					$strings[$key]=str_replace('_QQ_','"',$string);
				}
			}
		}
		if ($this->debug) {
			$this->debug = false;
			$errors = array();
			$lineNumber = 0;
			$stream = new JStream();
			$stream->open($filename);
			while(!$stream->eof())
			{
				$line = $stream->gets();
				$lineNumber++;
				if (!preg_match('/^(|(\[[^\]]*\])|([A-Z][A-Z0-9_\-]*\s*=(\s*(("[^"]*")|(_QQ_)))+))\s*(;.*)?$/',$line))
				{
					$errors[] = $lineNumber;
				}
			}
			$stream->close();
			if (count($errors)) {
				if (basename($filename)!=$this->lang.'.ini') {
					$this->errorfiles[$filename] = $filename.JText::sprintf('JERROR_PARSING_LANGUAGE_FILE',implode(', ',$errors));
				}
				else {
					$this->errorfiles[$filename] = $filename . '&nbsp;: error(s) in line(s) ' . implode(', ',$errors);
				}
			}
			$this->debug = true;
		}
		return $strings;
	}

	/**
	 * Get a matadata language property
	 *
	 * @param	string $property	The name of the property
	 * @param	mixed  $default	The default value
	 * @return	mixed The value of the property
	 * @since	1.5
	 */
	public function get($property, $default = null)
	{
		if (isset ($this->metadata[$property])) {
			return $this->metadata[$property];
		}
		return $default;
	}

	/**
	 * Determine who called JLanguage or JText
	 *
	 * @return	array Caller information
	 * @since	1.5
	 */
	protected function getCallerInfo()
	{
		// Try to determine the source if none was provided
		if (!function_exists('debug_backtrace')) {
			return null;
		}

		$backtrace	= debug_backtrace();
		$info		= array();

		// Search through the backtrace to our caller
		$continue = true;
		while ($continue && next($backtrace)) {
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
	 * @return	string Official name element of the language
	 * @since	1.5
	 */
	public function getName() {
		return $this->metadata['name'];
	}

	/**
	 * Get a list of language files that have been loaded
	 *
	 * @param	string	$extension	An option extension name
	 * @return	array
	 * @since	1.5
	 */
	public function getPaths($extension = null)
	{
		if (isset($extension)) {
			if (isset($this->_paths[$extension])) {
				return $this->_paths[$extension];
			}

			return null;
		} else {
			return $this->_paths;
		}
	}

	/**
	 * Get a list of language files that are in error state
	 *
	 * @return	array
	 * @since	1.6
	 */
	public function getErrorFiles()
	{
		return $this->errorfiles;
	}

	/**
	 * Get for the language tag (as defined in RFC 3066)
	 *
	 * @return	string The language tag
	 * @since	1.5
	 */
	public function getTag() {
		return $this->metadata['tag'];
	}

	/**
	 * Get the RTL property
	 *
	 * @return	boolean True is it an RTL language
	 * @since	1.5
	 */
	public function isRTL()
	{
		return $this->metadata['rtl'];
	}

	/**
	 * Set the Debug property
	 *
	 * @return	boolean Previous value
	 * @since	1.5
	 */
	public function setDebug($debug)
	{
		$previous	= $this->debug;
		$this->debug = $debug;
		return $previous;
	}

	/**
	 * Get the Debug property
	 *
	 * @return	boolean True is in debug mode
	 * @since	1.5
	 */
	public function getDebug()
	{
		return $this->debug;
	}

	/**
	 * Get the default language code
	 *
	 * @return	string Language code
	 * @since	1.5
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Set the default language code
	 *
	 * @return	string Previous value
	 * @since	1.5
	 */
	public function setDefault($lang)
	{
		$previous	= $this->default;
		$this->default	= $lang;
		return $previous;
	}

	/**
	 * Get the list of orphaned strings if being tracked
	 *
	 * @return	array Orphaned text
	 * @since	1.5
	 */
	public function getOrphans()
	{
		return $this->orphans;
	}

	/**
	 * Get the list of used strings
	 *
	 * Used strings are those strings requested and found either as a string or a constant
	 *
	 * @return	array	Used strings
	 * @since	1.5
	 */
	public function getUsed()
	{
		return $this->used;
	}

	/**
	 * Determines is a key exists
	 *
	 * @param	key $key	The key to check
	 * @return	boolean True, if the key exists
	 * @since	1.5
	 */
	function hasKey($string)
	{
		$key = strtoupper($string);
		return isset ($this->strings[$key]);
	}

	/**
	 * Returns a associative array holding the metadata
	 *
	 * @param	string	The name of the language
	 * @return	mixed	If $lang exists return key/value pair with the language metadata,
	 *				otherwise return NULL
	 * @since	1.5
	 */
	public static function getMetadata($lang)
	{
		$path = self::getLanguagePath(JPATH_BASE, $lang);
		$file = "$lang.xml";

		$result = null;
		if (is_file("$path/$file")) {
			$result = self::parseXMLLanguageFile("$path/$file");
		}

		return $result;
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @param	string	$basePath	The basepath to use
	 * @return	array	key/value pair with the language file and real name
	 * @since	1.5
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		$dir = self::getLanguagePath($basePath);
		$knownLanguages = self::parseLanguageFiles($dir);

		return $knownLanguages;
	}

	/**
	 * Get the path to a language
	 *
	 * @param	string $basePath  The basepath to use
	 * @param	string $language	The language tag
	 * @return	string	language related path or null
	 * @since	1.5
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		$dir = "$basePath/language";
		if (!empty($language)) {
			$dir .= "/$language";
		}
		return $dir;
	}

	/**
	 * Set the language attributes to the given language
	 *
	 * Once called, the language still needs to be loaded using JLanguage::load()
	 *
	 * @param	string	$lang	Language code
	 * @return	string	Previous value
	 * @since	1.5
	 */
	public function setLanguage($lang)
	{
		$previous			= $this->lang;
		$this->lang		= $lang;
		$this->metadata	= $this->getMetadata($this->lang);

		return $previous;
	}

	/**
	 * Searches for language directories within a certain base dir
	 *
	 * @param	string	$dir	directory of files
	 * @return	array	Array holding the found languages as filename => real name pairs
	 * @deprecated use parseLanguageFiles instead
	 * @since	1.5
	 */
	public static function _parseLanguageFiles($dir = null)
	{
		return self::parseLanguageFiles($dir);
	}

	/**
	 * Searches for language directories within a certain base dir
	 *
	 * @param	string	$dir	directory of files
	 * @return	array	Array holding the found languages as filename => real name pairs
	 * @since	1.6
	 */
	public static function parseLanguageFiles($dir = null)
	{
		jimport('joomla.filesystem.folder');

		$languages = array ();

		$subdirs = JFolder::folders($dir);
		foreach ($subdirs as $path) {
			$langs = self::parseXMLLanguageFiles("$dir/$path");
			$languages = array_merge($languages, $langs);
		}

		return $languages;
	}

	/**
	 * Parses XML files for language information
	 *
	 * @param	string	$dir	Directory of files
	 * @return	array	Array holding the found languages as filename => metadata array
	 * @deprecated use parseXMLLanguageFiles instead
	 * @since	1.5
	 */
	public static function _parseXMLLanguageFiles($dir = null)
	{
		return self::parseXMLLanguageFiles($dir);
	}

	/**
	 * Parses XML files for language information
	 *
	 * @param	string	$dir	Directory of files
	 * @return	array	Array holding the found languages as filename => metadata array
	 * @since	1.6
	 */
	public static function parseXMLLanguageFiles($dir = null)
	{
		if ($dir == null) {
			return null;
		}

		$languages = array ();
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '^([-_A-Za-z]*)\.xml$');
		foreach ($files as $file) {
			if ($content = file_get_contents("$dir/$file")) {
				if ($metadata = self::parseXMLLanguageFile("$dir/$file")) {
					$lang = str_replace('.xml', '', $file);
					$languages[$lang] = $metadata;
				}
			}
		}
		return $languages;
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param	string	$path	Path to the xml files
	 * @return	array	Array holding the found metadata as a key => value pair
	 * @deprecated use parseXMLLanguageFile instead
	 * @since	1.5
	 */
	public static function _parseXMLLanguageFile($path)
	{
		return self::parseXMLLanguageFile($path);
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param	string	$path	Path to the xml files
	 * @return	array	Array holding the found metadata as a key => value pair
	 * @since	1.6
	 */
	public static function parseXMLLanguageFile($path)
	{
		// Try to load the file
		if (!$xml = JFactory::getXML($path)) {
			return null;
		}

		// Check that it's a metadata file
		if ((string)$xml->getName() != 'metafile') {
			return null;
		}

		$metadata = array();

		foreach ($xml->metadata->children() as $child) {
			$metadata[$child->getName()] = (string) $child;
		}

		return $metadata;
	}
}

