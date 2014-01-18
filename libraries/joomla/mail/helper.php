<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Mail
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Email helper class, provides static methods to perform various tasks relevant
 * to the Joomla email routines.
 *
 * TODO: Test these methods as the regex work is first run and not tested thoroughly
 *
 * @package     Joomla.Platform
 * @subpackage  Mail
 * @since       11.1
 */
abstract class JMailHelper
{
	/**
	 * Cleans single line inputs.
	 *
	 * @param   string  $value  String to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @since   11.1
	 */
	public static function cleanLine($value)
	{
		$value = JStringPunycode::emailToPunycode($value);

		return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', $value));
	}

	/**
	 * Cleans multi-line inputs.
	 *
	 * @param   string  $value  Multi-line string to be cleaned.
	 *
	 * @return  string  Cleaned multi-line string.
	 *
	 * @since   11.1
	 */
	public static function cleanText($value)
	{
		return trim(preg_replace('/(%0A|%0D|\n+|\r+)(content-type:|to:|cc:|bcc:)/i', '', $value));
	}

	/**
	 * Cleans any injected headers from the email body.
	 *
	 * @param   string  $body  email body string.
	 *
	 * @return  string  Cleaned email body string.
	 *
	 * @since   11.1
	 */
	public static function cleanBody($body)
	{
		// Strip all email headers from a string
		return preg_replace("/((From:|To:|Cc:|Bcc:|Subject:|Content-type:) ([\S]+))/", "", $body);
	}

	/**
	 * Cleans any injected headers from the subject string.
	 *
	 * @param   string  $subject  email subject string.
	 *
	 * @return  string  Cleaned email subject string.
	 *
	 * @since   11.1
	 */
	public static function cleanSubject($subject)
	{
		return preg_replace("/((From:|To:|Cc:|Bcc:|Content-type:) ([\S]+))/", "", $subject);
	}

	/**
	 * Verifies that an email address does not have any extra headers injected into it.
	 *
	 * @param   string  $address  email address.
	 *
	 * @return  mixed   email address string or boolean false if injected headers are present.
	 *
	 * @since   11.1
	 */
	public static function cleanAddress($address)
	{
		if (preg_match("[\s;,]", $address))
		{
			return false;
		}

		return $address;
	}

	/**
	 * Verifies that the string is in a proper email address format.
	 *
	 * @param   string  $email  String to be verified.
	 *
	 * @return  boolean  True if string has the correct format; false otherwise.
	 *
	 * @since   11.1
	 */
	public static function isEmailAddress($email)
	{
		// The regular expression to use in testing a form field value.
		$regex = '^[a-zA-Z0-9.!#$%&‚Äô*+/=?^_`{|}~-]+@[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$';

		// Handle idn e-mail addresses by converting to punycode.
		$email = JStringPunycode::emailToPunycode($email);

		// Test the value against the regular expression.
		if (preg_match(chr(1) . $regex . chr(1), $email))
		{
			return true;
		}

		return false;
	}
}
