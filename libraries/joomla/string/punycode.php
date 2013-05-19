<?php
/**
 * @package     Joomla.Platform
 * @subpackage  String
 *
 * @copyright   Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('JPATH_PLATFORM') or die;

jimport('punycode.punycode');
/**
 * Joomla Platform String Punycode Class
 *
 * Class for handling UTF-8 URLs
 * Wraps the Punycode library
 * All functions assume the validity of utf-8 URLs.

 * @package     Joomla.Platform
 * @subpackage  String
 * @since       3.1.2
 */
abstract class JStringPunycode
{
	/*
	 * Transforms a UTF-8 string to a Punycode string
	 *
	 * @param  string  $url  The UTF-8 string to transform
	 *
	 * @return  string  The punycode string
	 *
	 * @since  3.1.2
	 */
	public static function toPunycode($url)
	{
		return punycode::encode($url);
	}

	/*
	 * Transforms a Punycode URL to a UTF-8 URL
	*
	* @param  string  $url  The Punycode URL to transform
	*
	* @return  string  The UF-8 URL
	*
	* @since  3.1.2
	*/
	public static function fromPunycode($url)
	{
		return punycode::decode($url);

	}

	/*
	 * Transforms a UTF-8 URL to a Punycode URL
	*
	* @param  string  $url  The UTF-8 URL to transform
	*
	* @return  string  The punycode URL
	*
	* @since  3.1.2
	*/
	public static function urlToPunycode($uri)
	{

		$parsed = JString::parse_url($uri);
		$host = $parsed['host'];
		$hostExploded = explode('.', $host);
		$newhost = '';
		foreach ($hostExploded as $hostex)
		{
			$hostex = JStringPunycode::toPunycode($hostex);
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

		if (!empty($parsed['path']))
		{
			$newuri .= $parsed['path'] ;
		}
		if (!empty($parsed['query']))
		{
			$newuri .= '?' . $parsed['query'];
		}

		return $newuri;
	}

	/*
	 * Transforms a Punycode URL to a UTF-8 URL
	*
	* @param  string  $url  The Punycode URL to transform
	*
	* @return  string  The UTF-8 URL
	*
	* @since  3.1.2
	*/
	public static function urlToUTF8($uri)
	{
		$parsed = JString::parse_url($uri);
		$host = $parsed['host'];
		$hostExploded = explode('.', $host);
		$newhost = '';
		foreach ($hostExploded as $hostex)
		{
			$hostex = JStringPunycode::fromPunycode($hostex);
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

		if (!empty($parsed['path']))
		{
			$newuri .= $parsed['path'] ;
		}
		if (!empty($parsed['query']))
		{
			$newuri .= '?' . $parsed['query'];
		}

		return $newuri;
	}
}
