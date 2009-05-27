<?php
/**
 * @version		$Id: helper.php 11380 2009-01-01 15:48:59Z ian $
 * @package		Joomla.Framework
 * @subpackage	Mail
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

/**
 * E-Mail helper class, provides static methods to perform various tasks relevant
 * to the Joomla e-mail routines.
 *
 * TODO: Test these methods as the regex work is first run and not tested thoroughly
 *
 * @static
 * @package 	Joomla.Framework
 * @subpackage	Mail
 * @since		1.5
 */
class JMailHelper
{
	/**
	 * Cleans single line inputs.
	 *
	 * @static
	 * @param string $value String to be cleaned.
	 * @return string Cleaned string.
	 */
	function cleanLine($value) {
		return trim(preg_replace('/(%0A|%0D|\n+|\r+)/i', '', $value));
	}

	/**
	 * Cleans multi-line inputs.
	 *
	 * @static
	 * @param string $value Multi-line string to be cleaned.
	 * @return string Cleaned multi-line string.
	 */
	function cleanText($value) {
		return trim(preg_replace('/(%0A|%0D|\n+|\r+)(content-type:|to:|cc:|bcc:)/i', '', $value));
	}

	/**
	 * Cleans any injected headers from the E-Mail body.
	 *
	 * @static
	 * @param string $body E-Mail body string.
	 * @return string Cleaned E-Mail body string.
	 * @since 1.5
	 */
	function cleanBody($body) {
		// Strip all E-Mail headers from a string
		return preg_replace("/((From:|To:|Cc:|Bcc:|Subject:|Content-type:) ([\S]+))/", "", $body);
	}

	/**
	 * Cleans any injected headers from the subject string.
	 *
	 * @static
	 * @param string $subject E-Mail subject string.
	 * @return string Cleaned E-Mail subject string.
	 * @since 1.5
	 */
	function cleanSubject($subject) {
		return preg_replace("/((From:|To:|Cc:|Bcc:|Content-type:) ([\S]+))/", "", $subject);
	}

	/**
	 * Verifies that an e-mail address does not have any extra headers injected into it.
	 *
	 * @static
	 * @param string $address E-Mail address.
	 * @return string|false E-Mail address string or boolean false if injected headers are present.
	 * @since 1.5
	 */
	function cleanAddress($address)
	{
		if (preg_match("[\s;,]", $address)) {
			return false;
		}
		return $address;
	}

	/**
	 * Verifies that the string is in a proper e-mail address format.
	 *
	 * @static
	 * @param string $email String to be verified.
	 * @return boolean True if string has the correct format; false otherwise.
	 * @since 1.5
	 */
	function isEmailAddress($email)
	{

		// Split the email into a local and domain
		$atIndex	= strrpos($email, "@");
		$domain		= substr($email, $atIndex+1);
		$local		= substr($email, 0, $atIndex);

		// Check Length of domain
		$domainLen	= strlen($domain);
		if ($domainLen < 1 || $domainLen > 255) {
			return false;
		}

		// Check the local address
		// We're a bit more conservative about what constitutes a "legal" address, that is, A-Za-z0-9!#$%&\'*+/=?^_`{|}~-
		$allowed	= 'A-Za-z0-9!#&*+=?_-';
		$regex		= "/^[$allowed][\.$allowed]{0,63}$/";
		if (! preg_match($regex, $local)) {
			return false;
		}

		// No problem if the domain looks like an IP address, ish
		$regex		= '/^[0-9\.]+$/';
		if (preg_match($regex, $domain)) {
			return true;
		}

		// Check Lengths
		$localLen	= strlen($local);
		if ($localLen < 1 || $localLen > 64) {
			return false;
		}

		// Check the domain
		$domain_array	= explode(".", rtrim($domain, '.'));
		$regex		= '/^[A-Za-z0-9-]{0,63}$/';
		foreach ($domain_array as $domain) {

			// Must be something
			if (! $domain) {
				return false;
			}

			// Check for invalid characters
			if (! preg_match($regex, $domain)) {
				return false;
			}

			// Check for a dash at the beginning of the domain
			if (strpos($domain, '-') === 0) {
				return false;
			}

			// Check for a dash at the end of the domain
			$length = strlen($domain) -1;
			if (strpos($domain, '-', $length) === $length) {
				return false;
			}

		}

		return true;
	}

}
