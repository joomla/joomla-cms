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
	 * @var  array  $suffixes
	 * @since  __DEPLOY_VERSION__
	 */
	protected static $suffixes = array('0', '1', 'MORE');

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
		if ($count === 0)
		{
			return array('0');
		}
		elseif ($count === 1)
		{
			return array('1');
		}
		else
		{
			return array('MORE');
		}
	}


	/**
	 * Returns an array with two attributes
	 * - suffixes  :  an array of possible suffixes for this language
	 * - pluralizer:  a string that holds the code that will be inserted into the javascript pluralize(count) function
	 *                This code will need to operate with the `count` argument and return a pluralized string
	 *
	 *                The creation is pretty easy when the PHP counterpart exists
	 *
	 *                Example:
	 *
	 *                <code>
	 *                    if (count === 0) {
	 *                      return '0';
	 *                    }
	 *                    else if (count === 1) {
	 *                      return '1';
	 *                    }
	 *                    else {
	 *                      return 'MORE';
	 *                    }
	 *                </code>
	 *
	 *                Example for more complicated code (Here Russian).
	 *                <code>
	 *                    var ret;
	 *
	 *                    if (count === 0) {
	 *                        ret = ['0'];
	 *                    } else {
	 *                        ret = [(count%10==1 && count%100!=11 ? '1' : (count%10>=2 && count%10<=4 && (count%100<10 || count%100>=20) ? '2' : 'MORE'))];
	 *                    }
	 *                    return ret;
	 *                </code>
	 *
	 * @return  array  An array of data for use by the pluralJS() function
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public static function getJSPluralizer()
	{
		$pluralData['suffixes'] = array('0', '1', 'MORE');

		$pluralData['pluralize'] = /** @lang JavaScript */
			<<<'JS'
	if (count === 0) {
		return '0';
	}
	else if (count === 1) {
		return '1';
	}
	else {
		return 'MORE';
	}
JS;

		return $pluralData;
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
