<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * JMediawikiObjectMock class.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mediawiki
 *
 * @since       12.3
 */
class JMediawikiObjectMock extends JMediawikiObject
{
	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string  $path  URL to inflect
	 *
	 * @return  string   The request URL.
	 *
	 * @since   12.3
	 */
	public function fetchUrl($path)
	{
		return parent::fetchUrl($path);
	}

	/**
	 * Method to build request parameters from a string array.
	 *
	 * @param   array  $params  string array that contains the parameters
	 *
	 * @return  string   request parameter
	 *
	 * @since   12.3
	 */
	public function buildParameter($params)
	{
		return parent::buildParameter($params);
	}

	/**
	 * Method to validate response for errors
	 *
	 * @param   JHttpresponse  $response  reponse from the mediawiki server
	 *
	 * @return  Object
	 *
	 * @since   12.3
	 */
	public function validateResponse($response)
	{
		return parent::validateResponse($response);
	}
}
