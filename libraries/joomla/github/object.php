<?php
/**
 * @package     Joomla.Platform
 * @subpackage  GitHub
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die();

/**
 * GitHub API object class for the Joomla Platform.
 *
 * @package     Joomla.Platform
 * @subpackage  GitHub
 * @since       11.4
 */
abstract class JGithubObject
{
	/**
	 * @var    JRegistry  Options for the GitHub object.
	 * @since  11.4
	 */
	protected $options;

	/**
	 * @var    JGithubHttp  The HTTP client object to use in sending HTTP requests.
	 * @since  11.4
	 */
	protected $client;

	/**
	 * Constructor.
	 *
	 * @param   JRegistry    $options  GitHub options object.
	 * @param   JGithubHttp  $client   The HTTP client object.
	 *
	 * @since   11.4
	 */
	public function __construct(JRegistry $options = null, JGithubHttp $client = null)
	{
		$this->options = isset($options) ? $options : new JRegistry();
		$this->client = isset($client) ? $client : new JGithubHttp();
	}

	/**
	 * Github pagination inflection method
	 *
	 * Adds the appropriate terms to the request string to correctly paginate
	 *
	 * @param   string   $url       URL to inflect
	 * @param   integer  $page      Page to request
	 * @param   integer  $per_page  Number of results to return per page
	 *
	 * @return  string   The inflected URL
	 *
	 * @since   11.4
	 */
	protected function paginate($url, $page = 0, $per_page = 0)
	{
		$query_string = array();

		if ($page > 0) {
			$query_string[] = 'page='.(int)$page;
		}

		if ($per_page > 0) {
			$query_string[] = 'per_page='.(int)$per_page;
		}

		if (isset($query_string[0])) {
			$query = implode('&', $query_string);
		} else {
			$query = '';
		}

		if (strlen($query) > 0) {
			if (strpos($url, '?') === false) {
				$url .= '?'.$query;
			} else {
				$url .= '&'.$query;
			}
		}

		return $url;
	}
}
