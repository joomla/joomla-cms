<?php
/**
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * OAuth HMAC-SHA1 Signature Method class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  OAuth1
 * @since       12.1
 */
class JOAuth1MessageSignerHMAC implements JOAuth1MessageSigner
{
	/**
	 * Calculate and return the OAuth message signature using HMAC-SHA1
	 *
	 * @param   string  $baseString        The OAuth message as a normalized base string.
	 * @param   string  $clientSecret      The OAuth client's secret.
	 * @param   string  $credentialSecret  The OAuth credentials' secret.
	 *
	 * @return  string  The OAuth message signature.
	 *
	 * @since   12.1
	 * @throws  InvalidArgumentException
	 */
	public function sign($baseString, $clientSecret, $credentialSecret)
	{
		// Build the key for hashing the base string.
		$key = $clientSecret . '&' . $credentialSecret;

		// Generate the binary hash of the based string and key.
		$hmac = hash_hmac('sha1', $baseString, $key, true);

		return base64_encode($hmac);
	}
}
