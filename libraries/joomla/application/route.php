<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Route handling class
 *
 * @package     Joomla.Platform
 * @subpackage  Application
 * @since       11.1
 */
class JRoute
{
	/**
	 * The route object so we don't have to keep fetching it.
	 *
	 * @var    JRouter
	 * @since  12.2
	 */
	private static $_router = null;

	/**
	 * Translates an internal Joomla URL to a humanly readible URL.
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compilance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *                             1: Make URI secure using global secure site URI.
	 *                             0: Leave URI in the same secure state as it was passed to the function.
	 *                            -1: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return  The translated humanly readible URL.
	 *
	 * @since   11.1
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		if (!self::$_router)
		{
			// Get the router.
			self::$_router = JFactory::getApplication()->getRouter();

			// Make sure that we have our router
			if (!self::$_router)
			{
				return null;
			}
		}

		if ((strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		// Build route.
		$uri = self::$_router->build($url);
		$url = $uri->toString(array('path', 'query', 'fragment'));

		// Replace spaces.
		$url = preg_replace('/\s/u', '%20', $url);

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		if ((int) $ssl)
		{
			$uri = JURI::getInstance();

			// Get additional parts.
			static $prefix;
			if (!$prefix)
			{
				$prefix = $uri->toString(array('host', 'port'));
			}

			// Determine which scheme we want.
			$scheme = ((int) $ssl === 1) ? 'https' : 'http';

			// Make sure our URL path begins with a slash.
			if (!preg_match('#^/#', $url))
			{
				$url = '/' . $url;
			}

			// Build the URL.
			$url = $scheme . '://' . $prefix . $url;
		}

		if ($xhtml)
		{
			$url = htmlspecialchars($url);
		}

		return $url;
	}
}
