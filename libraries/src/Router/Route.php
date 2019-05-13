<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Router;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

/**
 * Route handling class
 *
 * @since  1.7.0
 */
class Route
{
	/**
	 * The route object so we don't have to keep fetching it.
	 *
	 * @var    Router[]
	 * @since  3.0.1
	 */
	private static $_router = array();

	/**
	 * Translates an internal Joomla URL to a humanly readable URL. This method builds links for the current active client.
	 *
	 * @param   string   $url    Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml  Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl    Secure state for the resolved URI.
	 *                             0: (default) No change, use the protocol currently used in the request
	 *                             1: Make URI secure using global secure site URI.
	 *                             2: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return  string  The translated humanly readable URL.
	 *
	 * @since   1.7.0
	 */
	public static function _($url, $xhtml = true, $ssl = null)
	{
		try
		{
			$app    = Factory::getApplication();
			$client = $app->getName();

			return static::link($client, $url, $xhtml, $ssl);
		}
		catch (\RuntimeException $e)
		{
			// Before 3.9.0 this method failed silently on router error. This B/C will be removed in Joomla 4.0.
			return null;
		}
	}

	/**
	 * Translates an internal Joomla URL to a humanly readable URL.
	 * NOTE: To build link for active client instead of a specific client, you can use <var>JRoute::_()</var>
	 *
	 * @param   string   $client  The client name for which to build the link.
	 * @param   string   $url     Absolute or Relative URI to Joomla resource.
	 * @param   boolean  $xhtml   Replace & by &amp; for XML compliance.
	 * @param   integer  $ssl     Secure state for the resolved URI.
	 *                              0: (default) No change, use the protocol currently used in the request
	 *                              1: Make URI secure using global secure site URI.
	 *                              2: Make URI unsecure using the global unsecure site URI.
	 *
	 * @return  string  The translated humanly readable URL.
	 *
	 * @throws  \RuntimeException
	 *
	 * @since   3.9.0
	 */
	public static function link($client, $url, $xhtml = true, $ssl = null)
	{
		// If we cannot process this $url exit early.
		if (!is_array($url) && (strpos($url, '&') !== 0) && (strpos($url, 'index.php') !== 0))
		{
			return $url;
		}

		// Get the router instance, only attempt when a client name is given.
		if ($client && !isset(self::$_router[$client]))
		{
			$app = Factory::getApplication();

			self::$_router[$client] = $app->getRouter($client);
		}

		// Make sure that we have our router
		if (!isset(self::$_router[$client]))
		{
			throw new \RuntimeException(\JText::sprintf('JLIB_APPLICATION_ERROR_ROUTER_LOAD', $client), 500);
		}

		// Build route.
		$uri    = self::$_router[$client]->build($url);
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
				$uri2      = Uri::getInstance();
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
