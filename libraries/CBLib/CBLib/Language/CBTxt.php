<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 09.06.13 01:29 $
* @package ${NAMESPACE}
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Language;

defined('CBLIB') or die();

/**
 * CBLib\Language\CBTxt (L like Language) Class implementation
 * 
 */
class CBTxt
{
	/**
	 * Singleton storage for central translation tables
	 *
	 * @var self
	 */
	protected static $self;

	/**
	 * Array of files imported: array[language][path][file] = (boolean) loaded
	 *
	 * @var array
	 */
	protected $importedLangPathFiles	=	array();

	/**
	 * Array of callbacks for delayed imports
	 *
	 * @var callable[]
	 */
	protected $delayedImports			=	array();

	/**
	 * The translation tables array[language][key]
	 *
	 * @var array[]
	 */
	protected $strings					=	array();

	/**
	 * The current translation language in ISO format 'en-GB'
	 *
	 * @var string
	 */
	protected $currentLanguage			=	'en-GB';

	/**
	 * The main reference translation language in ISO format 'en-GB'
	 *
	 * @var string
	 */
	protected $mainLanguage				=	null;

	/**
	 * Translation Logging/Debugging mode:
	 * 0: normal, 1: text markers debug, 2: HTML markers debug, 3: Markers and untranslated, 4: Markers and all strings
	 *
	 * @var int
	 */
	protected $mode						=	0;

	/**
	 * @var TranslationsLogger
	 */
	protected $translationsLogger;

	/**
	 * Last key used for translation
	 * @var string
	 */
	protected $lastKeyUsed;

	/**
	 * Last key used for translation
	 * @var string
	 */
	protected $lastAutoKey;

	/**
	 * Constructor
	 *
	 * @param   int                          $debugMode           [optional] debug mode: 0 none, 1 highlight, 2 highlight html, 3 list untranslated, 4 list all strings
	 * @param   TranslationsLoggerInterface  $translationsLogger  [optional] translations logger
	 */
	public function __construct( $debugMode = 0, TranslationsLoggerInterface $translationsLogger = null )
	{
		static::$self					=	$this;

		$this->mode						=	$debugMode;

		if ( $this->mode > 0 && $translationsLogger ) {
			$this->translationsLogger	=	$translationsLogger;

			$translationsLogger->setDebugMode( $this->mode );
		}
	}

	/**
	 * T like Translate text from english to target language
	 * (can add translation markers in TEXT for display in language debug mode)
	 *
	 * Multiple keys can be given if string starts with *, and will be tried from left to right until English string at right, E.g. as follows:
	 * T( '*REGISTRATION_SIGN-UP_TITLE*REGISTRATION_SIGN-UP*Sign-up' );
	 * This allows to translate or override specific texts, or generally.
	 *
	 * Variables substitution is performed for the $args parameter.
	 *
	 * The message supports two different types of pluralization rules:
	 *
	 * interval: {0} There are no apples|{1} There is one apple|]1,Inf] There are %count% apples
	 * indexed:  There is one apple|There are %count% apples
	 *
	 * The indexed solution can also contain labels (e.g. one: There is one apple).
	 * This is purely for making the translations more clear - it does not
	 * affect the functionality.
	 *
	 * The two methods can also be mixed:
	 *     {0} There are no apples|one: There is one apple|more: There are %count% apples
	 *
	 * @param  string  $languageKeys   Key(s) separated by space. Or if second argument is empty, English string
	 *                                 (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string  $englishString  The English string to use if no translations found
	 * @param  array   $args           A strtr-formatted array of string substitutions
	 * @return string                  Translated string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function T( $languageKeys, $englishString = null, array $args = array() )
	{
		if ( $languageKeys == '' ) {
			return $languageKeys;
		}

		$translated			=	static::$self->translateToCurrentLanguage( $languageKeys, $englishString, $args );

		if ( static::$self->mode == 0 ) {
			return $translated;
		}

		if ( ! static::$self->hasText( $languageKeys ) ) {
			return $languageKeys;
		}

		$isTranslated		=	! is_null( static::$self->lastKeyUsed );

		static::$self->translationsLogger->recordUsedString( $languageKeys, static::$self->lastKeyUsed, static::$self->lastAutoKey, $englishString, $isTranslated ? $translated : null );

		if ( ! $isTranslated ) {
			return '===\\' . $translated . '/---';
		}

		return '*' . $translated . '*';
	}

	/**
	 * Th like Translate HTML from english to target language
	 * (can add translation markers in HTML for better display in language debug mode)
	 *
	 * @see \CBLib\Language\CBTxt::T() for pluralizations
	 *
	 * @param  string  $languageKeys   Key(s) separated by space. Or if second argument is empty, English string
	 *                                 (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string  $englishString  The English string to use if no translations found
	 * @param  array   $args           A strtr-formatted array of string substitutions
	 * @return string                  Translated string
	 *
	 * @throws \InvalidArgumentException
	 */
	public static function Th( $languageKeys, $englishString = null, array $args = array() )
	{
		if ( $languageKeys == '' ) {
			return $languageKeys;
		}

		$translated		=	static::$self->translateToCurrentLanguage( $languageKeys, $englishString, $args );

		if ( static::$self->mode == 0 )
		{
			return $translated;
		}

		return self::$self->htmlHighlighter( $languageKeys, $englishString, $args, $translated );
	}

	/**
	 * Internal function to add translation markers in HTML for better display in language debug mode
	 *
	 * @param  string  $languageKeys   Key(s) separated by space. Or if second argument is empty, English string
	 *                                 (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string  $englishString  The English string to use if no translations found
	 * @param  array   $args           A strtr-formatted array of string substitutions
	 * @param  string  $translated     The translated string, if translated
	 * @return string                  Translated string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function htmlHighlighter( $languageKeys, $englishString, array $args, $translated )
	{
		if ( ! $this->hasText( $languageKeys ) ) {
			return $languageKeys;
		}

		$isTranslated		=	! is_null( $this->lastKeyUsed );

		if ( $this->mode == 1 )
		{
			if ( $isTranslated )
			{
				return '*' . $translated . '*';
			}

			return '===&gt;' . str_replace( '%s', '[%s]', $languageKeys ) . '&lt;===';
		}

		$this->translationsLogger->recordUsedString(
			$languageKeys,
			$this->lastKeyUsed,
			$this->lastAutoKey,
			$englishString,
			$isTranslated ?
				$this->translateToCurrentLanguage( $languageKeys, $englishString, $args )
				: null
		);

		if ( $isTranslated )
		{
			// Replace %'s in title with %-htmlentity and [] with their htmlentities too, to avoid replacements and sprintf-multi-replacement errors:
			return '<span style="color:#CCC;font-style:italic" title="' . str_replace( array( '%', '[', ']' ), array( '&#37;', '&#91;', '&#93;' ), htmlspecialchars( $this->lastKeyUsed, ENT_COMPAT, 'UTF-8' ) ) . '">'
				. $translated
				. '</span>';
		}

		return '<span style="color:#FF0000;font-weight:bold">' . '===>' . $languageKeys . '<===' . '</span>';
	}

	/**
	 * Internal translation and pluralization function without string debug features
	 *
	 * @param  string  $languageKeys   Key(s) separated by space. Or if second argument is empty, English string
	 *                                 (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string  $englishString  The English string to use if no translations found
	 * @param  array   $args           A strtr-formatted array of string substitutions
	 * @return string                  Translated string
	 * @internal string  $this ->lastKeyUsed updated with $key
	 *
	 * @throws \InvalidArgumentException
	 */
	private function translateToCurrentLanguage( $languageKeys, $englishString, $args )
	{
		if ( is_array( $englishString ) ) {
			throw new \InvalidArgumentException( 'Incorrect second argument for CBTxt::T array instead of string.', 500 );
		}

		return $this->pluralizeStringWithArgs( $this->findKeyValue( $languageKeys, $englishString ), $args );
	}

	/**
	 * Internal translation and pluralization function without string debug features
	 *
	 * @param  string  $languageKeys   Key(s) separated by space. Or if second argument is empty, English string
	 *                                 (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string  $englishString  The English string to use if no translations found
	 * @return string                  Translated string
	 * @internal string  $this ->lastKeyUsed updated with $key
	 *
	 * @throws \InvalidArgumentException
	 */
	private function findKeyValue( $languageKeys, $englishString )
	{
		$this->lastKeyUsed		=	null;
		$this->lastAutoKey		=	null;

		// Has default $englishString, so $languageKeys is (a) key(s) (most PHP cases):
		if ( is_string( $englishString ) )
		{
			// updates $this->lastKeyUsed if result not false:
			$translated		=	$this->get( $languageKeys );

			if ( $translated !== false )
			{
				return $translated;
			}

			return $englishString;
		}

		// No key, first try to use the $langKeys as key(s):
		if ( $this->hasKey( $languageKeys ) )
		{
			// updates $this->lastKeyUsed:
			return $this->getKey( $languageKeys );
		}

		if ( ! $this->hasText( $languageKeys ) ) {
			return $languageKeys;
		}

		// Try to auto-generate a key from the language string corresponding to the strings-grabber and tries getting that translation (most XML cases):
		$autoKey			=	$this->generateKeyFromString( $languageKeys );

		$this->lastAutoKey	=	$autoKey;

		if ( $autoKey && $this->hasKey( $autoKey ) )
		{
			// updates $this->lastKeyUsed:
			return $this->getKey( $autoKey );
		}

		// Old Define strings:		//TODO: This should be removed in CB 3.0:
		if ( defined( $languageKeys ) ) {
			$this->lastKeyUsed	=	$languageKeys . ' (through deprecated DEFINE)';

			return constant( $languageKeys );
		}

		// Desperately return untranslated string:
		return $languageKeys;
	}

	/**
	 * Checks if it has text, so either a key or translatable
	 *
	 * @param  string    $string
	 * @return boolean
	 */
	private function hasText( $string )
	{
		return preg_match( '/[a-z]/i', $string );
	}

	/**
	 * Generates automatically a unique key from a string
	 *
	 * @param  string       $languageKeys  String to convert to key
	 * @return string|null                 Key corresponding to string and different from string, otherwise null
	 */
	private function generateKeyFromString( $languageKeys )
	{
		$upperWithNoTags	=	strtoupper( strip_tags( $languageKeys ) );

		$key	=	preg_replace( array( '/[^A-Z0-9 ]+/', '/^\s+|\s+$/', '/ +/', '/^(\d)/' ), array( '', '', '_', 'N\1' ), trim( $upperWithNoTags ) );

		if ( $key === '' || $key == $languageKeys ) {
			return null;
		}

		if ( strlen( $key ) > 50 ) {
			$key			=	substr( $key, 0, 50 );
		}

		$md5First6Chars		=	substr( md5( $languageKeys ), 0, 6 );

		return $key . '_' . $md5First6Chars;
	}

	/**
	 * Applies arguments to string, including pluralization
	 *
	 * @param  string  $translated
	 * @param  array   $args
	 * @return string
	 */
	private function pluralizeStringWithArgs( $translated, $args )
	{
		if ( empty( $args ) )
		{
			return $translated;
		}

		if ( strpos( $translated, '|' ) !== false )
		{
			$translated	=	Pluralization::pluralize( $translated, $args, $this->currentLanguage );
		}

		return strtr( $translated, $args );
	}

	/**
	 * Sets the current language and returns the previous one.
	 *
	 * @param  string|null  $language  New ISO language (ISO 639-1 language code, a dash (-), then the ISO 3166-1 alpha-2 country code: e.g. 'en-GB'), NULL or '' doesn't change it.
	 * @return string                  Previous ISO language
	 */
	public static function setLanguage( $language )
	{
		$current							=	static::$self->currentLanguage;

		if ( $language ) {
			if ( ! isset( static::$self->importedLangPathFiles[$language] ) ) {
				static::importSameInLanguage( $language );
			}

			static::$self->currentLanguage		=	$language;
		}

		return $current;
	}

	/**
	 * Adds a callback to lazy-load later language files
	 * (this is useful for basic language files if CBLib is loaded too early)
	 * Do not use for language overrides.
	 * @since 2.0.10
	 *
	 * @param  callable  $callback
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function addLanguageFile( $callback )
	{
		if ( ! is_callable( $callback ) ) {
			throw new \InvalidArgumentException( '$callback must be a valid callback' );
		}

		static::$self->delayedImports[]	=	$callback;
	}

	/**
	 * Loads all language files that were added for lazy-loading with addLanguageFile()
	 *
	 * @return boolean
	 */
	private function loadLanguageFiles()
	{
		if ( count( static::$self->delayedImports ) == 0 ) {
			return false;
		}

		foreach ( static::$self->delayedImports as $callback ) {
			$callback();
		}

		static::$self->delayedImports	=	array();

		return true;
	}

	/**
	 * Includes CB text file
	 *
	 * @param  string  $langPath  Path of the language folder (without trailing '/')
	 * @param  string  $language  ISO language (ISO 639-1 language code, a dash (-), then the ISO 3166-1 alpha-2 country code: e.g. 'en-GB') (which is also the name of the language folder)
	 * @param  string  $filename  Filename of the php language file (ending with '.php')
	 * @param  bool    $fallback  True (default): Falls back to default_language if $filename does not exist
	 * @return bool               True if language loaded successfully
	 */
	public static function import( $langPath, $language, $filename, $fallback = true )
	{
		if ( isset( static::$self->importedLangPathFiles[$language][$langPath][$filename] ) ) {
			return true;
		}

		static::$self->loadLanguageFiles();

		$file				=	$langPath . '/' . strtolower( $language ) . '/' . $filename;

		if ( ! file_exists( $file ) ) {
			// If fallback is allowed, last resort is default_language without fallback:
			return $fallback && static::import( $langPath, 'default_language', $filename, false );
		}

		$extension			=	substr( $file, -4, 4 );

		if ( $extension == '.php' ) {
			/** @noinspection PhpIncludeInspection */
			$strings		=	include_once $file;
		} elseif ( $extension == '.ini' ) {
			$strings		=	parse_ini_file( $file, false );
		} else {
			return false;
		}

		if ( ! is_array( $strings ) ) {
			return false;
		}

		/** @noinspection PhpDeprecationInspection */
		static::addStrings( $strings, $language );

		// Now if we already import other non-default languages, we also want to import this file in those other languages:
		if ( ( $language != 'default_language' ) && count( static::$self->importedLangPathFiles ) > 1 ) {
			foreach ( array_keys( static::$self->importedLangPathFiles ) as $la ) {
				if ( ( $la != 'default_language' ) && ( $la !== $language ) ) {
					static::importSameInLanguage( $la );
				}
			}
		}

		static::$self->importedLangPathFiles[$language][$langPath][$filename]	=	true;

		static::setCurrentLanguage( $language );

		return true;
	}

	/**
	 * Include same language files for $language as have already been included for the main language
	 *
	 * @param  string  $language  ISO language (e.g. 'en-GB')
	 * @return void
	 */
	private static function importSameInLanguage( $language )
	{
		$mainLanguage		=	static::$self->mainLanguage;
		if ( ! isset( static::$self->importedLangPathFiles[$mainLanguage] ) ) {
			$mainLanguage	=	'default_language';
		}

		foreach ( static::$self->importedLangPathFiles[$mainLanguage] as $langPath => $langPathFilename ) {
			foreach ( $langPathFilename as  $filename => $loaded ) {
				if ( $loaded ) {
					static::import( $langPath, $language, $filename );
				}
			}
		}

	}

	/**
	 * Gets an existing translation from translation table
	 *
	 * @param  string  $languageKeys  Keys, separated by space
	 *                                (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @return string|boolean         Translation or FALSE if no translation in table for any of the key(s) nor English
	 */
	private function get( $languageKeys )
	{
		$keys	=	explode( ' ', $languageKeys );

		foreach ( $keys as $k ) {
			if ( $this->hasKey( $k ) ) {
				return $this->getKey( $k );
			}
		}

		// If the string was not found, try again if there was some pending lazy-loading to be done:
		if ( $this->loadLanguageFiles() ) {
			return $this->get( $languageKeys );
		}

		return false;
	}

	/**
	 * Check if a string in english exists in translation table.
	 *
	 * @param  string   $key  Key or String in english
	 * @return boolean        True: string exists, False otherwise
	 */
	private function hasKey( $key )
	{
		return isset( $this->strings[$this->currentLanguage][$key] );
	}

	/**
	 * Gets an existing translation from translation table
	 *
	 * @param  string $key Key or String in english
	 * @return string         Translation
	 * @internal string  $this ->lastKeyUsed updated with $key
	 */
	private function getKey( $key )
	{
		$this->lastKeyUsed		=	$key;
		return $this->strings[static::$self->currentLanguage][$key];
	}

	/**
	 * Adds strings to the translations. Used by language plugins
	 *
	 * @deprecated 2.0 : return the array in language file instead of calling this method
	 *
	 * @param  array        $array     Keyed array of translation strings
	 * @param  string|null  $language  Language of the array [param added in 2.0]
	 * @return void
	 */
	public static function addStrings( $array, $language = null )
	{
		static::setCurrentLanguage( $language );

		if ( ! empty( $array ) ) {
			if ( isset( static::$self->strings[static::$self->currentLanguage] ) ) {
				static::$self->strings[static::$self->currentLanguage]	=	array_merge( static::$self->strings[static::$self->currentLanguage], $array );
			} else {
				static::$self->strings[static::$self->currentLanguage]	=	$array;
			}
		}
	}

	/**
	 * Sets the currently used language
	 *
	 * @param  string  $language  ISO language
	 * @return void
	 */
	private static function setCurrentLanguage( $language )
	{
		if ( $language ) {
			if ( $language == 'default_language' ) {
				$language	=	'en-GB';
			}

			static::$self->currentLanguage		=	$language;

			if ( ! static::$self->mainLanguage ) {
				static::$self->mainLanguage		=	$language;
			}
		}
	}
}
