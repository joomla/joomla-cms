<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * Linkedin API Companies class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  Linkedin
 * @since       12.3
 */
class JLinkedinCompanies extends JLinkedinObject
{
	/**
	 * Method to retrieve companies using a company ID, a universal name, or an email domain.
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   integer         $id      The unique internal numeric company identifier.
	 * @param   string          $name    The unique string identifier for a company.
	 * @param   string          $domain  Company email domains.
	 * @param   string          $fields  Request fields beyond the default ones.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 * @throws  RuntimeException
	 */
	public function getCompanies($oauth, $id = null, $name = null, $domain = null, $fields = null)
	{
		// At least one value is needed to retrieve data.
		if ($id == null && $name == null && $domain == null)
		{
			// We don't have a valid entry
			throw new RuntimeException('You must specify a company ID, a universal name, or an email domain.');
		}

		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/companies';

		if ($id && $name)
		{
			$base .= '::(' . $id . ',universal-name=' . $name . ')';
		}
		elseif ($id)
		{
			$base .= '/' . $id;
		}
		elseif ($name)
		{
			$base .= '/universal-name=' . $name;
		}

		// Set request parameters.
		$data['format'] = 'json';

		if ($domain)
		{
			$data['email-domain'] = $domain;
		}

		// Check if fields is specified.
		if ($fields)
		{
			$base .= ':' . $fields;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}

	/**
	 * Method to read shares for a particular company .
	 *
	 * @param   JLinkedinOAuth  $oauth   The JLinkedinOAuth object.
	 * @param   string          $id      The unique company identifier.
	 * @param   string          $type    Any valid Company Update Type from the table: https://developer.linkedin.com/reading-company-updates.
	 * @param   integer         $count   Maximum number of updates to return.
	 * @param   integer         $start   The offset by which to start Network Update pagination.
	 *
	 * @return  array  The decoded JSON response
	 *
	 * @since   12.3
	 */
	public function getUpdates($oauth, $id, $type = null, $count = 0, $start = 0)
	{
		// Set parameters.
		$parameters = array(
			'oauth_token' => $oauth->getToken('key')
		);

		// Set the API base
		$base = '/v1/companies/' . $id . '/updates';

		// Set request parameters.
		$data['format'] = 'json';

		// Check if type is specified.
		if ($type)
		{
			$data['event-type'] = $type;
		}

		// Check if count is specified.
		if ($count > 0)
		{
			$data['count'] = $count;
		}

		// Check if start is specified.
		if ($start > 0)
		{
			$data['start'] = $start;
		}

		// Build the request path.
		$path = $this->getOption('api.url') . $base;

		// Send the request.
		$response = $oauth->oauthRequest($path, 'GET', $parameters, $data);
		return json_decode($response->body);
	}
}
