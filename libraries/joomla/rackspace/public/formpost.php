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
class JRackspacePublicFormpost extends JRackspacePublic
{
	/**
	 * Generate a TempURL.
	 *
	 * @param   string  $cfUrl         Form action is the Cloud Files URL (CF-url)
	 *                                 to the destination where files will be uploaded
	 * @param   string  $maxFileSize   The maximum file size in bytes (may not exceed 5GB)
	 * @param   string  $maxFileCount  The maximum number of files
	 * @param   string  $expires       The expires attribute is the Unix timestamp when
	 *                                 the form is invalidated
	 * @param   string  $key           This key can be any arbitrary sequence as it is
	 *                                 for encoding your account.
	 * @param   string  $redirectUrl   Optional: The redirect attribute is the URL
	 *                                 of the page that displays on your website after
	 *                                 the form processes.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function createForm($cfUrl, $maxFileSize, $maxFileCount, $expires,
		$key, $redirectUrl = "")
	{
		$form = '<form action="' . $cfUrl . '" method="POST" enctype="multipart/form-data">' . "\n\t";

		if ($redirectUrl != "")
		{
			$form .= '<input type="hidden" name="redirect" value="' . $redirectUrl . '" />' . "\n\t";
		}

		$form .= '<input type="hidden" name="max_file_size" value="' . $maxFileSize . '" />' . "\n\t"
			. '<input type="hidden" name="max_file_count" value="' . $maxFileCount . '" />' . "\n\t"
			. '<input type="hidden" name="expires" value="' . $expires . '" />' . "\n\t";

		// Create the HMAC-SHA1 signature
		$hmac_body = "$cfUrl\n$redirectUrl\n$maxFileSize\n$maxFileCount\n$expires";
		$hmac = hash_hmac("sha1", $hmac_body, $key);

		$form .= '<input type="hidden" name="signature" value="' . $hmac . '" />' . "\n\t"
			. '<input type="file" name="file1" /><br />' . "\n\t"
			. '<input type="submit" />' . "\n"
			. '</form>' . "\n";

		return $form;
	}
}
