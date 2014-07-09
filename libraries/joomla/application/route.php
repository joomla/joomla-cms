<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Application
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
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
	 *                             2: Make URI unsecure using the global unsecure site URI.
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
		
		$current_uri = JUri::getInstance();

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		if ((int) $ssl || $current_uri->isSSL())
		{
			// Determine which scheme we want.
			$uri->setScheme(((int)$ssl === 1 || $current_uri->isSSL()) ? 'https' : 'http');
			$uri->setHost( $current_uri->getHost() );
			$uri->setPort( $current_uri->getPort() );
			$scheme = array_merge($scheme, array('host', 'port', 'scheme'));
		}

		$url = $uri->toString($scheme);

		// Replace spaces.
		$url = preg_replace('/\s/u', '%20', $url);

		if ($xhtml)
		{
			$url = htmlspecialchars($url);
		}

		return $url;
	}
}
