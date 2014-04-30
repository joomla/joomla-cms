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
 * Public Access to the Cloud Files Account
 *
 * @package     Joomla.Platform
 * @subpackage  Rackspace
 * @since       ??.?
 */
class JRackspacePublic extends JRackspaceObject
{
	/**
	 * @var    JRackspacePublicTempurl  Rackspace API object for creating a Temporary URL
	 * @since  ??.?
	 */
	protected $tempurl;

	/**
	 * @var    JRackspacePublicFormpost Rackspace API object for FormPost
	 * @since  ??.?
	 */
	protected $formpost;

	/**
	 * Magic method to lazily create API objects
	 *
	 * @param   string  $name  Name of property to retrieve.
	 *
	 * @return  JRackspaceObject  Rackspace API object
	 *
	 * @since   ??.?
	 * @throws  InvalidArgumentException
	 */
	public function __get($name)
	{
		$class = 'JRackspacePublic' . ucfirst($name);

		if (class_exists($class))
		{
			if (false == isset($this->$name))
			{
				$this->$name = new $class($this->options, $this->client);
			}

			return $this->$name;
		}

		throw new InvalidArgumentException(
			sprintf('Argument %s produced an invalid class name: %s', $name, $class)
		);
	}

	/**
	 * To create a Temporary URL, you must first set the metadata header
	 * X-Account-Meta-Temp-URL-Key on your Cloud Files account to a key
	 * that only you know.
	 *
	 * @param   string  $key  This key can be any arbitrary sequence as it is
	 *                        for encoding your account.
	 *
	 * @return string  The response body
	 *
	 * @since   ??.?
	 */
	public function setAccountTempUrlMetadataKey($key)
	{
		$authTokenHeaders = $this->getAuthTokenHeaders();
		$url = $authTokenHeaders["X-Storage-Url"];

		// Create the headers
		$headers = array(
			"Host" => $this->options->get("cdn.host"),
			"X-Account-Meta-Temp-Url-Key" => $key,
		);
		$headers["X-Auth-Token"] = $authTokenHeaders["X-Auth-Token"];

		// Send the http request
		$response = $this->client->get($url, $headers);

		if ($response->code / 100 == 2)
		{
			return "The metadata key was successfully set.";
		}

		return "The response code was " . $response->code . ".";
	}
}
