<?php
/**
 * Part of the Joomla Framework Uri Package
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

namespace Joomla\Uri;

/**
 * Uri Helper
 *
 * This class provides a UTF-8 safe version of parse_url().
 *
 * @since  1.0
 */
class UriHelper
{
	/**
	 * Does a UTF-8 safe version of PHP parse_url function
	 *
	 * @param   string   $url        URL to parse
	 * @param   integer  $component  Retrieve just a specific URL component
	 *
	 * @return  array|boolean  Associative array or false if badly formed URL.
	 *
	 * @link    https://www.php.net/manual/en/function.parse-url.php
	 * @since   1.0
	 */
	public static function parse_url($url, $component = -1)
	{
		// If no UTF-8 chars in the url just parse it using php native parse_url which is faster.
		if (utf8_decode($url) === $url)
		{
			return parse_url($url, $component);
		}

		// URL with UTF-8 chars in the url.

		// Build the reserved uri encoded characters map.
		$reservedUriCharactersMap = [
			'%21' => '!',
			'%2A' => '*',
			'%27' => "'",
			'%28' => '(',
			'%29' => ')',
			'%3B' => ';',
			'%3A' => ':',
			'%40' => '@',
			'%26' => '&',
			'%3D' => '=',
			'%24' => '$',
			'%2C' => ',',
			'%2F' => '/',
			'%3F' => '?',
			'%23' => '#',
			'%5B' => '[',
			'%5D' => ']',
		];

		// Encode the URL (so UTF-8 chars are encoded), revert the encoding in the reserved uri characters and parse the url.
		$parts = parse_url(strtr(urlencode($url), $reservedUriCharactersMap), $component);

		// With a well formed url decode the url (so UTF-8 chars are decoded).
		return $parts ? array_map('urldecode', $parts) : $parts;
	}
}
