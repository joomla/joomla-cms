<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Facebook
 */
class JFacebookObjectMock extends JFacebookObject
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
	 */
	public function fetchUrl($path, $page = 0, $limit = 0)
	{
		return parent::fetchUrl($path, $page, $limit);
	}
}
