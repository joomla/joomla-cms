<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * GitHub API Meta class.
 *
 * @since       3.2.0
 * @deprecated  4.0  Use the `joomla/github` package via Composer instead
 */
class JGithubMeta extends JGithubObject
{
	/**
	 * Method to get the authorized IP addresses for services
	 *
	 * @return  array  Authorized IP addresses
	 *
	 * @since   3.2.0
	 * @throws  DomainException
	 */
	public function getMeta()
	{
		// Build the request path.
		$path = '/meta';

		$githubIps = $this->processResponse($this->client->get($this->fetchUrl($path)), 200);

		/*
		 * The response body returns the IP addresses in CIDR format
		 * Decode the response body and strip the subnet mask information prior to
		 * returning the data to the user.  We're assuming quite a bit here that all
		 * masks will be /32 as they are as of the time of development.
		 */

		$authorizedIps = array();

		foreach ($githubIps as $key => $serviceIps)
		{
			// The first level contains an array of IPs based on the service
			$authorizedIps[$key] = array();

			foreach ($serviceIps as $serviceIp)
			{
				// The second level is each individual IP address, strip the mask here
				$authorizedIps[$key][] = substr($serviceIp, 0, -3);
			}
		}

		return $authorizedIps;
	}
}
