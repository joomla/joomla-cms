<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

use Joomla\Registry\Registry;
use Joomla\String\StringHelper;

/**
 * Form Rule class for the Joomla Platform.
 *
 * @since  11.1
 */
class JFormRuleUsername extends JFormRule
{
	/**
	 * Method to test the username for uniqueness, minimum size, maximum size, and character set compliant.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 * @param   Registry          $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm             $form     The form object for which the field is being tested.
	 *
	 * @return  boolean  True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, Registry $input = null, JForm $form = null)
	{
		// Default value
		$result = true;
		$app    = JFactory::getApplication();

		// Load language files
		JFactory::getLanguage()->load('com_users');

		// Get the database object and a new query object.
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		// Build the query.
		$query->select('COUNT(*)')
			->from('#__users')
			->where('username = ' . $db->quote($value));

		// Get the extra field check attribute.
		$userId = ($form instanceof JForm) ? $form->getValue('id') : '';
		$query->where($db->quoteName('id') . ' <> ' . (int) $userId);

		// Set and query the database.
		$db->setQuery($query);
		$duplicate = (bool) $db->loadResult();

		if ($duplicate)
		{
			return false;
		}

		// Get the config params for username
		$params = JComponentHelper::getParams('com_users');

		// CHECK MINIMUM CHARACTER'S NUMBER
		// Get the number of characters in $username
		$usernameLength = StringHelper::strlen($value);

		// Get the minimum number of characters
		$minNumChars = $params->get('minimum_length_username');

		// If is set minNumChars and $usernameLength does't achieve minimum lenght
		if (($minNumChars) && ($usernameLength < $minNumChars))
		{
			$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_MINNUMCHARS_REQUIRED', $minNumChars, $usernameLength), 'warning');
			$result = false;
		}

		// CHECK MAXIMUM CHARACTER'S NUMBER
		// Get the maximum number of characters
		$maxNumChars = $params->get('maximum_length_username');

		// If is set maxNumChars and $usernameLength surpass maximum lenght
		if (($maxNumChars) && ($usernameLength > $maxNumChars))
		{
			$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_MAXNUMCHARS_REQUIRED', $maxNumChars, $usernameLength), 'warning');
			$result = false;
		}

		// CHECK IF USERNAME SPELLING IN ALLOWED CHARACTER SET
		// Get preset option
		$allowed_preset = $params->get('allowed_chars_username_preset');

		if ($allowed_preset)
		{
			switch ($allowed_preset)
			{
				case 1: // CUSTOM ALLOWED
				case 2: // CUSTOM FORBIDDEN
					$customCharsUsername = array_unique(StringHelper::str_split($params->get('custom_chars_username')));

					// Get the username
					$uname = array_unique(StringHelper::str_split($value));

					if ($allowed_preset == 1)
					{
						// Get the invalid chars for CUSTOM ALLOWED
						$invalid_chars = array_diff($uname, $customCharsUsername);
					}
					else
					{
						// Get the invalid chars for CUSTOM FORBIDDEN
						$invalid_chars = array_intersect($uname, $customCharsUsername);
					}

					// Check if all the $uname chars are valid chars
					if (!empty($invalid_chars))
					{
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_CHARSET_REQUIRED', implode('', $invalid_chars)), 'warning');
						$result = false;
					}
					break;

				case 3:
					// CUSTOM IS REGEXP
					// All that match is rejected
					$regExp = (string) $params->get('custom_chars_username');

					if (preg_match_all($regExp, $value, $regExpChars))
					{
						$nonRegExpString = preg_replace($regExp, '', $value);

						// Enqueue error message and return false
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_CHARSET_REQUIRED', $nonRegExpString), 'warning');

						$result = false;
					}
					break;
				case 4:
					/* ALPHANUMERIC ONLY
					* @TODO: with regExp
					* $alphaReg = '/\w+/';
                    */
					if (!ctype_alnum($value))
					{
						// Enqueue error message and return false
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_ALPHANUMERIC_REQUIRED'), 'warning');

						$result = false;
					}
					break;

				case 5:
					// LATIN ONLY
					if (preg_match_all('/[^\\p{Common}\\p{Latin}]/u', $value, $nonLatinChars))
					{
						$nonLatinString = implode(' ', array_unique($nonLatinChars[0]));

						// Enqueue error message and return false
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_LATIN_REQUIRED', $nonLatinString), 'warning');

						$result = false;
					}
					break;

				case 6:
					// UNICODE Security MECHANISMS IMPLEMENTATION of Highly Restrictive level
					// http://www.unicode.org/reports/tr39/#General_Security_Profile

					// Whenever **scripts** are tested for in the following definitions,
					// characters with Script_Extension=Common and Script_Extension=Inherited are ignored.
					$commonORinherited = '/[\p{Common}]+|[\p{Inherited}]+/u';
					$valueWithoutCommonInherited = preg_replace($commonORinherited, '', $value);

					// UAX31 Only some scripts are recommended for Highly Restrictive level
					// http://www.unicode.org/reports/tr31/#Table_Recommended_Scripts
					$regexRecommendedScripts = '/^(?:'
						. '[\p{Latin}\p{Han}\p{Hiragana}\p{Katakana}]+'
						. '|[\p{Latin}\p{Han}\p{Bopomofo}]+'
						. '|[\p{Latin}\p{Han}\p{Hangul}]+'
						. '|[\p{Arabic}]+|[\p{Armenian}]+|[\p{Bengali}]+|[\p{Cyrillic}]+|[\p{Devanagari}]+|[\p{Ethiopic}]+|[\p{Georgian}]+|[\p{Greek}]+'
						. '|[\p{Gujarati}]+|[\p{Gurmukhi}]+|[\p{Hebrew}]+|[\p{Kannada}]+|[\p{Khmer}]+|[\p{Lao}]+|[\p{Malayalam}]+|[\p{Myanmar}]+'
						. '|[\p{Oriya}]+|[\p{Sinhala}]+|[\p{Tamil}]+|[\p{Telugu}]+|[\p{Thaana}]+|[\p{Thai}]+|[\p{Tibetan}]+'
						. ')$/u';

					// @TODO extract common and inheritaded
					// If the username comes from more than a recommended script (or permitted combination) throws Error
					if (!preg_match_all($regexRecommendedScripts, $valueWithoutCommonInherited))
					{
						// Enqueue error message and return false
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_ONESCRIPT_REQUIRED'), 'warning');

						$result = false;
					}

					/* No characters in the identifier (username) can be outside of the Identifier Profile
					* Identifier profile defined here: http://www.unicode.org/Public/security/latest/xidmodifications.txt
					* Security Profile for General Identifiers :: Version: 8.0.0
					*/
					$regexAllowedChars = "/[^\x{0030}-\x{0039}\x{0041}-\x{005A}\x{005F}\x{0061}-\x{007A}\x{00C0}-\x{00D6}\x{00D8}-\x{00F6}\x{00F8}-\x{0131}\\
\x{0134}-\x{013E}\x{0141}-\x{0148}\x{014A}-\x{017E}\x{018F}\x{01A0}-\x{01A1}\x{01AF}-\x{01B0}\x{01CD}-\x{01DC}\x{01DE}-\x{01E3}\\
\x{01E6}-\x{01F0}\x{01F4}-\x{01F5}\x{01F8}-\x{021B}\x{021E}-\x{021F}\x{0226}-\x{0233}\x{0259}\x{02BB}-\x{02BC}\x{02EC}\\
\x{0300}-\x{0304}\x{0306}-\x{030C}\x{030F}-\x{0311}\x{0313}-\x{0314}\x{031B}\x{0323}-\x{0328}\x{032D}-\x{032E}\\
\x{0330}-\x{0331}\x{0335}\x{0338}-\x{0339}\x{0342}\x{0345}\x{037B}-\x{037D}\x{0386}\x{0388}-\x{038A}\x{038C}\x{038E}-\x{03A1}\\
\x{03A3}-\x{03CE}\x{03FC}-\x{045F}\x{048A}-\x{0529}\x{052E}-\x{052F}\x{0531}-\x{0556}\x{0559}\x{0561}-\x{0586}\x{05B4}\\
\x{05D0}-\x{05EA}\x{05F0}-\x{05F2}\x{0620}-\x{063F}\x{0641}-\x{0655}\x{0660}-\x{0669}\x{0670}-\x{0672}\x{0674}\x{0679}-\x{068D}\\
\x{068F}-\x{06D3}\x{06D5}\x{06E5}-\x{06E6}\x{06EE}-\x{06FC}\x{06FF}\x{0750}-\x{07B1}\x{08A0}-\x{08AC}\x{08B2}\x{0901}-\x{094D}\\
\x{094F}-\x{0950}\x{0956}-\x{0957}\x{0960}-\x{0963}\x{0966}-\x{096F}\x{0971}-\x{0977}\x{0979}-\x{097F}\x{0981}-\x{0983}\\
\x{0985}-\x{098C}\x{098F}-\x{0990}\x{0993}-\x{09A8}\x{09AA}-\x{09B0}\x{09B2}\x{09B6}-\x{09B9}\x{09BC}-\x{09C4}\x{09C7}-\x{09C8}\\
\x{09CB}-\x{09CE}\x{09D7}\x{09E0}-\x{09E3}\x{09E6}-\x{09F1}\x{0A01}-\x{0A03}\x{0A05}-\x{0A0A}\x{0A0F}-\x{0A10}\x{0A13}-\x{0A28}\\
\x{0A2A}-\x{0A30}\x{0A32}\x{0A35}\x{0A38}-\x{0A39}\x{0A3C}\x{0A3E}-\x{0A42}\x{0A47}-\x{0A48}\x{0A4B}-\x{0A4D}\x{0A5C}\\
\x{0A66}-\x{0A74}\x{0A81}-\x{0A83}\x{0A85}-\x{0A8D}\x{0A8F}-\x{0A91}\x{0A93}-\x{0AA8}\x{0AAA}-\x{0AB0}\x{0AB2}-\x{0AB3}\\
\x{0AB5}-\x{0AB9}\x{0ABC}-\x{0AC5}\x{0AC7}-\x{0AC9}\x{0ACB}-\x{0ACD}\x{0AD0}\x{0AE0}-\x{0AE3}\x{0AE6}-\x{0AEF}\x{0B01}-\x{0B03}\\
\x{0B05}-\x{0B0C}\x{0B0F}-\x{0B10}\x{0B13}-\x{0B28}\x{0B2A}-\x{0B30}\x{0B32}-\x{0B33}\x{0B35}-\x{0B39}\x{0B3C}-\x{0B43}\\
\x{0B47}-\x{0B48}\x{0B4B}-\x{0B4D}\x{0B56}-\x{0B57}\x{0B5F}-\x{0B61}\x{0B66}-\x{0B6F}\x{0B71}\x{0B82}-\x{0B83}\x{0B85}-\x{0B8A}\\
\x{0B8E}-\x{0B90}\x{0B92}-\x{0B95}\x{0B99}-\x{0B9A}\x{0B9C}\x{0B9E}-\x{0B9F}\x{0BA3}-\x{0BA4}\x{0BA8}-\x{0BAA}\x{0BAE}-\x{0BB9}\\
\x{0BBE}-\x{0BC2}\x{0BC6}-\x{0BC8}\x{0BCA}-\x{0BCD}\x{0BD0}\x{0BD7}\x{0BE6}-\x{0BEF}\x{0C01}-\x{0C03}\x{0C05}-\x{0C0C}\\
\x{0C0E}-\x{0C10}\x{0C12}-\x{0C28}\x{0C2A}-\x{0C33}\x{0C35}-\x{0C39}\x{0C3D}-\x{0C44}\x{0C46}-\x{0C48}\x{0C4A}-\x{0C4D}\\
\x{0C55}-\x{0C56}\x{0C60}-\x{0C61}\x{0C66}-\x{0C6F}\x{0C82}-\x{0C83}\x{0C85}-\x{0C8C}\x{0C8E}-\x{0C90}\x{0C92}-\x{0CA8}\\
\x{0CAA}-\x{0CB3}\x{0CB5}-\x{0CB9}\x{0CBC}-\x{0CC4}\x{0CC6}-\x{0CC8}\x{0CCA}-\x{0CCD}\x{0CD5}-\x{0CD6}\x{0CE0}-\x{0CE3}\\
\x{0CE6}-\x{0CEF}\x{0CF1}-\x{0CF2}\x{0D02}-\x{0D03}\x{0D05}-\x{0D0C}\x{0D0E}-\x{0D10}\x{0D12}-\x{0D3A}\x{0D3D}-\x{0D43}\\
\x{0D46}-\x{0D48}\x{0D4A}-\x{0D4E}\x{0D57}\x{0D60}-\x{0D61}\x{0D66}-\x{0D6F}\x{0D7A}-\x{0D7F}\x{0D82}-\x{0D83}\x{0D85}-\x{0D8E}\\
\x{0D91}-\x{0D96}\x{0D9A}-\x{0DA5}\x{0DA7}-\x{0DB1}\x{0DB3}-\x{0DBB}\x{0DBD}\x{0DC0}-\x{0DC6}\x{0DCA}\x{0DCF}-\x{0DD4}\x{0DD6}\\
\x{0DD8}-\x{0DDE}\x{0DF2}\x{0E01}-\x{0E32}\x{0E34}-\x{0E3A}\x{0E40}-\x{0E4E}\x{0E50}-\x{0E59}\x{0E81}-\x{0E82}\x{0E84}\\
\x{0E87}-\x{0E88}\x{0E8A}\x{0E8D}\x{0E94}-\x{0E97}\x{0E99}-\x{0E9F}\x{0EA1}-\x{0EA3}\x{0EA5}\x{0EA7}\x{0EAA}-\x{0EAB}\\
\x{0EAD}-\x{0EB2}\x{0EB4}-\x{0EB9}\x{0EBB}-\x{0EBD}\x{0EC0}-\x{0EC4}\x{0EC6}\x{0EC8}-\x{0ECD}\x{0ED0}-\x{0ED9}\\
\x{0EDE}-\x{0EDF}\x{0F00}\x{0F20}-\x{0F29}\x{0F35}\x{0F37}\x{0F3E}-\x{0F42}\x{0F44}-\x{0F47}\x{0F49}-\x{0F4C}\x{0F4E}-\x{0F51}\\
\x{0F53}-\x{0F56}\x{0F58}-\x{0F5B}\x{0F5D}-\x{0F68}\x{0F6A}-\x{0F6C}\x{0F71}-\x{0F72}\x{0F74}\x{0F7A}-\x{0F80}\x{0F82}-\x{0F84}\\
\x{0F86}-\x{0F92}\x{0F94}-\x{0F97}\x{0F99}-\x{0F9C}\x{0F9E}-\x{0FA1}\x{0FA3}-\x{0FA6}\x{0FA8}-\x{0FAB}\x{0FAD}-\x{0FB8}\\
\x{0FBA}-\x{0FBC}\x{0FC6}\x{1000}-\x{1049}\x{1050}-\x{109D}\x{10C7}\x{10CD}\x{10D0}-\x{10F0}\x{10F7}-\x{10FA}\x{10FD}-\x{10FF}\\
\x{1200}-\x{1248}\x{124A}-\x{124D}\x{1250}-\x{1256}\x{1258}\x{125A}-\x{125D}\x{1260}-\x{1288}\x{128A}-\x{128D}\x{1290}-\x{12B0}\\
\x{12B2}-\x{12B5}\x{12B8}-\x{12BE}\x{12C0}\x{12C2}-\x{12C5}\x{12C8}-\x{12D6}\x{12D8}-\x{1310}\x{1312}-\x{1315}\x{1318}-\x{135A}\\
\x{135D}-\x{135F}\x{1380}-\x{138F}\x{1780}-\x{17A2}\x{17A5}-\x{17A7}\x{17A9}-\x{17B3}\x{17B6}-\x{17CA}\x{17D2}\x{17D7}\x{17DC}\\
\x{17E0}-\x{17E9}\x{1E00}-\x{1E99}\x{1E9E}\x{1EA0}-\x{1EF9}\x{1F00}-\x{1F15}\x{1F18}-\x{1F1D}\x{1F20}-\x{1F45}\x{1F48}-\x{1F4D}\\
\x{1F50}-\x{1F57}\x{1F59}\x{1F5B}\x{1F5D}\x{1F5F}-\x{1F70}\x{1F72}\x{1F74}\x{1F76}\x{1F78}\x{1F7A}\x{1F7C}\x{1F80}-\x{1FB4}\\
\x{1FB6}-\x{1FBA}\x{1FBC}\x{1FC2}-\x{1FC4}\x{1FC6}-\x{1FC8}\x{1FCA}\x{1FCC}\x{1FD0}-\x{1FD2}\x{1FD6}-\x{1FDA}\x{1FE0}-\x{1FE2}\\
\x{1FE4}-\x{1FEA}\x{1FEC}\x{1FF2}-\x{1FF4}\x{1FF6}-\x{1FF8}\x{1FFA}\x{1FFC}\x{2D27}\x{2D2D}\x{2D80}-\x{2D96}\x{2DA0}-\x{2DA6}\\
\x{2DA8}-\x{2DAE}\x{2DB0}-\x{2DB6}\x{2DB8}-\x{2DBE}\x{2DC0}-\x{2DC6}\x{2DC8}-\x{2DCE}\x{2DD0}-\x{2DD6}\x{2DD8}-\x{2DDE}\\
\x{3005}-\x{3007}\x{3041}-\x{3096}\x{3099}-\x{309A}\x{309D}-\x{309E}\x{30A1}-\x{30FA}\x{30FC}-\x{30FE}\x{3105}-\x{312D}\\
\x{31A0}-\x{31BA}\x{3400}-\x{4DB5}\x{4E00}-\x{9FD5}\x{A660}-\x{A661}\x{A674}-\x{A67B}\x{A67F}\x{A69F}\x{A717}-\x{A71F}\x{A788}\\
\x{A78D}-\x{A78E}\x{A790}-\x{A793}\x{A7A0}-\x{A7AA}\x{A7FA}\x{A9E7}-\x{A9FE}\x{AA60}-\x{AA76}\x{AA7A}-\x{AA7F}\x{AB01}-\x{AB06}\\
\x{AB09}-\x{AB0E}\x{AB11}-\x{AB16}\x{AB20}-\x{AB26}\x{AB28}-\x{AB2E}\x{AC00}-\x{D7A3}\x{FA0E}-\x{FA0F}\x{FA11}\x{FA13}-\x{FA14}\\
\x{FA1F}\x{FA21}\x{FA23}-\x{FA24}\x{FA27}-\x{FA29}\x{20000}-\x{2A6D6}\x{2A700}-\x{2B734}\x{2B740}-\x{2B81D}\x{2B820}-\x{2CEA1}\\
\x{0027}\x{002D}-\x{002E}\x{003A}\x{00B7}\x{0375}\x{058A}\x{05F3}-\x{05F4}\x{06FD}-\x{06FE}\x{0F0B}\x{200C}-\x{200D}\x{2010}\\
\x{2019}\x{2027}\x{30A0}\x{30FB}]/u";

					// No characters in the identifier (username) can be outside of the Identifier Profile
					if (preg_match_all($regexAllowedChars, $value, $nonIdentifierProfile))
					{
						$nonIdentifierProfileString = implode(',', array_unique($nonIdentifierProfile[0]));

						if (!preg_match('/[\p{Mc}]/u', $nonIdentifierProfileString))
						{
							$nonIdentifierProfileString .= 'whitespace';
						}

						// Enqueue error message and return false
						$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_IDPROFILE_REQUIRED', $nonIdentifierProfileString), 'warning');

						$result = false;
					}

					/*
					* // UAX31-D1. Default Identifier Syntax: <ID_Start>
					* // <identifier> := <ID_Start> <ID_Continue>*
					* if (!preg_match('/^[\p{Lu}|\p{Ll}|\p{Lt}|\p{Lm}|\p{Lo}|\p{Nl}]/u', $value))
					* {
					*	// Enqueue error message and return false
					*	$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_IDSTART_REQUIRED'), 'warning');
					*
					*	$result = false;
					* }
					*
					* // UAX31-D1. Default Identifier Syntax: <ID_Continue>
					* // http://www.unicode.org/reports/tr31/#Default_Identifier_Syntax
					* if (preg_match_all('/[^\p{Lu}\p{Ll}\p{Lt}\p{Lm}\p{Lo}\p{Nl}\p{Mn}\p{Mc}\p{Nd}\p{Pc}]/u', $value, $nonIdContinue))
					* {
					*	$nonIdContinueString = implode(' ', array_unique($nonIdContinue[0]));
					*
					*	// Enqueue error message and return false
					*	$app->enqueueMessage(JText::sprintf('COM_USERS_CONFIG_FIELD_USERNAME_IDCONTINUE_REQUIRED', $nonIdContinueString), 'warning');
					*
					*	$result = false;
					* }
					*/

					break;

				case 7:
					// EMAIL

					if (!JMailHelper::isEmailAddress($value) )
					{
						// Enqueue error message and return false
						$app->enqueueMessage(JText::_('COM_USERS_CONFIG_FIELD_USERNAME_EMAIL_REQUIRED'), 'warning');

						$result = false;
					}
					break;

				default:
					// NO OPTION
					$app->enqueueMessage(JText::_('COM_USERS_CONFIG_FIELD_USERNAME_NOOPTION'), 'warning');

					return false;
			}
		}

		return $result;
	}
}
