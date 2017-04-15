<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Defines the GET operation on the service
 *
 * @package     Joomla.Platform
 * @subpackage  Googlecloudstorage
 * @since       ??.?
 */
class JGooglecloudstorageServiceGet extends JGooglecloudstorageService
{
	/**
	 * Lists all of the buckets in a specified project.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function getService()
	{
		$url = "https://" . $this->options->get("api.url") . "/";

		// The headers may be optionally set in advance
		$headers = array(
			"Host" => $this->options->get("api.url"),
			"x-goog-project-id" => $this->options->get("project.id"),
		);

		return $this->commonGetOperations($url, $headers);
	}
}
