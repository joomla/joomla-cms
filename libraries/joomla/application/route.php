<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

/**
 * Route handling class
 *
 * @since  11.1
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
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		if (!self::$_router)
		{
			// Get the router.
			$app = JFactory::getApplication();
			self::$_router = $app::getRouter();

			// Make sure that we have our router
			if (!self::$_router)
			{
				return null;
			}
		}

		if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		// Build route.
		$uri = self::$_router->build($url);

		$scheme = array('path', 'query', 'fragment');

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the base URL is secure (uses HTTPS), then we need to add 'host', 'scheme' and 'port' to $scheme,
		 * otherwise we have to set scheme and port according to $ssl (if $ssl is 1 we need HTTPS scheme,
		 * if $ssl is 2 we need HTTP scheme, for details see JUri::siteScheme()) and we also need to add 'host',
		 * 'scheme' and 'port' to $scheme.
		 */
		if ($uri->isSsl())
		{
			$scheme = array_merge($scheme, array('host', 'scheme', 'port'));
		}
		elseif ((int) $ssl)
		{
			$uri = JUri::siteScheme($uri, $ssl == 1);
			$scheme = array_merge($scheme, array('host', 'scheme', 'port'));
		}

		$url = $uri->toString($scheme);

		// Replace spaces.
		$url = preg_replace('/\s/u', '%20', $url);

		if ($xhtml)
		{
			$url = htmlspecialchars($url, ENT_COMPAT, 'UTF-8');
		}

		return $url;
	}
}
