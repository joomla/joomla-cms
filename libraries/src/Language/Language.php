<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2005 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Language;

use Joomla\CMS\Factory;
use Joomla\Language\Language as BaseLanguage;
use Joomla\String\StringHelper;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Languages/translation handler class
 *
 * @since  1.7.0
 */
class Language extends BaseLanguage
{
    /**
     * Array of Language objects
     *
     * @var    Language[]
     * @since  1.7.0
     */
    protected static $languages = [];

    /**
     * Translations
     *
     * @var    array
     * @since  1.7.0
     */
    protected $strings = [];

    /**
     * Name of the transliterator function for this language.
     *
     * @var    callable
     * @since  1.7.0
     */
    protected $transliterator = null;

    /**
     * Name of the pluralSuffixesCallback function for this language.
     *
     * @var    callable
     * @since  1.7.0
     */
    protected $pluralSuffixesCallback = null;

    /**
     * Constructor activating the default information of the language.
     *
     * @param   string   $lang   The language
     * @param   boolean  $debug  Indicates if language debugging is enabled.
     *
     * @since   1.7.0
     */
    public function __construct($lang = null, $debug = false)
    {
        $this->strings = [];

        if ($lang == null) {
            $lang = $this->default;
        }

        $this->lang     = $lang;
        $this->metadata = LanguageHelper::getMetadata($this->lang);
        $this->setDebug($debug);

        /*
         * Let's load the default override once, so we can profit from that, too
         * But make sure, that we don't enforce it on each language file load.
         * So don't put it in $this->override
         */
        if (!$this->debug && $lang !== $this->default) {
            $this->loadLanguage(JPATH_BASE . '/language/overrides/' . $this->default . '.override.ini');
        }

        $this->override = $this->parse(JPATH_BASE . '/language/overrides/' . $lang . '.override.ini');

        // Look for a language specific localise class
        $class = str_replace('-', '_', $lang . 'Localise');
        $paths = [];

        if (\defined('JPATH_SITE')) {
            // Note: Manual indexing to enforce load order.
            $paths[0] = JPATH_SITE . "/language/overrides/$lang.localise.php";
            $paths[2] = JPATH_SITE . "/language/$lang/localise.php";
            $paths[4] = JPATH_SITE . "/language/$lang/$lang.localise.php";
        }

        if (\defined('JPATH_ADMINISTRATOR')) {
            // Note: Manual indexing to enforce load order.
            $paths[1] = JPATH_ADMINISTRATOR . "/language/overrides/$lang.localise.php";
            $paths[3] = JPATH_ADMINISTRATOR . "/language/$lang/localise.php";
            $paths[5] = JPATH_ADMINISTRATOR . "/language/$lang/$lang.localise.php";
        }

        ksort($paths);
        $path = reset($paths);

        while (!class_exists($class) && $path) {
            if (is_file($path)) {
                require_once $path;
            }

            $path = next($paths);
        }

        if (class_exists($class)) {
            /**
             * Class exists. Try to find
             * -a transliterate method,
             * -a getPluralSuffixes method,
             * -a getIgnoredSearchWords method
             * -a getLowerLimitSearchWord method
             * -a getUpperLimitSearchWord method
             * -a getSearchDisplayCharactersNumber method
             */
            if (method_exists($class, 'transliterate')) {
                $this->transliterator = [$class, 'transliterate'];
            }

            if (method_exists($class, 'getPluralSuffixes')) {
                $this->pluralSuffixesCallback = [$class, 'getPluralSuffixes'];
            }

            if (method_exists($class, 'getIgnoredSearchWords')) {
                $this->ignoredSearchWordsCallback = [$class, 'getIgnoredSearchWords'];
            }

            if (method_exists($class, 'getLowerLimitSearchWord')) {
                $this->lowerLimitSearchWordCallback = [$class, 'getLowerLimitSearchWord'];
            }

            if (method_exists($class, 'getUpperLimitSearchWord')) {
                $this->upperLimitSearchWordCallback = [$class, 'getUpperLimitSearchWord'];
            }

            if (method_exists($class, 'getSearchDisplayedCharactersNumber')) {
                $this->searchDisplayedCharactersNumberCallback = [$class, 'getSearchDisplayedCharactersNumber'];
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
     * @since       1.7.0
     *
     * @deprecated  4.3 will be removed in 6.0
     *              Use the language factory instead
     *              Example: Factory::getContainer()->get(LanguageFactoryInterface::class)->createLanguage($lang, $debug);
     */
    public static function getInstance($lang, $debug = false)
    {
        if (!isset(self::$languages[$lang . $debug])) {
            self::$languages[$lang . $debug] = Factory::getContainer()->get(LanguageFactoryInterface::class)->createLanguage($lang, $debug);
        }

        return self::$languages[$lang . $debug];
    }

    /**
     * Translate function, mimics the php gettext (alias _) function.
     *
     * The function checks if $jsSafe is true, then if $interpretBackslashes is true.
     *
     * @param   string   $string                The string to translate
     * @param   boolean  $jsSafe                Parameter to add slashes to the string that will be rendered as JavaScript.
     *                                          However, set as "false" if the string is going to be encoded by json_encode().
     * @param   boolean  $interpretBackSlashes  Interpret \t and \n
     *
     * @return  string  The translation of the string
     *
     * @since   1.7.0
     */
    public function _($string, $jsSafe = false, $interpretBackSlashes = true)
    {
        // Detect empty string
        if ($string == '') {
            return '';
        }

        $key = strtoupper($string);

        if (isset($this->strings[$key])) {
            $string = $this->strings[$key];

            // Store debug information
            if ($this->debug) {
                $value  = Factory::getApplication()->get('debug_lang_const', true) ? $string : $key;
                $string = '**' . $value . '**';

                $caller = $this->getCallerInfo();

                if (!\array_key_exists($key, $this->used)) {
                    $this->used[$key] = [];
                }

                $this->used[$key][] = $caller;
            }
        } else {
            if ($this->debug) {
                $info           = [];
                $info['trace']  = $this->getTrace();
                $info['key']    = $key;
                $info['string'] = $string;

                if (!\array_key_exists($key, $this->orphans)) {
                    $this->orphans[$key] = [];
                }

                $this->orphans[$key][] = $info;

                $string = '??' . $string . '??';
            }
        }

        if ($jsSafe) {
            // Javascript filter
            $string = addslashes($string);
        } elseif ($interpretBackSlashes) {
            if (strpos($string, '\\') !== false) {
                // Interpret \n and \t characters
                $string = str_replace(['\\\\', '\t', '\n'], ["\\", "\t", "\n"], $string);
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
     * @since   1.7.0
     */
    public function transliterate($string)
    {
        // First check for transliterator provided by translation
        if ($this->transliterator !== null) {
            $string = \call_user_func($this->transliterator, $string);

            // Check if all symbols were transliterated (contains only ASCII), otherwise continue
            if (!preg_match('/[\\x80-\\xff]/', $string)) {
                return $string;
            }
        }

        // Run our transliterator for common symbols,
        // This need to be executed before native php transliterator, because it may not have all required transliterators
        $string = Transliterate::utf8_latin_to_ascii($string);

        // Check if all symbols were transliterated (contains only ASCII),
        // Otherwise try to use native php function if available
        if (preg_match('/[\\x80-\\xff]/', $string) && \function_exists('transliterator_transliterate') && \function_exists('iconv')) {
            return iconv("UTF-8", "ASCII//TRANSLIT//IGNORE", transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $string));
        }

        return StringHelper::strtolower($string);
    }

    /**
     * Getter for transliteration function
     *
     * @return  callable  The transliterator function
     *
     * @since   1.7.0
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
     * @since   1.7.0
     */
    public function setTransliterator(callable $function)
    {
        $previous             = $this->transliterator;
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
     * @since   1.7.0
     */
    public function getPluralSuffixes($count)
    {
        if ($this->pluralSuffixesCallback !== null) {
            return \call_user_func($this->pluralSuffixesCallback, $count);
        }

        return [(string) $count];
    }

    /**
     * Getter for pluralSuffixesCallback function.
     *
     * @return  callable  Function name or the actual function.
     *
     * @since   1.7.0
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
     * @since   1.7.0
     */
    public function setPluralSuffixesCallback(callable $function)
    {
        $previous                     = $this->pluralSuffixesCallback;
        $this->pluralSuffixesCallback = $function;

        return $previous;
    }

    /**
     * Loads a single language file and appends the results to the existing strings
     *
     * @param   string   $extension  The extension for which a language file should be loaded.
     * @param   string   $basePath   The basepath to use.
     * @param   string   $lang       The language to load, default null for the current language.
     * @param   boolean  $reload     Flag that will force a language to be reloaded if set to true.
     * @param   boolean  $default    Flag that force the default language to be loaded if the current does not exist.
     *
     * @return  boolean  True if the file has successfully loaded.
     *
     * @since   1.7.0
     */
    public function load($extension = 'joomla', $basePath = JPATH_BASE, $lang = null, $reload = false, $default = true)
    {
        // If language is null set as the current language.
        if (!$lang) {
            $lang = $this->lang;
        }

        // Load the default language first if we're not debugging and a non-default language is requested to be loaded
        // with $default set to true
        if (!$this->debug && ($lang != $this->default) && $default) {
            $this->load($extension, $basePath, $this->default, false, true);
        }

        $path = LanguageHelper::getLanguagePath($basePath, $lang);

        $internal = $extension === 'joomla' || $extension == '';

        $filenames = [];

        if ($internal) {
            $filenames[] = "$path/joomla.ini";
            $filenames[] = "$path/$lang.ini";
        } else {
            // Try first without a language-prefixed filename.
            $filenames[] = "$path/$extension.ini";
            $filenames[] = "$path/$lang.$extension.ini";
        }

        foreach ($filenames as $filename) {
            if (isset($this->paths[$extension][$filename]) && !$reload) {
                // This file has already been tested for loading.
                $result = $this->paths[$extension][$filename];
            } else {
                // Load the language file
                $result = $this->loadLanguage($filename, $extension);
            }

            if ($result) {
                return true;
            }
        }

        return false;
    }

    /**
     * Loads a language file.
     *
     * This method will not note the successful loading of a file - use load() instead.
     *
     * @param   string  $fileName   The name of the file.
     * @param   string  $extension  The name of the extension.
     *
     * @return  boolean  True if new strings have been added to the language
     *
     * @see     Language::load()
     * @since   1.7.0
     */
    protected function loadLanguage($fileName, $extension = 'unknown')
    {
        $this->counter++;

        $result  = false;
        $strings = $this->parse($fileName);

        if ($strings !== []) {
            $this->strings = array_replace($this->strings, $strings, $this->override);
            $result        = true;
        }

        // Record the result of loading the extension's file.
        if (!isset($this->paths[$extension])) {
            $this->paths[$extension] = [];
        }

        $this->paths[$extension][$fileName] = $result;

        return $result;
    }

    /**
     * Parses a language file.
     *
     * @param   string  $fileName  The name of the file.
     *
     * @return  array  The array of parsed strings.
     *
     * @since   1.7.0
     */
    protected function parse($fileName)
    {
        try {
            $strings = LanguageHelper::parseIniFile($fileName, $this->debug);
        } catch (\RuntimeException $e) {
            $strings = [];

            // Debug the ini file if needed.
            if ($this->debug && is_file($fileName)) {
                if (!$this->debugFile($fileName)) {
                    // We didn't find any errors but there's a parser warning.
                    $this->errorfiles[$fileName] = 'PHP parser errors :' . $e->getMessage();
                }
            }
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
    public function debugFile(string $filename): int
    {
        // Make sure our file actually exists
        if (!is_file($filename)) {
            throw new \InvalidArgumentException(
                \sprintf('Unable to locate file "%s" for debugging', $filename)
            );
        }

        // Initialise variables for manually parsing the file for common errors.
        $reservedWord = ['YES', 'NO', 'NULL', 'FALSE', 'ON', 'OFF', 'NONE', 'TRUE'];
        $errors       = [];

        // Open the file as a stream.
        $file = new \SplFileObject($filename);

        foreach ($file as $lineNumber => $line) {
            // Avoid BOM error as BOM is OK when using parse_ini.
            if ($lineNumber == 0) {
                $line = str_replace("\xEF\xBB\xBF", '', $line);
            }

            $line = trim($line);

            // Ignore comment lines.
            if (!\strlen($line) || $line['0'] == ';') {
                continue;
            }

            // Ignore grouping tag lines, like: [group]
            if (preg_match('#^\[[^\]]*\](\s*;.*)?$#', $line)) {
                continue;
            }

            // Remove any escaped double quotes \" from the equation
            $line = str_replace('\"', '', $line);

            $realNumber = $lineNumber + 1;

            // Check for odd number of double quotes.
            if (substr_count($line, '"') % 2 != 0) {
                $errors[] = $realNumber;
                continue;
            }

            // Check that the line passes the necessary format.
            if (!preg_match('#^[A-Z][A-Z0-9_:\*\-\.]*\s*=\s*".*"(\s*;.*)?$#', $line)) {
                $errors[] = $realNumber;
                continue;
            }

            // Check that the key is not in the reserved constants list.
            $key = strtoupper(trim(substr($line, 0, strpos($line, '='))));

            if (\in_array($key, $reservedWord)) {
                $errors[] = $realNumber;
            }
        }

        // Check if we encountered any errors.
        if (\count($errors)) {
            $this->errorfiles[$filename] = $errors;
        }

        return \count($errors);
    }

    /**
     * Get a back trace.
     *
     * @return array
     *
     * @since 4.0.0
     */
    protected function getTrace()
    {
        return \function_exists('debug_backtrace') ? debug_backtrace() : [];
    }

    /**
     * Get a list of language files that have been loaded.
     *
     * @param   string  $extension  An optional extension name.
     *
     * @return  array
     *
     * @since   1.7.0
     */
    public function getPaths($extension = null)
    {
        if (isset($extension)) {
            if (isset($this->paths[$extension])) {
                return $this->paths[$extension];
            }

            return [];
        }

        return $this->paths;
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
        return $this->metadata['calendar'] ?? 'gregorian';
    }

    /**
     * Determines is a key exists.
     *
     * @param   string  $string  The key to check.
     *
     * @return  boolean  True, if the key exists.
     *
     * @since   1.7.0
     */
    public function hasKey($string)
    {
        if ($string === null) {
            return false;
        }

        return isset($this->strings[strtoupper($string)]);
    }
}
