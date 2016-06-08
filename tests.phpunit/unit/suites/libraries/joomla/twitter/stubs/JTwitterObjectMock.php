<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 * @since       12.3
 */
class JTwitterObjectMock extends JTwitterObject
{
	/**
	 * Method to build and return a full request URL for the request.  This method will
	 * add appropriate pagination details if necessary and also prepend the API url
	 * to have a complete URL for the request.
	 *
	 * @param   string   $path   URL to inflect
	 * @param   integer  $page   Page to request
	 * @param   integer  $limit  Number of results to return per page
	 *
	 * @return  string   The request URL.
	 *
	 * @since   12.3
	 */
	public function fetchUrl($path, $page = 0, $limit = 0)
	{
		return parent::fetchUrl($path, $page, $limit);
	}
}
