<?php
/**
* @version		$Id$
* @package		Joomla.Framework
* @subpackage	Language
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// No direct access
defined('JPATH_BASE') or die();

/**
 * Languages/translation handler class
 *
 * @package 	Joomla.Framework
 * @subpackage	Language
 * @since		1.5
 */
class JLanguage extends JClass
{
	/**
	 * Debug language, If true, highlights if string isn't found.
	 *
	 * @var		boolean
	 * @since	1.5
	 */
	protected $_debug = false;

	/**
	 * The default language.
	 *
	 * The default language is used when a language file in the requested
	 * language does not exist.
	 *
	 * @var		string
	 * @since	1.5
	 */
	protected $_default	= 'en-GB';

	/**
	 * An array of orphaned text.
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $_orphans = array();

	/**
	 * Array holding the language metadata.
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $_metadata = null;

	/**
	 * The language to load.
	 *
	 * @var		string
	 * @since	1.5
	 */
	protected $_lang = null;

	/**
	 * List of language files that have been loaded.
	 *
	 * @var		array of arrays
	 * @since	1.5
	 */
	protected $_paths = array();

	/**
	 * Translations.
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $_strings = null;

	/**
	 * An array of used text, used during debugging.
	 *
	 * @var		array
	 * @since	1.5
	 */
	protected $_used = array();

	/**
	* Constructor activating the default information of the language.
	*/
	protected function __construct($lang = null)
	{
		$this->_strings = array();
		if ($lang == null) {
			$lang = $this->_default;
		}
		$this->setLanguage($lang);
		$this->load();
	}

	/**
	 * Returns a reference to a language object.
	 *
	 * This method must be invoked as:
	 * 		<pre>  $browser = &JLanguage::getInstance([$lang);</pre>
	 *
	 * @param	string The language to use.
	 * @return	JLanguage  The Language object.
	 * @since	1.5
	 */
	public static function &getInstance($lang)
	{
		$instance = new JLanguage($lang);
		$reference = &$instance;
		return $reference;
	}

	/**
	* Translate function, mimics the php gettext (alias _) function.
	*
	* @param	string	The string to translate.
	* @param	boolean	Make the result javascript safe.
	* @return	string	The translation of the string.
	* @since	1.5
	*/
	public function _($string, $jsSafe = false)
	{
		//$key = str_replace(' ', '_', strtoupper(trim($string)));echo '<br />'.$key;
		$key = strtoupper($string);
		$key = substr($key, 0, 1) == '_' ? substr($key, 1) : $key;

		if (isset ($this->_strings[$key]))
		{
			$string = $this->_strings[$key];

			// Store debug information
			if ($this->_debug)
			{
				$string = '&bull;' . $string . '&bull;';
				$caller = $this->_getCallerInfo();

				if (! array_key_exists($key, $this->_used)) {
					$this->_used[$key] = array();
				}

				$this->_used[$key][] = $caller;
			}

		} else {
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

		if ($jsSafe) {
			$string = addslashes($string);
		}

		return $string;
	}

	/**
	 * Transliterate function.
	 *
	 * This method processes a string and replaces all accented UTF-8 characters
	 * by unaccented ASCII-7 "equivalents".
	 *
	 * @param	string 	The string to transliterate.
	 * @return	string	The transliteration of the string.
	 * @since	1.5
	 */
	public function transliterate($string)
	{
		$string = htmlentities(utf8_decode($string));
		$string = preg_replace(
			array('/&szlig;/','/&(..)lig;/', '/&([aouAOU])uml;/','/&(.)[^;]*;/'),
			array('ss',"$1","$1".'e',"$1"),
			$string
		);
		return $string;
	}

	/**
	 * Check if a language exists.
	 *
	 * This is a simple, quick check for the directory that should contain
	 * language files for the given user.
	 *
	 * @param	string Language to check.
	 * @param	string Optional path to check.
	 * @return	boolean True if the language exists.
	 * @since	1.5
	 */
	public static function exists($lang, $basePath = JPATH_BASE)
	{
		static $paths = array();

		// Return false if no language was specified
		if (! $lang) {
			return false;
		}

		$path = $basePath . DS . 'language' . DS . $lang;

		// Return previous check results if it exists
		if (isset($paths[$path])) {
			return $paths[$path];
		}

		// Check if the language exists
		jimport('joomla.filesystem.folder');

		$paths[$path] = JFolder::exists($path);

		return $paths[$path];
	}

	/**
	 * Loads a single language file and appends the results to the existing
	 * strings.
	 *
	 * @param	string	The extension for which a language file should be
	 * loaded.
	 * @param	string 	The basepath to use.
	 * @param	string	The language to load, default null for the current
	 * language.
	 * @param	boolean Flag that will force a language to be reloaded if set to
	 * true.
	 * @return	boolean	True, if the file has successfully loaded.
	 * @since	1.5
	 */
	function load(
		$extension = 'joomla', $basePath = JPATH_BASE, $lang = null, $reload = false
	) {
		if (! $lang) {
			$lang = $this->_lang;
		}

		$path = JLanguage::getLanguagePath($basePath, $lang);

		$internal = $extension == 'joomla' || $extension == '';
		$filename = $internal ? $lang : $lang . '.' . $extension;
		$filename = $path . DS . $filename . '.ini';

		$result = false;
		if (isset($this->_paths[$extension][$filename]) && ! $reload)
		{
			// Strings for this file have already been loaded
			$result = true;
		} else {
			// Load the language file
			$result = $this->_load($filename, $extension);

			// Check if there was a problem with loading the file
			if ($result === false)
			{
				// No strings, which probably means that the language file does not exist
				$path = JLanguage::getLanguagePath($basePath, $this->_default);
				$filename = $internal ? $this->_default : $this->_default . '.' . $extension;
				$filename = $path . DS . $filename . '.ini';

				$result = $this->_load($filename, $extension, false);
			}
		}
		return $result;
	}

	/**
	* Loads a language file.
	*
	* This method will not note the successful loading of a file - use load()
	* instead.
	*
	* @param	string The name of the file.
	* @param	string The name of the extension.
	* @return	boolean True if new strings have been added to the language.
	* @see		JLanguage::load()
	* @since	1.5
	*/
	function _load($filename, $extension = 'unknown', $overwrite = true)
	{
		$result	= false;

		if ($content = @file_get_contents($filename))
		{
			$registry = new JRegistry();
			$registry->loadINI($content);
			$newStrings = $registry->toArray();

			if (is_array($newStrings))
			{
				$this->_strings = $overwrite ? array_merge($this->_strings, $newStrings)
					: array_merge($newStrings, $this->_strings);
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
	 * Get a metadata language property.
	 *
	 * @param	string The name of the property.
	 * @param	mixed  The default value.
	 * @return	mixed The value of the property.
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
	 * Determine who called JLanguage or JText.
	 *
	 * @return	array Caller information.
	 * @since	1.5
	 */
	function _getCallerInfo()
	{
		// Try to determine the source if none was provided
		if (!function_exists('debug_backtrace')) {
			return null;
		}

		$backtrace = debug_backtrace();
		$info = array();

		// Search through the backtrace to our caller
		$continue = true;
		while ($continue && next($backtrace))
		{
			$step = current($backtrace);
			$class = @$step['class'];

			// We're looking for something outside of language.php
			if ($class != 'JLanguage' && $class != 'JText') {
				$info['function'] = @$step['function'];
				$info['class'] = $class;
				$info['step'] = prev($backtrace);

				// Determine the file and name of the file
				$info['file'] = @$step['file'];
				$info['line'] = @$step['line'];

				$continue = false;
			}
		}

		return $info;
	}

	/**
	* Getter for Name.
	*
	* @return	string Official name element of the language.
	* @since	1.5
	*/
	public function getName() {
		return $this->_metadata['name'];
	}

	/**
	 * Get a list of language files that have been loaded.
	 *
	 * @param	string	An option extension name.
	 * @return	array
	 * @since	1.5
	 */
	public function getPaths($extension = null)
	{
		if (isset($extension))
		{
			if (isset($this->_paths[$extension]))
			{
				return $this->_paths[$extension];
			}
			return null;
		}
		return $this->_paths;
	}

	/**
	* Getter for PDF Font Name.
	*
	* @return	string Name of pdf font to be used.
	* @since	1.5
	*/
	public function getPdfFontName() {
		return $this->_metadata['pdffontname'];
	}

	/**
	* Getter for Windows locale code page.
	*
	* @return	string Windows locale encoding.
	* @since	1.5
	*/
	public function getWinCP() {
		return $this->_metadata['wincodepage'];
	}

	/**
	* Getter for backward compatible language name.
	*
	* @return	string Backward compatible name.
	* @since	1.5
	*/
	public function getBackwardLang() {
		return $this->_metadata['backwardlang'];
	}

	/**
	* Get for the language tag (as defined in RFC 3066).
	*
	* @return	string The language tag.
	* @since	1.5
	*/
	public function getTag() {
		return $this->_metadata['tag'];
	}

	/**
	* Get locale property.
	*
	* @return	string The locale property.
	* @since	1.5
	*/
	public function getLocale()
	{
		if (!isset($this->_metadata['locale'])) {
			return '';
		}
		$locales = explode(',', $this->_metadata['locale']);

		array_walk($locales, 'trim');

		//return implode(',', $locales);
		return $locales;
	}

	/**
	* Get the RTL property.
	*
	* @return	boolean True is it an RTL language.
	* @since	1.5
	*/
	public function isRTL() {
		return $this->_metadata['rtl'];
	}

	/**
	* Set the Debug property.
	*
	* @param    boolean The debug value.
	* @return	boolean Previous value.
	* @since	1.5
	*/
	public function setDebug($debug) {
		$previous = $this->_debug;
		$this->_debug = $debug;
		return $previous;
	}

	/**
	* Get the Debug property.
	*
	* @return	boolean True is in debug mode.
	* @since	1.5
	*/
	public function getDebug() {
		return $this->_debug;
	}

	/**
	 * Get the default language code.
	 *
	 * @return	string Language code.
	 * @since	1.5
	 */
	public function getDefault() {
		return $this->_default;
	}

	/**
	 * Set the default language code.
	 *
	 * @return	string Previous value.
	 * @since	1.5
	 */
	public function setDefault($lang) {
		$previous = $this->_default;
		$this->_default	= $lang;
		return $previous;
	}

	/**
	* Get the list of orphaned strings if being tracked.
	*
	* @return	array List of orphaned strings.
	* @since	1.5
	*/
	public function getOrphans() {
		return $this->_orphans;
	}

	/**
	 * Get the list of used strings.
	 *
	 * Used strings are those strings requested and found either as a string or
	 * a constant.
	 *
	 * @return	array	Used strings.
	 * @since	1.5
	 */
	public function getUsed() {
		return $this->_used;
	}

	/**
	 * Determines if a key exists.
	 *
	 * @param	key The key to check.
	 * @return	boolean True if the key exists.
	 * @since	1.5
	 */
	public function hasKey($key) {
		return isset ($this->_strings[strtoupper($key)]);
	}

	/**
	 * Returns a associative array holding the metadata.
	 *
	 * @param	string	The name of the language.
	 * @return	mixed	If the language exists return key/value pair with the
	 * language metadata, otherwise return NULL.
	 * @since	1.5
	 */

	public static function getMetadata($lang)
	{
		$path = JLanguage::getLanguagePath(JPATH_BASE, $lang);
		$file = $lang . '.xml';

		$result = null;
		if (is_file($path . DS . $file)) {
			$result = JLanguage::_parseXMLLanguageFile($path . DS . $file);
		}
		return $result;
	}

	/**
	 * Returns a list of known languages for an area.
	 *
	 * @param	string	The basepath to use.
	 * @return	array	Key/value pairs with the language file and real name.
	 * @since	1.5
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		$dir = JLanguage::getLanguagePath($basePath);
		$knownLanguages = JLanguage::_parseLanguageFiles($dir);
		return $knownLanguages;
	}

	/**
	 * Get the language identifier.
	 *
	 * @return  string  Language code.
	 * @since   1.6
	 */
	public function getLanguageIdentifier()
	{
		return $this->_lang;
	}

	/**
	 * Get the path to a language.
	 *
	 * @param	string The basepath to use.
	 * @param	string The language tag.
	 * @return	string	Language related path or null.
	 * @since	1.5
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		$dir = $basePath . DS . 'language';
		if (!empty($language)) {
			$dir .= DS . $language;
		}
		return $dir;
	}

	/**
	 * Set the language attributes to the given language.
	 *
	 * Once called, the language still needs to be loaded using
	 * JLanguage::load().
	 *
	 * @param	string	Language code.
	 * @return	string|boolean	Previous value if successful. False on failure.
	 * @since	1.5
	 */
	public function setLanguage($lang)
	{
		$previous = $this->_lang;
		if (($metaData = $this->getMetadata($lang)) === null) {
			return false;
		}
		$this->_metadata = $metaData;
		$this->_lang = $lang;

		//set locale based on the language tag
		//TODO : add function to display locale setting in configuration
		$locale = setlocale(LC_TIME, $this->getLocale());
		return $previous;
	}

	/**
	 * Searches for language directories within a certain base dir.
	 *
	 * @param	string 	Directory of files.
	 * @return	array	Array holding the found languages as filename => real
	 * name pairs.
	 * @since	1.5
	 */
	protected function _parseLanguageFiles($dir = null)
	{
		jimport('joomla.filesystem.folder');

		$languages = array();

		$subdirs = JFolder::folders($dir);
		foreach ($subdirs as $path) {
			$langs = JLanguage::_parseXMLLanguageFiles($dir . DS . $path);
			$languages = array_merge($languages, $langs);
		}

		return $languages;
	}

	/**
	 * Parses XML files for language information.
	 *
	 * @param	string	Directory of files.
	 * @return	array	Array holding the found languages as filename =>
	 * metadata array.
	 * @since	1.5
	 */
	protected static function _parseXMLLanguageFiles($dir = null)
	{
		if ($dir == null) {
			return null;
		}

		$languages = array();
		jimport('joomla.filesystem.folder');
		$files = JFolder::files($dir, '^([-_A-Za-z]*)\.xml$');
		foreach ($files as $file) {
			if ($content = file_get_contents($dir . DS . $file)) {
				if ($metadata = JLanguage::_parseXMLLanguageFile($dir . DS . $file)) {
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
	 * @param	string	Path to the xml files.
	 * @return	array	Array holding the found metadata as a key => value pair.
	 * @since	1.5
	 */
	protected static function _parseXMLLanguageFile($path)
	{
		$xml = &JFactory::getXMLParser('Simple');

		// Load the file
		if (!$xml || !$xml->loadFile($path)) {
			return null;
		}

		// Check that it's am metadata file
		if (!$xml->document || $xml->document->name() != 'metafile') {
			return null;
		}

		$metadata = array();

		//if ($xml->document->attributes('type') == 'language') {

			foreach ($xml->document->metadata[0]->children() as $child) {
				$metadata[$child->name()] = $child->data();
			}
		//}
		return $metadata;
	}

}
