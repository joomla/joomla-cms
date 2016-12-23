<?php
/**
 * @package    Joomla.Language
 *
 * @copyright  Copyright (C) 2005 - 2016 Open Source Matters, Inc. All rights reserved.
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
	 *
	 * @deprecated 4.0
	 */
	public static function getPluralSuffixes($count)
	{
		$suffixes = self::getAllPluralSuffixes();

		foreach ($suffixes as $suffix)
		{
			if (version_compare($count, $suffix[1], $suffix[0]))
			{
				return array($suffix[2]);
			}
		}
		return array();
	}


	/**
	 * Returns an array of suffixes for plural rules.
	 * This array holds the suffix information and their prerequisites
	 * The array is the used by the getPluralSuffixes for PHP and JS
	 *
	 * @return  array  An array of suffixes for use by the getPluralSuffixes function
	 * for either PHP or JS.
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getAllPluralSuffixes()
	{
		/*
		 * The array of suffixes-data ([0]=>comparison operator, [1]=>comparison value and [2]=>the suffix itself)
		 *
		 * - the first key [0] is the comparison operator (or anything one wants, as long
		 * as the PHP and JS suffix generators can decode it.)
		 *
		 * - the second key [1] is the number that the count is compared to
		 *
		 * - the third key [2] is the suffix itself
		 *
		 *
		 * The allowed comparison operators for our case we only allow symbol operators: <, <=, >, >=, ==, =, !=, <> respectively.
		 * That is, so we don't need to write additional comparison functions in javascript, and because frankly,
		 * it works without them.
		 *
		 */

		return array(
			array('=', 0, '0'),
			array('=', 1, '1'),
			array('>', 1, 'MORE'),
		);
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
