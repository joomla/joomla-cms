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
 * Defines the operations on CDN object services
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspaceCdnObject extends JRackspaceCdn
{
	/**
	 * You can perform DELETE operations against a CDN-enabled object when you
	 * find it absolutely necessary to remove the object from public access
	 * and you cannot wait for the TTL to expire.
	 * You may only DELETE up to 25 objects per day.
	 * In order to remove the container from the CDN, the X-CDN-Enabled flag
	 * has to be set to to False.
	 *
	 * @param   array  $object     The path to the object to be deleted
	 * @param   array  $emailList  An array of emails that will receive a
	 *                             notification that the object was deleted
	 *
	 * @return string  A message corresponding to the response code
	 *
	 * @since   ??.?
	 */
	public function purgeCdnEnabledObject($object, $emailList)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-CDN-Management-Url"] . "/" . $object;

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("cdn.host"),
			"X-Purge-Email" => implode(", ", $emailList),
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->delete($url, $headers);

		return $this->displayResponseCodeAndHeaders($response);
	}
}
