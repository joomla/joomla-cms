<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_mailto
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Mailto route helper class.
 *
 * @package     Joomla.Site
 * @subpackage  com_mailto
 * @since       1.6.1
 */
abstract class MailtoHelper
{
	/**
	 * Adds a URL to the mailto system and returns the hash
	 *
	 * @param   string  $url  Url
	 *
	 * @return  string  URL hash
	 */
	public static function addLink($url)
	{
		$hash = sha1($url);
		self::cleanHashes();

		$session      = JFactory::getSession();
		$mailto_links = $session->get('com_mailto.links', array());

		if (!isset($mailto_links[$hash]))
		{
			$mailto_links[$hash] = new stdClass;
		}

		$mailto_links[$hash]->link   = $url;
		$mailto_links[$hash]->expiry = time();
		$session->set('com_mailto.links', $mailto_links);

		return $hash;
	}

	/**
	 * Checks if a URL is a Flash file
	 *
	 * @param   string  $hash  File hash
	 *
	 * @return URL
	 */
	public static function validateHash($hash)
	{
		$retval  = false;
		$session = JFactory::getSession();

		self::cleanHashes();
		$mailto_links = $session->get('com_mailto.links', array());

		if (isset($mailto_links[$hash]))
		{
			$retval = $mailto_links[$hash]->link;
		}

		return $retval;
	}

	/**
	 * Cleans out old hashes
	 *
	 * @param   integer  $lifetime  How old are the hashes we want to remove
	 *
	 * @return  void
	 *
	 * @since 1.6.1
	 */
	public static function cleanHashes($lifetime = 1440)
	{
		// Flag for if we've cleaned on this cycle
		static $cleaned = false;

		if (!$cleaned)
		{
			$past         = time() - $lifetime;
			$session      = JFactory::getSession();
			$mailto_links = $session->get('com_mailto.links', array());

			foreach ($mailto_links as $index => $link)
			{
				if ($link->expiry < $past)
				{
					unset($mailto_links[$index]);
				}
			}

			$session->set('com_mailto.links', $mailto_links);
			$cleaned = true;
		}
	}
}
