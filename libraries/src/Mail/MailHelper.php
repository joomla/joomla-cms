<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mail;

defined('JPATH_PLATFORM') or die;

/**
 * Email helper class, provides static methods to perform various tasks relevant
 * to the Joomla email routines.
 *
 * TODO: Test these methods as the regex work is first run and not tested thoroughly
 *
 * @since  1.7.0
 */
abstract class MailHelper
{
	/**
	 * Cleans single line inputs.
	 *
	 * @param   string  $value  String to be cleaned.
	 *
	 * @return  string  Cleaned string.
	 *
	 * @since   1.7.0
	 */
	public static function cleanLine($value)
	{
		$value = \JStringPunycode::emailToPunycode($value);

		return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', $value));
	}

	/**
	 * Cleans multi-line inputs.
	 *
	 * @param   string  $value  Multi-line string to be cleaned.
	 *
	 * @return  string  Cleaned multi-line string.
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public static function cleanBody($body)
	{
		// Strip all email headers from a string
		return preg_replace("/((From:|To:|Cc:|Bcc:|Subject:|Content-type:) ([\S]+))/", '', $body);
	}

	/**
	 * Cleans any injected headers from the subject string.
	 *
	 * @param   string  $subject  email subject string.
	 *
	 * @return  string  Cleaned email subject string.
	 *
	 * @since   1.7.0
	 */
	public static function cleanSubject($subject)
	{
		return preg_replace("/((From:|To:|Cc:|Bcc:|Content-type:) ([\S]+))/", '', $subject);
	}

	/**
	 * Verifies that an email address does not have any extra headers injected into it.
	 *
	 * @param   string  $address  email address.
	 *
	 * @return  mixed   email address string or boolean false if injected headers are present.
	 *
	 * @since   1.7.0
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
	 * @since   1.7.0
	 */
	public static function isEmailAddress($email)
	{
		// Split the email into a local and domain
		$atIndex = strrpos($email, '@');
		$domain = substr($email, $atIndex + 1);
		$local = substr($email, 0, $atIndex);

		// Check Length of domain
		$domainLen = strlen($domain);

		if ($domainLen < 1 || $domainLen > 255)
		{
			return false;
		}

		/*
		 * Check the local address
		 * We're a bit more conservative about what constitutes a "legal" address, that is, a-zA-Z0-9.!#$%&'*+/=?^_`{|}~-
		 * The first and last character in local cannot be a period ('.')
		 * Also, period should not appear 2 or more times consecutively
		 */
		$allowed = "a-zA-Z0-9.!#$%&'*+\/=?^_`{|}~-";
		$regex = "/^[$allowed][\.$allowed]{0,63}$/";

		if (!preg_match($regex, $local) || substr($local, -1) == '.' || $local[0] == '.' || preg_match('/\.\./', $local))
		{
			return false;
		}

		// No problem if the domain looks like an IP address, ish
		$regex = '/^[0-9\.]+$/';

		if (preg_match($regex, $domain))
		{
			return true;
		}

		// Check Lengths
		$localLen = strlen($local);

		if ($localLen < 1 || $localLen > 64)
		{
			return false;
		}

		// Check the domain
		$domain_array = explode('.', $domain);
		$regex = '/^[a-zA-Z0-9-]+(?:\.[a-zA-Z0-9-]+)*$/';

		foreach ($domain_array as $domain)
		{
			// Convert domain to punycode
			$domain = \JStringPunycode::toPunycode($domain);

			// Must be something
			if (!$domain)
			{
				return false;
			}

			// Check for invalid characters
			if (!preg_match($regex, $domain))
			{
				return false;
			}

			// Check for a dash at the beginning of the domain
			if (strpos($domain, '-') === 0)
			{
				return false;
			}

			// Check for a dash at the end of the domain
			$length = strlen($domain) - 1;

			if (strpos($domain, '-', $length) === $length)
			{
				return false;
			}
		}

		return true;
	}
}
