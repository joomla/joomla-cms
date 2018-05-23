<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

defined('JPATH_PLATFORM') or die;

use JFile;
use Joomla\CMS\Filter\InputFilter;
use Joomla\String\StringHelper;
use RuntimeException;

/**
 * Allows for quoting in language .ini files.
 */
define('_QQ_', '"');

/**
 * Languages/translation handler class
 *
 * @since  11.1
 */
class Language
{
	/**
	 * Array of Language objects
	 *
	 * @var    Language[]
	 * @since  11.1
	 */
	protected static $languages = array();

	/**
	 * Array of library language paths
	 *
	 * @var    string[]
	 * @since  3.8.0
	 */
	protected static $libraryPathCache = array();

	/**
	 * Debug language, If true, highlights if string isn't found.
	 *
	 * @var    boolean
	 * @since  11.1
	 */
	protected $debug = false;

	/**
	 * The default language, used when a language file in the requested language does not exist.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $default = 'en-GB';

	/**
	 * An array of orphaned text.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $orphans = array();

	/**
	 * Array holding the language metadata.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $metadata = null;

	/**
	 * Array holding the language locale or boolean null if none.
	 *
	 * @var    array|boolean
	 * @since  11.1
	 */
	protected $locale = null;

	/**
	 * The language to load.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $lang = null;

	/**
	 * A nested array of language files that have been loaded
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $paths = array();

	/**
	 * List of language files that are in error state
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $errorfiles = array();

	/**
	 * Translations
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $strings = array();

	/**
	 * An array of used text, used during debugging.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $used = array();

	/**
	 * Counter for number of loads.
	 *
	 * @var    integer
	 * @since  11.1
	 */
	protected $counter = 0;

	/**
	 * An array used to store overrides.
	 *
	 * @var    array
	 * @since  11.1
	 */
	protected $override = array();

	/**
	 * Name of the transliterator function for this language.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $transliterator = null;

	/**
	 * Name of the pluralSuffixesCallback function for this language.
	 *
	 * @var    callable
	 * @since  11.1
	 */
	protected $pluralSuffixesCallback = null;

	/**
	 * Name of the ignoredSearchWordsCallback function for this language.
	 *
	 * @var    callable
	 * @since  11.1
	 */
	protected $ignoredSearchWordsCallback = null;

	/**
	 * Name of the lowerLimitSearchWordCallback function for this language.
	 *
	 * @var    callable
	 * @since  11.1
	 */
	protected $lowerLimitSearchWordCallback = null;

	/**
	 * Name of the upperLimitSearchWordCallback function for this language.
	 *
	 * @var    callable
	 * @since  11.1
	 */
	protected $upperLimitSearchWordCallback = null;

	/**
	 * Name of the searchDisplayedCharactersNumberCallback function for this language.
	 *
	 * @var    callable
	 * @since  11.1
	 */
	protected $searchDisplayedCharactersNumberCallback = null;

	/**
	 * Constructor activating the default information of the language.
	 *
	 * @param   string   $lang   The language
	 * @param   boolean  $debug  Indicates if language debugging is enabled.
	 *
	 * @since   11.1
	 */
	public function __construct($lang = null, $debug = false)
	{
		$this->strings = array();

		if ($lang == null)
		{
			$lang = $this->default;
		}

		$this->lang = $lang;
		$this->metadata = LanguageHelper::getMetadata($this->lang);
		$this->setDebug($debug);

		$filename = JPATH_BASE . "/language/overrides/$lang.override.ini";

		if (file_exists($filename) && $contents = $this->parse($filename))
		{
			if (is_array($contents))
			{
				// Sort the underlying heap by key values to optimize merging
				ksort($contents, SORT_STRING);
				$this->override = $contents;
			}

			unset($contents);
		}

		// Look for a language specific localise class
		$class = str_replace('-', '_', $lang . 'Localise');
		$paths = array();

		if (defined('JPATH_SITE'))
		{
			// Note: Manual indexing to enforce load order.
			$paths[0] = JPATH_SITE . "/language/overrides/$lang.localise.php";
			$paths[2] = JPATH_SITE . "/language/$lang/$lang.localise.php";
		}

		if (defined('JPATH_ADMINISTRATOR'))
		{
			// Note: Manual indexing to enforce load order.
			$paths[1] = JPATH_ADMINISTRATOR . "/language/overrides/$lang.localise.php";
			$paths[3] = JPATH_ADMINISTRATOR . "/language/$lang/$lang.localise.php";
		}

		ksort($paths);
		$path = reset($paths);

		while (!class_exists($class) && $path)
		{
			if (file_exists($path))
			{
				require_once $path;
			}

			$path = next($paths);
		}

		if (class_exists($class))
		{
			/**
			 * Class exists. Try to find
			 * -a transliterate method,
			 * -a getPluralSuffixes method,
			 * -a getIgnoredSearchWords method
			 * -a getLowerLimitSearchWord method
			 * -a getUpperLimitSearchWord method
			 * -a getSearchDisplayCharactersNumber method
			 */
			if (method_exists($class, 'transliterate'))
			{
				$this->transliterator = array($class, 'transliterate');
			}

			if (method_exists($class, 'getPluralSuffixes'))
			{
				$this->pluralSuffixesCallback = array($class, 'getPluralSuffixes');
			}

			if (method_exists($class, 'getIgnoredSearchWords'))
			{
				$this->ignoredSearchWordsCallback = array($class, 'getIgnoredSearchWords');
			}

			if (method_exists($class, 'getLowerLimitSearchWord'))
			{
				$this->lowerLimitSearchWordCallback = array($class, 'getLowerLimitSearchWord');
			}

			if (method_exists($class, 'getUpperLimitSearchWord'))
			{
				$this->upperLimitSearchWordCallback = array($class, 'getUpperLimitSearchWord');
			}

			if (method_exists($class, 'getSearchDisplayedCharactersNumber'))
			{
				$this->searchDisplayedCharactersNumberCallback = array($class, 'getSearchDisplayedCharactersNumber');
			}
		}

		$this->load();
	}

	/**
	 * Returns a language object.
	 *
	 * @param   string   $lang   The language to use.
	 * @param   boolean  $debug  The debug mode.
	 *
	 * @return  Language  The Language object.
	 *
	 * @since   11.1
	 */
	public static function getInstance($lang, $debug = false)
	{
		if (!isset(self::$languages[$lang . $debug]))
		{
			self::$languages[$lang . $debug] = new Language($lang, $debug);
		}

		return self::$languages[$lang . $debug];
	}

	/**
	 * Translate function, mimics the php gettext (alias _) function.
	 *
	 * The function checks if $jsSafe is true, then if $interpretBackslashes is true.
	 *
	 * @param   string   $string                The string to translate
	 * @param   boolean  $jsSafe                Make the result javascript safe
	 * @param   boolean  $interpretBackSlashes  Interpret \t and \n
	 *
	 * @return  string  The translation of the string
	 *
	 * @since   11.1
	 */
	public function _($string, $jsSafe = false, $interpretBackSlashes = true)
	{
		// Detect empty string
		if ($string == '')
		{
			return '';
		}

		$key = strtoupper($string);

		if (isset($this->strings[$key]))
		{
			$string = $this->debug ? '**' . $this->strings[$key] . '**' : $this->strings[$key];

			// Store debug information
			if ($this->debug)
			{
				$caller = $this->getCallerInfo();

				if (!array_key_exists($key, $this->used))
				{
					$this->used[$key] = array();
				}

				$this->used[$key][] = $caller;
			}
		}
		else
		{
			if ($this->debug)
			{
				$caller = $this->getCallerInfo();
				$caller['string'] = $string;

				if (!array_key_exists($key, $this->orphans))
				{
					$this->orphans[$key] = array();
				}

				$this->orphans[$key][] = $caller;

				$string = '??' . $string . '??';
			}
		}

		if ($jsSafe)
		{
			// Javascript filter
			$string = addslashes($string);
		}
		elseif ($interpretBackSlashes)
		{
			if (strpos($string, '\\') !== false)
			{
				// Interpret \n and \t characters
				$string = str_replace(array('\\\\', '\t', '\n'), array("\\", "\t", "\n"), $string);
			}
		}

		return $string;
	}

	/**
	 * Transliterate function
	 *
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents".
	 *
	 * @param   string  $string  The string to transliterate.
	 *
	 * @return  string  The transliteration of the string.
	 *
	 * @since   11.1
	 */
	public function transliterate($string)
	{
		if ($this->transliterator !== null)
		{
			return call_user_func($this->transliterator, $string);
		}

		$string = Transliterate::utf8_latin_to_ascii($string);
		$string = StringHelper::strtolower($string);

		return $string;
	}

	/**
	 * Getter for transliteration function
	 *
	 * @return  callable  The transliterator function
	 *
	 * @since   11.1
	 */
	public function getTransliterator()
	{
		return $this->transliterator;
	}

	/**
	 * Set the transliteration function.
	 *
	 * @param   callable  $function  Function name or the actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setTransliterator($function)
	{
		$previous = $this->transliterator;
		$this->transliterator = $function;

		return $previous;
	}

	/**
	 * Returns an array of suffixes for plural rules.
	 *
	 * @param   integer  $count  The count number the rule is for.
	 *
	 * @return  array    The array of suffixes.
	 *
	 * @since   11.1
	 */
	public function getPluralSuffixes($count)
	{
		if ($this->pluralSuffixesCallback !== null)
		{
			return call_user_func($this->pluralSuffixesCallback, $count);
		}
		else
		{
			return array((string) $count);
		}
	}

	/**
	 * Getter for pluralSuffixesCallback function.
	 *
	 * @return  callable  Function name or the actual function.
	 *
	 * @since   11.1
	 */
	public function getPluralSuffixesCallback()
	{
		return $this->pluralSuffixesCallback;
	}

	/**
	 * Set the pluralSuffixes function.
	 *
	 * @param   callable  $function  Function name or actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setPluralSuffixesCallback($function)
	{
		$previous = $this->pluralSuffixesCallback;
		$this->pluralSuffixesCallback = $function;

		return $previous;
	}

	/**
	 * Returns an array of ignored search words
	 *
	 * @return  array  The array of ignored search words.
	 *
	 * @since   11.1
	 */
	public function getIgnoredSearchWords()
	{
		if ($this->ignoredSearchWordsCallback !== null)
		{
			return call_user_func($this->ignoredSearchWordsCallback);
		}
		else
		{
			return array();
		}
	}

	/**
	 * Getter for ignoredSearchWordsCallback function.
	 *
	 * @return  callable  Function name or the actual function.
	 *
	 * @since   11.1
	 */
	public function getIgnoredSearchWordsCallback()
	{
		return $this->ignoredSearchWordsCallback;
	}

	/**
	 * Setter for the ignoredSearchWordsCallback function
	 *
	 * @param   callable  $function  Function name or actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setIgnoredSearchWordsCallback($function)
	{
		$previous = $this->ignoredSearchWordsCallback;
		$this->ignoredSearchWordsCallback = $function;

		return $previous;
	}

	/**
	 * Returns a lower limit integer for length of search words
	 *
	 * @return  integer  The lower limit integer for length of search words (3 if no value was set for a specific language).
	 *
	 * @since   11.1
	 */
	public function getLowerLimitSearchWord()
	{
		if ($this->lowerLimitSearchWordCallback !== null)
		{
			return call_user_func($this->lowerLimitSearchWordCallback);
		}
		else
		{
			return 3;
		}
	}

	/**
	 * Getter for lowerLimitSearchWordCallback function
	 *
	 * @return  callable  Function name or the actual function.
	 *
	 * @since   11.1
	 */
	public function getLowerLimitSearchWordCallback()
	{
		return $this->lowerLimitSearchWordCallback;
	}

	/**
	 * Setter for the lowerLimitSearchWordCallback function.
	 *
	 * @param   callable  $function  Function name or actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setLowerLimitSearchWordCallback($function)
	{
		$previous = $this->lowerLimitSearchWordCallback;
		$this->lowerLimitSearchWordCallback = $function;

		return $previous;
	}

	/**
	 * Returns an upper limit integer for length of search words
	 *
	 * @return  integer  The upper limit integer for length of search words (200 if no value was set or if default value is < 200).
	 *
	 * @since   11.1
	 */
	public function getUpperLimitSearchWord()
	{
		if ($this->upperLimitSearchWordCallback !== null && call_user_func($this->upperLimitSearchWordCallback) > 200)
		{
			return call_user_func($this->upperLimitSearchWordCallback);
		}

		return 200;
	}

	/**
	 * Getter for upperLimitSearchWordCallback function
	 *
	 * @return  callable  Function name or the actual function.
	 *
	 * @since   11.1
	 */
	public function getUpperLimitSearchWordCallback()
	{
		return $this->upperLimitSearchWordCallback;
	}

	/**
	 * Setter for the upperLimitSearchWordCallback function
	 *
	 * @param   callable  $function  Function name or the actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setUpperLimitSearchWordCallback($function)
	{
		$previous = $this->upperLimitSearchWordCallback;
		$this->upperLimitSearchWordCallback = $function;

		return $previous;
	}

	/**
	 * Returns the number of characters displayed in search results.
	 *
	 * @return  integer  The number of characters displayed (200 if no value was set for a specific language).
	 *
	 * @since   11.1
	 */
	public function getSearchDisplayedCharactersNumber()
	{
		if ($this->searchDisplayedCharactersNumberCallback !== null)
		{
			return call_user_func($this->searchDisplayedCharactersNumberCallback);
		}
		else
		{
			return 200;
		}
	}

	/**
	 * Getter for searchDisplayedCharactersNumberCallback function
	 *
	 * @return  callable  Function name or the actual function.
	 *
	 * @since   11.1
	 */
	public function getSearchDisplayedCharactersNumberCallback()
	{
		return $this->searchDisplayedCharactersNumberCallback;
	}

	/**
	 * Setter for the searchDisplayedCharactersNumberCallback function.
	 *
	 * @param   callable  $function  Function name or the actual function.
	 *
	 * @return  callable  The previous function.
	 *
	 * @since   11.1
	 */
	public function setSearchDisplayedCharactersNumberCallback($function)
	{
		$previous = $this->searchDisplayedCharactersNumberCallback;
		$this->searchDisplayedCharactersNumberCallback = $function;

		return $previous;
	}

	/**
	 * Checks if a language exists.
	 *
	 * This is a simple, quick check for the directory that should contain language files for the given user.
	 *
	 * @param   string  $lang      Language to check.
	 * @param   string  $basePath  Optional path to check.
	 *
	 * @return  boolean  True if the language exists.
	 *
	 * @since   11.1
	 * @deprecated   3.7.0, use LanguageHelper::exists() instead.
	 */
	public static function exists($lang, $basePath = JPATH_BASE)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::exists() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::exists($lang, $basePath);
	}

	/**
	 * Smart language file loader, appending the results to the existing strings.
	 *
	 * A few notes on what $alternate does and when it works.
	 *
	 * - It only works when you are loading the language for a component, module, plugin, library or template extension.
	 * - The $extension must be provided in the Joomla! canonical format, i.e. com_example, mod_example,
	 *   plg_folder_something (e.g. plg_system_example, plg_user_example and so on), lib_example or tpl_example.
	 * - The $basePath must be JPATH_SITE (frontend language), JPATH_ADMINISTRATOR (backend language) or JPATH_BASE
	 *   (frontend or backend language, depending on what we're running right now).
	 * - The load order of languages is as follows (each loaded item is overridden by the next loaded item):
	 *     i.   extension-specific default language e.g. components/com_example/language/en-GB/en-GB.com_example.ini
	 *     ii.  system-wide default language e.g. language/en-GB/en-GB.com_example.ini
	 *     iii. extension-specific current language e.g. language/fr-FR/fr-FR.com_example.ini
	 *     iv.  system-wide  current language e.g. language/fr-FR/fr-FR.com_example.ini
	 *   As a result system-wide languages (provided with user-installable language packs i.e. language ZIP files)
	 *   override the extension-specific language files provided with the extension itself.
	 * - The default language files are ONLY loaded when $default is true *and* Debug Site is set to No in Global
	 *   Configuration. In any other case only items ii and iv listed above are loaded.
	 *
	 * If you are a developer you are recommended to load language files in your extension with the following one-liner,
	 * depending on whether you have a component, module, plugin, library or template respectively:
	 *
	 * JFactory::getLanguage()->load('com_example');
	 * JFactory::getLanguage()->load('mod_example');
	 * JFactory::getLanguage()->load('plg_folder_example');
	 * JFactory::getLanguage()->load('lib_example');
	 * JFactory::getLanguage()->load('tpl_example');
	 *
	 * @param   string   $extension  The extension for which a language file should be loaded.
	 * @param   string   $basePath   The base language load path to use: JPATH_BASE (autodetect frontend or backend),
	 *                               JPATH_SITE (frontend languages), JPATH_ADMINISTRATOR (backend languages) or any
	 *                               custom path if you are loading language files from a bespoke path.
	 * @param   string   $lang       The language to load, default null for the current language.
	 * @param   boolean  $reload     Flag that will force a language to be reloaded if set to true.
	 * @param   boolean  $default    Flag that force the default language to be loaded if the current does not exist.
	 * @param   boolean  $alternate  Also load files from the folder inside the extension's main installation folder.
	 *
	 * @return  boolean  True if the file has successfully loaded.
	 *
	 * @since   11.1
	 */
	public function load($extension = 'joomla', $basePath = JPATH_BASE, $lang = null, $reload = false, $default = true, $alternate = true)
	{
		// If language is null use the current language.
		if (!$lang)
		{
			$lang = $this->lang;
		}

		/**
		 * An empty extension name is a deprecated way to denote "joomla" a.k.a. internal language (see $internal
		 * below). We convert it to the string literal 'joomla' to let our auto-load code work correctly.
		 */
		if ($extension == '')
		{
			$extension = 'joomla';
		}

		/**
		 * Some developers -and core code- passes null to $basePath. Obviously the developer missed $basePath and tried
		 * passing null to $lang (or they just didn't read the code to understand why null in $basePath will case all
		 * sorts of issues). Let's same these poor developers from their mistakes.
		 */
		if (is_null($basePath))
		{
			$basePath = JPATH_BASE;
		}

		/**
		 * Load the default language first if we're not debugging and a non-default language is requested to be loaded
		 * with $default set to true. This allows us to fill in missing language strings in the current language with
		 * translations from the site's default language.
		 */
		$mustLoadDefault = !$this->debug && ($lang != $this->default) && $default;

		// Auto-load extension-specific languages
		if ($alternate && in_array($basePath, array(JPATH_BASE, JPATH_SITE)))
		{
			try
			{
				$extensionPath = self::getExtensionLanguageFolder($extension, $basePath);
			}
			catch (RuntimeException $e)
			{
				$extensionPath = null;
			}

			$ret = false;

			// Extension-specific default language e.g. components/com_example/language/en-GB/en-GB.com_example.ini
			if ($mustLoadDefault && !empty($extensionPath))
			{
				$ret = $ret || $this->load($extension, $extensionPath, $this->default, $reload, false, false);
			}

			// System-wide default language e.g. language/en-GB/en-GB.com_example.ini
			if ($mustLoadDefault)
			{
				$ret = $ret || $this->load($extension, $basePath, $this->default, $reload, false, false);
			}

			// Extension-specific current language e.g. language/fr-FR/fr-FR.com_example.ini
			if (!empty($extensionPath))
			{
				$ret = $ret || $this->load($extension, $extensionPath, $lang, $reload, false, false);
			}

			// System-wide  current language e.g. language/fr-FR/fr-FR.com_example.ini
			$ret = $ret || $this->load($extension, $basePath, $lang, $reload, false, false);

			return $ret;
		}

		if ($mustLoadDefault)
		{
			$this->load($extension, $basePath, $this->default, false, true);
		}

		$path     = LanguageHelper::getLanguagePath($basePath, $lang);
		$internal = $extension == 'joomla' || $extension == '';
		$filename = $internal ? $lang : $lang . '.' . $extension;
		$filename = "$path/$filename.ini";

		// If the language file is already loaded and we're not asked to forcibly reload it then return the cached result
		if (isset($this->paths[$extension][$filename]) && !$reload)
		{
			return $this->paths[$extension][$filename];
		}

		// Load the language file
		$result = $this->loadLanguage($filename, $extension);

		/**
		 * Return if the file loaded correctly -OR- if it failed to load but we're told not to load the default language
		 * -OR- if it failed but we're in debug mode. Do note that when Debug Language is set to Yes we are not falling
		 * back to the default language. We show untranslated strings so the developer can spot them and fix them.
		 */
		if (($result !== false) || !$default || $this->debug)
		{
			return $result;
		}

		/**
		 * If we're here, loading the language failed (missing or invalid file), Debug Language is set to No, therefore
		 * we have to load the default language.
		 */

		// Remember which file we tried -but failed- to load
		$oldFilename = $filename;

		// Find the default language file
		$path     = LanguageHelper::getLanguagePath($basePath, $this->default);
		$filename = $internal ? $this->default : $this->default . '.' . $extension;
		$filename = "$path/$filename.ini";

		// The file we failed to load is the same file as the default one. We have to give up.
		if ($oldFilename == $filename)
		{
			return $result;
		}

		// Finally, try to load the default language file and return its result
		return $this->loadLanguage($filename, $extension);
	}

	/**
	 * Loads a language file.
	 *
	 * This method will not note the successful loading of a file - use load() instead.
	 *
	 * @param   string  $filename   The name of the file.
	 * @param   string  $extension  The name of the extension.
	 *
	 * @return  boolean  True if new strings have been added to the language
	 *
	 * @see     Language::load()
	 * @since   11.1
	 */
	protected function loadLanguage($filename, $extension = 'unknown')
	{
		$this->counter++;

		$result = false;
		$strings = false;

		if (file_exists($filename))
		{
			$strings = $this->parse($filename);
		}

		if (is_array($strings) && count($strings))
		{
			$this->strings = array_replace($this->strings, $strings, $this->override);
			$result = true;
		}

		// Record the result of loading the extension's file.
		if (!isset($this->paths[$extension]))
		{
			$this->paths[$extension] = array();
		}

		$this->paths[$extension][$filename] = $result;

		return $result;
	}

	/**
	 * Parses a language file.
	 *
	 * @param   string  $filename  The name of the file.
	 *
	 * @return  array  The array of parsed strings.
	 *
	 * @since   11.1
	 */
	protected function parse($filename)
	{
		// Capture hidden PHP errors from the parsing.
		if ($this->debug)
		{
			// See https://secure.php.net/manual/en/reserved.variables.phperrormsg.php
			$php_errormsg = null;

			$trackErrors = ini_get('track_errors');
			ini_set('track_errors', true);
		}

		// This was required for https://github.com/joomla/joomla-cms/issues/17198 but not sure what server setup
		// issue it is solving
		$disabledFunctions = explode(',', ini_get('disable_functions'));
		$isParseIniFileDisabled = in_array('parse_ini_file', array_map('trim', $disabledFunctions));

		if (!function_exists('parse_ini_file') || $isParseIniFileDisabled)
		{
			$contents = file_get_contents($filename);
			$contents = str_replace('_QQ_', '"\""', $contents);
			$strings = @parse_ini_string($contents);
		}
		else
		{
			$strings = @parse_ini_file($filename);
		}

		if (!is_array($strings))
		{
			$strings = array();
		}

		// Restore error tracking to what it was before.
		if ($this->debug)
		{
			ini_set('track_errors', $trackErrors);

			$this->debugFile($filename);
		}

		return $strings;
	}

	/**
	 * Debugs a language file
	 *
	 * @param   string  $filename  Absolute path to the file to debug
	 *
	 * @return  integer  A count of the number of parsing errors
	 *
	 * @since   3.6.3
	 * @throws  \InvalidArgumentException
	 */
	public function debugFile($filename)
	{
		// Make sure our file actually exists
		if (!file_exists($filename))
		{
			throw new \InvalidArgumentException(
				sprintf('Unable to locate file "%s" for debugging', $filename)
			);
		}

		// Initialise variables for manually parsing the file for common errors.
		$blacklist = array('YES', 'NO', 'NULL', 'FALSE', 'ON', 'OFF', 'NONE', 'TRUE');
		$debug = $this->getDebug();
		$this->debug = false;
		$errors = array();
		$php_errormsg = null;

		// Open the file as a stream.
		$file = new \SplFileObject($filename);

		foreach ($file as $lineNumber => $line)
		{
			// Avoid BOM error as BOM is OK when using parse_ini.
			if ($lineNumber == 0)
			{
				$line = str_replace("\xEF\xBB\xBF", '', $line);
			}

			$line = trim($line);

			// Ignore comment lines.
			if (!strlen($line) || $line['0'] == ';')
			{
				continue;
			}

			// Ignore grouping tag lines, like: [group]
			if (preg_match('#^\[[^\]]*\](\s*;.*)?$#', $line))
			{
				continue;
			}

			// Remove the "_QQ_" from the equation
			$line = str_replace('"_QQ_"', '', $line);
			$realNumber = $lineNumber + 1;

			// Check for any incorrect uses of _QQ_.
			if (strpos($line, '_QQ_') !== false)
			{
				$errors[] = $realNumber;
				continue;
			}

			// Check for odd number of double quotes.
			if (substr_count($line, '"') % 2 != 0)
			{
				$errors[] = $realNumber;
				continue;
			}

			// Check that the line passes the necessary format.
			if (!preg_match('#^[A-Z][A-Z0-9_\*\-\.]*\s*=\s*".*"(\s*;.*)?$#', $line))
			{
				$errors[] = $realNumber;
				continue;
			}

			// Check that the key is not in the blacklist.
			$key = strtoupper(trim(substr($line, 0, strpos($line, '='))));

			if (in_array($key, $blacklist))
			{
				$errors[] = $realNumber;
			}
		}

		// Check if we encountered any errors.
		if (count($errors))
		{
			$this->errorfiles[$filename] = $filename . ' : error(s) in line(s) ' . implode(', ', $errors);
		}
		elseif ($php_errormsg)
		{
			// We didn't find any errors but there's probably a parse notice.
			$this->errorfiles['PHP' . $filename] = 'PHP parser errors :' . $php_errormsg;
		}

		$this->debug = $debug;

		return count($errors);
	}

	/**
	 * Get a metadata language property.
	 *
	 * @param   string  $property  The name of the property.
	 * @param   mixed   $default   The default value.
	 *
	 * @return  mixed  The value of the property.
	 *
	 * @since   11.1
	 */
	public function get($property, $default = null)
	{
		if (isset($this->metadata[$property]))
		{
			return $this->metadata[$property];
		}

		return $default;
	}

	/**
	 * Determine who called Language or JText.
	 *
	 * @return  array  Caller information.
	 *
	 * @since   11.1
	 */
	protected function getCallerInfo()
	{
		// Try to determine the source if none was provided
		if (!function_exists('debug_backtrace'))
		{
			return;
		}

		$backtrace = debug_backtrace();
		$info = array();

		// Search through the backtrace to our caller
		$continue = true;

		while ($continue && next($backtrace))
		{
			$step = current($backtrace);
			$class = @ $step['class'];

			// We're looking for something outside of language.php
			if ($class != '\\Joomla\\CMS\\Language\\Language' && $class != 'JText')
			{
				$info['function'] = @ $step['function'];
				$info['class'] = $class;
				$info['step'] = prev($backtrace);

				// Determine the file and name of the file
				$info['file'] = @ $step['file'];
				$info['line'] = @ $step['line'];

				$continue = false;
			}
		}

		return $info;
	}

	/**
	 * Getter for Name.
	 *
	 * @return  string  Official name element of the language.
	 *
	 * @since   11.1
	 */
	public function getName()
	{
		return $this->metadata['name'];
	}

	/**
	 * Get a list of language files that have been loaded.
	 *
	 * @param   string  $extension  An optional extension name.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getPaths($extension = null)
	{
		if (isset($extension))
		{
			if (isset($this->paths[$extension]))
			{
				return $this->paths[$extension];
			}

			return;
		}
		else
		{
			return $this->paths;
		}
	}

	/**
	 * Get a list of language files that are in error state.
	 *
	 * @return  array
	 *
	 * @since   11.1
	 */
	public function getErrorFiles()
	{
		return $this->errorfiles;
	}

	/**
	 * Getter for the language tag (as defined in RFC 3066)
	 *
	 * @return  string  The language tag.
	 *
	 * @since   11.1
	 */
	public function getTag()
	{
		return $this->metadata['tag'];
	}

	/**
	 * Getter for the calendar type
	 *
	 * @return  string  The calendar type.
	 *
	 * @since   3.7.0
	 */
	public function getCalendar()
	{
		if (isset($this->metadata['calendar']))
		{
			return $this->metadata['calendar'];
		}
		else
		{
			return 'gregorian';
		}
	}

	/**
	 * Get the RTL property.
	 *
	 * @return  boolean  True is it an RTL language.
	 *
	 * @since   11.1
	 */
	public function isRtl()
	{
		return (bool) $this->metadata['rtl'];
	}

	/**
	 * Set the Debug property.
	 *
	 * @param   boolean  $debug  The debug setting.
	 *
	 * @return  boolean  Previous value.
	 *
	 * @since   11.1
	 */
	public function setDebug($debug)
	{
		$previous = $this->debug;
		$this->debug = (boolean) $debug;

		return $previous;
	}

	/**
	 * Get the Debug property.
	 *
	 * @return  boolean  True is in debug mode.
	 *
	 * @since   11.1
	 */
	public function getDebug()
	{
		return $this->debug;
	}

	/**
	 * Get the default language code.
	 *
	 * @return  string  Language code.
	 *
	 * @since   11.1
	 */
	public function getDefault()
	{
		return $this->default;
	}

	/**
	 * Set the default language code.
	 *
	 * @param   string  $lang  The language code.
	 *
	 * @return  string  Previous value.
	 *
	 * @since   11.1
	 */
	public function setDefault($lang)
	{
		$previous = $this->default;
		$this->default = $lang;

		return $previous;
	}

	/**
	 * Get the list of orphaned strings if being tracked.
	 *
	 * @return  array  Orphaned text.
	 *
	 * @since   11.1
	 */
	public function getOrphans()
	{
		return $this->orphans;
	}

	/**
	 * Get the list of used strings.
	 *
	 * Used strings are those strings requested and found either as a string or a constant.
	 *
	 * @return  array  Used strings.
	 *
	 * @since   11.1
	 */
	public function getUsed()
	{
		return $this->used;
	}

	/**
	 * Determines is a key exists.
	 *
	 * @param   string  $string  The key to check.
	 *
	 * @return  boolean  True, if the key exists.
	 *
	 * @since   11.1
	 */
	public function hasKey($string)
	{
		$key = strtoupper($string);

		return isset($this->strings[$key]);
	}

	/**
	 * Returns an associative array holding the metadata.
	 *
	 * @param   string  $lang  The name of the language.
	 *
	 * @return  mixed  If $lang exists return key/value pair with the language metadata, otherwise return NULL.
	 *
	 * @since   11.1
	 * @deprecated   3.7.0, use LanguageHelper::getMetadata() instead.
	 */
	public static function getMetadata($lang)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::getMetadata() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::getMetadata($lang);
	}

	/**
	 * Returns a list of known languages for an area
	 *
	 * @param   string  $basePath  The basepath to use
	 *
	 * @return  array  key/value pair with the language file and real name.
	 *
	 * @since   11.1
	 * @deprecated   3.7.0, use LanguageHelper::getKnownLanguages() instead.
	 */
	public static function getKnownLanguages($basePath = JPATH_BASE)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::getKnownLanguages() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::getKnownLanguages($basePath);
	}

	/**
	 * Get the path to a language
	 *
	 * @param   string  $basePath  The basepath to use.
	 * @param   string  $language  The language tag.
	 *
	 * @return  string  language related path or null.
	 *
	 * @since   11.1
	 * @deprecated   3.7.0, use LanguageHelper::getLanguagePath() instead.
	 */
	public static function getLanguagePath($basePath = JPATH_BASE, $language = null)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::getLanguagePath() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::getLanguagePath($basePath, $language);
	}

	/**
	 * Set the language attributes to the given language.
	 *
	 * Once called, the language still needs to be loaded using Language::load().
	 *
	 * @param   string  $lang  Language code.
	 *
	 * @return  string  Previous value.
	 *
	 * @since   11.1
	 * @deprecated  4.0 (CMS) - Instantiate a new Language object instead
	 */
	public function setLanguage($lang)
	{
		\JLog::add(__METHOD__ . ' is deprecated. Instantiate a new Language object instead.', \JLog::WARNING, 'deprecated');

		$previous = $this->lang;
		$this->lang = $lang;
		$this->metadata = LanguageHelper::getMetadata($this->lang);

		return $previous;
	}

	/**
	 * Get the language locale based on current language.
	 *
	 * @return  array  The locale according to the language.
	 *
	 * @since   11.1
	 */
	public function getLocale()
	{
		if (!isset($this->locale))
		{
			$locale = str_replace(' ', '', isset($this->metadata['locale']) ? $this->metadata['locale'] : '');

			if ($locale)
			{
				$this->locale = explode(',', $locale);
			}
			else
			{
				$this->locale = false;
			}
		}

		return $this->locale;
	}

	/**
	 * Get the first day of the week for this language.
	 *
	 * @return  integer  The first day of the week according to the language
	 *
	 * @since   11.1
	 */
	public function getFirstDay()
	{
		return (int) (isset($this->metadata['firstDay']) ? $this->metadata['firstDay'] : 0);
	}

	/**
	 * Get the weekends days for this language.
	 *
	 * @return  string  The weekend days of the week separated by a comma according to the language
	 *
	 * @since   3.2
	 */
	public function getWeekEnd()
	{
		return (isset($this->metadata['weekEnd']) && $this->metadata['weekEnd']) ? $this->metadata['weekEnd'] : '0,6';
	}

	/**
	 * Searches for language directories within a certain base dir.
	 *
	 * @param   string  $dir  directory of files.
	 *
	 * @return  array  Array holding the found languages as filename => real name pairs.
	 *
	 * @since   11.1
	 * @deprecated   3.7.0, use LanguageHelper::parseLanguageFiles() instead.
	 */
	public static function parseLanguageFiles($dir = null)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::parseLanguageFiles() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::parseLanguageFiles($dir);
	}

	/**
	 * Parse XML file for language information.
	 *
	 * @param   string  $path  Path to the XML files.
	 *
	 * @return  array  Array holding the found metadata as a key => value pair.
	 *
	 * @since   11.1
	 * @throws  \RuntimeException
	 * @deprecated   3.7.0, use LanguageHelper::parseXMLLanguageFile() instead.
	 */
	public static function parseXMLLanguageFile($path)
	{
		\JLog::add(__METHOD__ . '() is deprecated, use LanguageHelper::parseXMLLanguageFile() instead.', \JLog::WARNING, 'deprecated');

		return LanguageHelper::parseXMLLanguageFile($path);
	}

	/**
	 * Gets the extension-specific language folder for the given extension.
	 *
	 * Note that the folder may NOT exist.
	 *
	 * @param   string  $extension  The name of the extension, e.g. com_example, plg_system_example, etc
	 * @param   string  $basePath   The path of the site where the main extension folder is located in. It MUST be
	 *                              set to the value of the constant JPATH_BASE, JPATH_SITE or JPATH_ADMINISTRATOR.
	 *
	 * @return  string|false  The language path or boolean false if no path can be determined
	 */
	private static function getExtensionLanguageFolder($extension, $basePath = JPATH_SITE)
	{
		// Normalize $extension (security measure)
		$filter    = InputFilter::getInstance();
		$extension = $filter->clean($extension, 'cmd');

		// It's possible that someone asks us to load a .sys file instead of a pure extension name. Let's deal with it.
		if ((strlen($extension) > 9) && (substr($extension, -4) == '.sys'))
		{
			$extension = substr($extension, 0, -4);
		}

		// All valid extension names are at least 5 characters long (3 character prefix, underscore and extension name)
		if (strlen($extension) < 5)
		{
			return false;
		}

		// $basePath must be JPATH_SITE or JPATH_ADMINISTRATOR
		if (($basePath != JPATH_SITE) && ($basePath != JPATH_ADMINISTRATOR))
		{
			throw new RuntimeException('$basePath must be either JPATH_SITE or JPATH_ADMINISTRATOR');
		}

		// Get the extension prefix
		list ($prefix, $bareName) = explode('_', $extension, 2);

		switch ($prefix)
		{
			// Component
			case 'com_':
				$extensionPath = $basePath . '/components/' . $extension;

				return $extensionPath;

				break;

			// Module
			case 'mod_':
				$extensionPath = $basePath . '/modules/' . $extension;

				return $extensionPath;

				break;

			// Plugin
			case 'plg_':
				if (strstr($bareName, '_') === false)
				{
					return false;
				}

				list ($folder, $pluginName) = explode('_', $bareName, 2);

				$extensionPath = JPATH_SITE . '/plugins/' . $folder . '/' . $pluginName;

				return $extensionPath;

				break;

			// Template
			case 'tpl_':
				$extensionPath = $basePath . '/templates/' . $bareName;

				return $extensionPath;

				break;

			// Library
			case 'lib_':
				return self::getPathForLibrary($bareName);

				break;

			case 'files_':
			case 'file_':
			case 'pkg_':
				// Files and package extensions do not have an installation path
				return false;

				break;
		}

		// Anything else is an invalid extension name
		return false;
	}

	/**
	 * Finds the language path for a library extension. The installation path of libraries is detected from their
	 * manifests. A cache is kept internally for performance reasons.
	 *
	 * @param   string  $bareName  The library name, minus the lib_ prefix.
	 *
	 * @return  string  The language base path for the library (i.e. the installation path of the library)
	 */
	private static function getPathForLibrary($bareName)
	{
		if (isset(self::$libraryPathCache[$bareName]))
		{
			return self::$libraryPathCache[$bareName];
		}

		$libraryPath  = JPATH_LIBRARIES . '/' . $bareName;
		$languagePath = $libraryPath . '/language';
		$manifestPath = JPATH_MANIFESTS . '/libraries/' . $bareName . '.xml';

		// No manifest file?
		if (!JFile::exists($manifestPath))
		{
			self::$libraryPathCache[$bareName] = $languagePath;

			return $languagePath;
		}

		$xml = simplexml_load_file($manifestPath);

		// Cannot load manifest?
		if ($xml === false)
		{
			self::$libraryPathCache[$bareName] = $languagePath;

			return $languagePath;
		}

		// If the <files> element defines a folder attribute use it to construct the base library path
		if (isset($xml->files) && isset($xml->files['folder']) && ($xml->files['folder'] != ''))
		{
			$libraryPath = JPATH_LIBRARIES . '/' . $xml->files['folder'];
			$languagePath = $libraryPath . '/language';
		}

		// Return the requested path
		self::$libraryPathCache[$bareName] = $languagePath;

		return $languagePath;
	}
}
