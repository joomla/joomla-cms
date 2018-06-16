<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * en-GB localise class.
 *
 * @since  1.6
 */
abstract class En_GBLocalise
{
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param   integer  $count  The number of items.
	 *
	 * @return  array  An array of potential suffixes.
	 *
	 * @since   1.6
	 */
	public static function getPluralSuffixes($count)
	{
		/*
		 * The language handler will attempt to suffix a language string with the item count before
		 * applying suffixes given by this handler, therefore we only need to return extra suffixes
		 * to be attempted if a string for the item count does not exist.
		 */
		if ($count == 0 || $count >= 2)
		{
			return array('MORE');
		}

		return array();
	}

	/**
	 * Returns the ignored search words
	 *
	 * @return  array  An array of ignored search words.
	 *
	 * @since   1.6
	 */
	public static function getIgnoredSearchWords()
	{
		return array('and', 'in', 'on');
	}

	/**
	 * Returns the lower length limit of search words
	 *
	 * @return  integer  The lower length limit of search words.
	 *
	 * @since   1.6
	 */
	public static function getLowerLimitSearchWord()
	{
		return 3;
	}

	/**
	 * Returns the upper length limit of search words
	 *
	 * @return  integer  The upper length limit of search words.
	 *
	 * @since   1.6
	 */
	public static function getUpperLimitSearchWord()
	{
		return 20;
	}

	/**
	 * Returns the number of chars to display when searching
	 *
	 * @return  integer  The number of chars to display when searching.
	 *
	 * @since   1.6
	 */
	public static function getSearchDisplayedCharactersNumber()
	{
		return 200;
	}
}
