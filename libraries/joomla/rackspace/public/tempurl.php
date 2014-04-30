<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * The Temporary URL feature (TempURL) allows you to create limited-time Internet
 * addresses that allow you to grant limited access to your Cloud Files account.
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspacePublicTempurl extends JRackspacePublic
{
	/**
	 * Generate a TempURL.
	 *
	 * @param   string  $method  GET or PUT
	 * @param   string  $url     The storage URL
	 * @param   string  $ttl     The number of seconds for which the temporary
	 *                           URL is available
	 * @param   string  $key     This key can be any arbitrary sequence as it is
	 *                           for encoding your account.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function createTempUrl($method, $url, $ttl, $key)
	{
		$method = strtoupper($method);
		list($base_url, $object_path) = preg_split("/v1/", $url);
		$object_path = "v1" . $object_path;
		$ttl = (int) $ttl;
		$expires = (int) (time() + $ttl);
		$hmac_body = "$method\n$expires\n$object_path";
		$sig = hash_hmac("sha1", $hmac_body, $key);

		return "$base_url$object_path?" .
			"temp_url_sig=$sig&temp_url_expires=$expires";
	}
}
