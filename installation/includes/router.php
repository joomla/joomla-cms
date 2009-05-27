<?php
/**
 * @version		$Id: router.php 245 2009-05-26 12:43:46Z andrew.eddie $
 * @package		Joomla.Installation
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License <http://www.gnu.org/copyleft/gpl.html>
 */

// No direct access
defined('JPATH_BASE') or die;

jimport('joomla.application.router');

/**
 * Class to create and parse routes
 *
 * @package 	Joomla
 * @since		1.5
 */
class JRouterInstallation extends JObject
{
	/**
	 * Function to convert a route to an internal URI
	 *
	 * @access public
	 */
	function parse($url)
	{
		return true;
	}

	/**
	 * Function to convert an internal URI to a route
	 *
	 * @param	string	$string	The internal URL
	 * @return	string	The absolute search engine friendly URL
	 * @since	1.5
	 */
	function build($url)
	{
		$url = str_replace('&amp;', '&', $url);

		return $url;
	}
}