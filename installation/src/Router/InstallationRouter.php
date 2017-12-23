<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Installation\Router;

defined('_JEXEC') or die;

use Joomla\CMS\Router\Router;
use Joomla\CMS\Uri\Uri;

/**
 * Class to create and parse routes.
 *
 * @since  1.5
 */
class InstallationRouter extends Router
{
	/**
	 * Function to convert a route to an internal URI
	 *
	 * @param   Uri   &$uri     The uri.
	 * @param   bool  $setVars  Set the parsed data in the internal
	 *                             storage for current-request-URLs
	 *
	 * @return  boolean
	 *
	 * @since   1.5
	 */
	public function parse(&$uri, $setVars = false)
	{
		return true;
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param   string  $url  The internal URL
	 *
	 * @return  string  The absolute search engine friendly URL
	 *
	 * @since   1.5
	 */
	public function build($url)
	{
		$url = str_replace('&amp;', '&', $url);

		return new Uri($url);
	}
}
