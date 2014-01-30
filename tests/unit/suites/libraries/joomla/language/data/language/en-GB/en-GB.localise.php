<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

/**
 * en-GB localise class
 *
 * @package  Joomla.Language
 * @since    1.6
 */
abstract class En_GBLocalise
{
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param   integer  $count  The number of items.
	 *
	 * @since    1.6
	 * @return    array  An array of potential suffixes.
	 */
	public static function getPluralSuffixes($count)
	{
		if ($count == 0)
		{
			$return = array('0');
		}
		elseif ($count == 1)
		{
			$return = array('1');
		}
		else
		{
			$return = array('MORE');
		}
		return $return;
	}

	/**
	 * Returns the ignored search words
	 *
	 * @since    1.6
	 * @return    array  An array of ignored search words.
	 */
	public static function getIgnoredSearchWords()
	{
		$search_ignore = array();
		$search_ignore[] = "and";
		$search_ignore[] = "in";
		$search_ignore[] = "on";

		return $search_ignore;
	}

	/**
	 * Returns the lower length limit of search words
	 *
	 * @since    1.6
	 * @return    integer  The lower length limit of search words.
	 */
	public static function getLowerLimitSearchWord()
	{
		return 3;
	}

	/**
	 * Returns the upper length limit of search words
	 *
	 * @since    1.6
	 * @return    integer  The upper length limit of search words.
	 */
	public static function getUpperLimitSearchWord()
	{
		return 20;
	}

	/**
	 * Returns the number of chars to display when searching
	 *
	 * @since    1.6
	 * @return    integer  The number of chars to display when searching.
	 */
	public static function getSearchDisplayedCharactersNumber()
	{
		return 200;
	}
}
