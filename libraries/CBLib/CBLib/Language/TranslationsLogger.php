<?php
/**
* CBLib, Community Builder Library(TM)
* @version $Id: 4/12/14 11:46 PM $
* @package CBLib\Language
* @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
* @license http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
*/

namespace CBLib\Language;

defined('CBLIB') or die();

/**
 * CBLib\Language\TranslationsLogger Class implementation
 * 
 */
class TranslationsLogger implements TranslationsLoggerInterface
{
	/**
	 * Strings used in the execution (english => translated)
	 * @var array
	 */

	protected $translatedStrings			=	array();

	/**
	 * English strings corresponding to the used strings in the execution (english => translated)
	 * @var array
	 */
	protected $languageKeysUsed		=	array();

	/**
	 * English strings corresponding to the used strings in the execution (english => translated)
	 * @var array
	 */
	protected $englishStrings		=	array();

	/**
	 * English strings corresponding to the used strings in the execution (english => translated)
	 * @var array
	 */
	protected $automaticKeys		=	array();

	/**
	 * Debug Mode: 3: Log only untranslated strings, Mode 4: Log translated strings too.
	 * @var
	 */
	protected $mode;

	/**
	 * Sets the logging mode
	 * Mode: 3: Log only untranslated strings, Mode 4: Log translated strings too.
	 *
	 * @param $debugMode
	 */
	public function setDebugMode( $debugMode )
	{
		$this->mode		=	$debugMode;
	}

	/**
	 * Record string used
	 *
	 * @param  string       $languageKeys     Key(s) separated by space or array of keys. Or if second argument is empty, English string
	 *                                        (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string|null  $languageKeyUsed  The key used       (or null if none has been used)
	 * @param  string|null  $automaticKey     Auto-generated key (null if translated)
	 * @param  string|null  $englishString    The English string to use if no translations found  (null if no default english string)
	 * @param  string|null  $translated       Translated string  (null if untranslated)
	 * @return void
	 */
	public function recordUsedString( $languageKeys, $languageKeyUsed, $automaticKey, $englishString, $translated )
	{
		if ( ! ( $this->mode == 4 || ( $this->mode == 3 && $translated === null ) ) ) {
			return;
		}

		$this->languageKeysUsed[$languageKeys]		=	$languageKeyUsed;
		$this->automaticKeys[$languageKeys]			=	$automaticKey;
		$this->englishStrings[$languageKeys]		=	$englishString;
		$this->translatedStrings[$languageKeys]			=	$translated;
	}

	/**
	 * Lists the used translations into an HTML table for display
	 *
	 * @return string
	 */
	public function listUsedStrings()
	{
		$r		=	null;
		if ( $this->translatedStrings ) {
			$r	= '<table class="adminlist" id="cbtranslatedstrings"><tr class="sectiontableheader"><th>'
				.	( $this->mode == 3 ? CBTxt::Th('Untranslated strings on this page')
					: CBTxt::Th('Translations on this page') )
				.	': '
				.	CBTxt::Th('Keys')
				.	'</th><th>'
				.	CBTxt::Th('Default string')
				.	'</th><th>'
				.	CBTxt::Th('Translated string')
				.	'</th></tr>'
			;
			$s				=	0;

			foreach ( $this->translatedStrings as $k => $v ) {
				$columns	=	$this->generateHtmlColumns( $k, $v );

				$r			.= '<tr class="sectiontableentry' . ( ( $s & 1 ) + 1 ) . ' row' . ( $s++ & 1 ) . '">';

				foreach ( $columns as $htmlColumn ) {
					$r		.=	'<td>' . $htmlColumn . '</td>';
				}

				$r			.=	'</tr>';
			}
			$r				.=	'</table>';
		}
		return $r;
	}

	/**
	 * Generates an array for the logger display columns
	 *
	 * @param  string       $k  Key of string
	 * @param  string|null  $v  Value of $this->translatedStrings[$k]
	 * @return string[]         HTML strings: Keys, Default string, Translated string
	 */
	protected function generateHtmlColumns( $k, $v )
	{
		$translatedFromKey	=	( $this->englishStrings[$k] === null ) && ( $this->automaticKeys[$k] == null );
		$availableKeys	=	$translatedFromKey ? $k : $this->automaticKeys[$k];
		$keys			=	$this->renderKeyColumnHtml( $availableKeys, $this->languageKeysUsed[$k] );

		$defaultString	=	$translatedFromKey ? htmlspecialchars( $this->englishStrings[$k] ) : htmlspecialchars( $k );
		$translated		=	$v === null ? '<strong style="color:#882222;">' .htmlspecialchars( $k ) . '</strong>' : htmlspecialchars( $v );

		return array( $keys, $defaultString, $translated );
	}

	/**
	 * Renders in HTML the Keys column for the logger display
	 *
	 * @param  string       $availableKeys    space-delimitted keys that could have been used for that string
	 * @param  string|null  $languageKeyUsed  Key used, if any
	 * @return string                         HTEML string of key
	 */
	protected function renderKeyColumnHtml( $availableKeys, $languageKeyUsed )
	{
		if ( $languageKeyUsed === null ) {
			return '<strong style="color:#882222;">' .htmlspecialchars( $availableKeys ) . '</strong>';
		}

		$allKeys		=	explode( ' ', $availableKeys );

		$allKeysKey	=	$languageKeyUsed ? array_search( $languageKeyUsed, $allKeys, true ) : false;

		array_map( 'htmlspecialchars', $allKeys );

		if ( $allKeysKey !== false ) {
			$allKeys[$allKeysKey]	=	'<span style="color:#228822; font-style:italic;">' . $allKeys[$allKeysKey] . '</span>';
		}

		return implode( ' ', $allKeys );
	}

	/**
	 * Event function that adds before the </body> tag the table of used strings
	 *
	 * @param  string  $body  Existing HTML page
	 * @return string
	 */
	public function appendToBodyUsedStrings( $body )
	{
		return str_replace( '</body>', $this->listUsedStrings() . '</body>', $body );
	}
}
