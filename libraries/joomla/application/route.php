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
	private static $_router = array();

	/**
	 * Translates an internal Joomla URL to a humanly readable URL.
	 *
	 * @param   string   $url           Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml         Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl           Secure state for the resolved URI.
	 *                                    0: (default) No change, use the protocol currently used in the request
	 *                                    1: Make URI secure using global secure site URI.
	 *                                    2: Make URI unsecure using the global unsecure site URI.
	 * @param   string   $forcedClient  Force route for a specific client.
	 *                                    null: (default) don't force client.
	 *                                    site: force site (frontend) client.
	 *                                    administrator: force administrator (backend) client.
	 *
	 * @return string The translated humanly readable URL.
	 *
	 * @since   11.1
	 */
	public static function _($url, $xhtml = true, $ssl = null, $forcedClient = null)
	{
		// Get the router.
		$app = JFactory::getApplication();

		// Check which client we are using.
		$client = isset($forcedClient) ? $forcedClient : $app->getName();

		if (!isset(self::$_router[$client]))
		{
			self::$_router[$client] = $app->getRouter($client);

			// Make sure that we have our router
			if (!self::$_router[$client])
			{
				return;
			}
		}

		if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		// Build route.
		$uri = self::$_router[$client]->build($url);

		$scheme = array('path', 'query', 'fragment');

		/*
		 * Get the secure/unsecure URLs.
		 *
		 * If the first 5 characters of the BASE are 'https', then we are on an ssl connection over
		 * https and need to set our secure URL to the current request URL, if not, and the scheme is
		 * 'http', then we need to do a quick string manipulation to switch schemes.
		 */
		if ((int) $ssl || $uri->isSsl())
		{
			static $host_port;

			if (!is_array($host_port))
			{
				$uri2 = JUri::getInstance();
				$host_port = array($uri2->getHost(), $uri2->getPort());
			}

			// Determine which scheme we want.
			$uri->setScheme(((int) $ssl === 1 || $uri->isSsl()) ? 'https' : 'http');
			$uri->setHost($host_port[0]);
			$uri->setPort($host_port[1]);
			$scheme = array_merge($scheme, array('host', 'port', 'scheme'));
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
