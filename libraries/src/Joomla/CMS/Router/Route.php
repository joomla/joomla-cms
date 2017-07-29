<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

defined('JPATH_PLATFORM') or die;

/**
 * Route handling class
 *
 * @since       11.1
 * @deprecated  5.0 Use \Joomla\CMS\Router\Router instead
 */
class Route
{
	/**
	 * Translates an internal Joomla URL to a humanly readable URL.
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *                             0: (default) No change, use the protocol currently used in the request
	 *                             1: Make URI secure using global secure site URI.
	 *                             2: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return string The translated humanly readable URL.
	 *
	 * @since   11.1
	 * @deprecated  Use \Joomla\CMS\Factory::getApplication()->getRouter()->route() instead
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		return \Joomla\CMS\Factory::getApplication()->getRouter()->route($url, $xhtml, $ssl);
	}
}
