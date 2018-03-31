<?php
/**
 * Joomla! Content Management System
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\String;

defined('JPATH_PLATFORM') or die;

use Joomla\Uri\UriHelper;

\JLoader::register('idna_convert', JPATH_LIBRARIES . '/idna_convert/idna_convert.class.php');

/**
 * Joomla Platform String Punycode Class
 *
 * Class for handling UTF-8 URLs
 * Wraps the Punycode library
 * All functions assume the validity of utf-8 URLs.
 *
 * @since  3.1.2
 */
abstract class PunycodeHelper
{
	/**
	 * Transforms a UTF-8 string to a Punycode string
	 *
	 * @param   string  $utfString  The UTF-8 string to transform
	 *
	 * @return  string  The punycode string
	 *
	 * @since   3.1.2
	 */
	public static function toPunycode($utfString)
	{
		$idn = new \idna_convert;

		return $idn->encode($utfString);
	}

	/**
	 * Transforms a Punycode string to a UTF-8 string
	 *
	 * @param   string  $punycodeString  The Punycode string to transform
	 *
	 * @return  string  The UF-8 URL
	 *
	 * @since   3.1.2
	 */
	public static function fromPunycode($punycodeString)
	{
		$idn = new \idna_convert;

		return $idn->decode($punycodeString);
	}

	/**
	 * Transforms a UTF-8 URL to a Punycode URL
	 *
	 * @param   string  $uri  The UTF-8 URL to transform
	 *
	 * @return  string  The punycode URL
	 *
	 * @since   3.1.2
	 */
	public static function urlToPunycode($uri)
	{
		$parsed = UriHelper::parse_url($uri);

		if (!isset($parsed['host']) || $parsed['host'] == '')
		{
			// If there is no host we do not need to convert it.
			return $uri;
		}

		$host = $parsed['host'];
		$hostExploded = explode('.', $host);
		$newhost = '';

		foreach ($hostExploded as $hostex)
		{
			$hostex = static::toPunycode($hostex);
			$newhost .= $hostex . '.';
		}

		$newhost = substr($newhost, 0, -1);
		$newuri = '';

		if (!empty($parsed['scheme']))
		{
			// Assume :// is required although it is not always.
			$newuri .= $parsed['scheme'] . '://';
		}

		if (!empty($newhost))
		{
			$newuri .= $newhost;
		}

		if (!empty($parsed['port']))
		{
			$newuri .= ':' . $parsed['port'];
		}

		if (!empty($parsed['path']))
		{
			$newuri .= $parsed['path'];
		}

		if (!empty($parsed['query']))
		{
			$newuri .= '?' . $parsed['query'];
		}

		if (!empty($parsed['fragment']))
		{
			$newuri .= '#' . $parsed['fragment'];
		}

		return $newuri;
	}

	/**
	 * Transforms a Punycode URL to a UTF-8 URL
	 *
	 * @param   string  $uri  The Punycode URL to transform
	 *
	 * @return  string  The UTF-8 URL
	 *
	 * @since   3.1.2
	 */
	public static function urlToUTF8($uri)
	{
		if (empty($uri))
		{
			return;
		}

		$parsed = UriHelper::parse_url($uri);

		if (!isset($parsed['host']) || $parsed['host'] == '')
		{
			// If there is no host we do not need to convert it.
			return $uri;
		}

		$host = $parsed['host'];
		$hostExploded = explode('.', $host);
		$newhost = '';

		foreach ($hostExploded as $hostex)
		{
			$hostex = self::fromPunycode($hostex);
			$newhost .= $hostex . '.';
		}

		$newhost = substr($newhost, 0, -1);
		$newuri = '';

		if (!empty($parsed['scheme']))
		{
			// Assume :// is required although it is not always.
			$newuri .= $parsed['scheme'] . '://';
		}

		if (!empty($newhost))
		{
			$newuri .= $newhost;
		}

		if (!empty($parsed['port']))
		{
			$newuri .= ':' . $parsed['port'];
		}

		if (!empty($parsed['path']))
		{
			$newuri .= $parsed['path'];
		}

		if (!empty($parsed['query']))
		{
			$newuri .= '?' . $parsed['query'];
		}

		if (!empty($parsed['fragment']))
		{
			$newuri .= '#' . $parsed['fragment'];
		}

		return $newuri;
	}

	/**
	 * Transforms a UTF-8 email to a Punycode email
	 * This assumes a valid email address
	 *
	 * @param   string  $email  The UTF-8 email to transform
	 *
	 * @return  string  The punycode email
	 *
	 * @since   3.1.2
	 */
	public static function emailToPunycode($email)
	{
		$explodedAddress = explode('@', $email);

		// Not addressing UTF-8 user names
		$newEmail = $explodedAddress[0];

		if (!empty($explodedAddress[1]))
		{
			$domainExploded = explode('.', $explodedAddress[1]);
			$newdomain = '';

			foreach ($domainExploded as $domainex)
			{
				$domainex = static::toPunycode($domainex);
				$newdomain .= $domainex . '.';
			}

			$newdomain = substr($newdomain, 0, -1);
			$newEmail = $newEmail . '@' . $newdomain;
		}

		return $newEmail;
	}

	/**
	 * Transforms a Punycode email to a UTF-8 email
	 * This assumes a valid email address
	 *
	 * @param   string  $email  The punycode email to transform
	 *
	 * @return  string  The punycode email
	 *
	 * @since   3.1.2
	 */
	public static function emailToUTF8($email)
	{
		$explodedAddress = explode('@', $email);

		// Not addressing UTF-8 user names
		$newEmail = $explodedAddress[0];

		if (!empty($explodedAddress[1]))
		{
			$domainExploded = explode('.', $explodedAddress[1]);
			$newdomain = '';

			foreach ($domainExploded as $domainex)
			{
				$domainex = static::fromPunycode($domainex);
				$newdomain .= $domainex . '.';
			}

			$newdomain = substr($newdomain, 0, -1);
			$newEmail = $newEmail . '@' . $newdomain;
		}

		return $newEmail;
	}
}
