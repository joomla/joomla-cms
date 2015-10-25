<?php
/**
 * CBLib, Community Builder Library(TM)
 *
 * @version       $Id: 5/6/14 1:58 PM $
 * @package       ${NAMESPACE}
 * @copyright (C) 2004-2015 www.joomlapolis.com / Lightning MultiCom SA - and its licensors, all rights reserved
 * @license       http://www.gnu.org/licenses/old-licenses/gpl-2.0.html GNU/GPL version 2
 */
namespace CBLib\Language;


/**
 * CBLib\Language\TranslationsLogger Class implementation
 *
 */
interface TranslationsLoggerInterface
{
	/**
	 * Sets the logging mode
	 * Mode: 3: Log only untranslated strings, Mode 4: Log translated strings too.
	 *
	 * @param $debugMode
	 */
	public function setDebugMode( $debugMode );

	/**
	 * Record string used
	 *
	 * @param  string       $languageKeys     Key(s) separated by space or array of keys. Or if second argument is empty, English string
	 *                                        (e.g. 'KEY1-DETAILED KEY2-GENERAL')
	 * @param  string|null  $languageKeyUsed  The key used       (or null if none has been used)
	 * @param  string|null  $englishString    The English string to use if no translations found  (null if no default english string)
	 * @param  string|null  $translated       Translated string  (null if untranslated)
	 * @param  string|null  $automaticKey     Auto-generated key (null if translated)
	 * @return void
	 */
	public function recordUsedString( $languageKeys, $languageKeyUsed, $englishString, $translated, $automaticKey );

	/**
	 * Lists the used stratings into an HTML table for display
	 *
	 * @return string
	 */
	public function listUsedStrings();

	/**
	 * Event function that adds before the </body> tag the table of used strings
	 *
	 * @param  string  $body  Existing HTML page
	 * @return string
	 */
	public function appendToBodyUsedStrings( $body );
}
