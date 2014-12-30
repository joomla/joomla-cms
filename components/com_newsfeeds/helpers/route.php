<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds Component Route Helper
 *
 * @since  1.5
 * @deprecated 4.0 Simply write non-SEF URLs instead
 */
abstract class NewsfeedsHelperRoute
{
	protected static $lang_lookup = array();

	/**
	 * getNewsfeedRoute
	 *
	 * @param   int  $id        menu itemid
	 * @param   int  $catid     category id
	 * @param   int  $language  language
	 *
	 * @return string
	 * @deprecated 4.0 Simply write a static non-SEF URL instead
	 */
	public static function getNewsfeedRoute($id, $catid, $language = 0)
	{
		// Create the link
		$link = 'index.php?option=com_newsfeeds&view=newsfeed&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
			}
		}

		return $link;
	}

	/**
	 * getCategoryRoute
	 *
	 * @param   int  $catid     category id
	 * @param   int  $language  language
	 *
	 * @return string
	 * @deprecated 4.0 Simply write a static non-SEF URL instead
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$catid = $catid->id;
		}

		if ($catid < 1)
		{
			$link = '';
		}
		else
		{
			// Create the link
			$link = 'index.php?option=com_newsfeeds&view=category&id=' . $catid;

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
				}
			}
		}

		return $link;
	}

	/**
	 * buildLanguageLookup
	 *
	 * @return  void
	 *
	 * @throws Exception
	 */
	protected static function buildLanguageLookup()
	{
		if (count(self::$lang_lookup) == 0)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select('a.sef AS sef')
				->select('a.lang_code AS lang_code')
				->from('#__languages AS a');

			$db->setQuery($query);
			$langs = $db->loadObjectList();

			foreach ($langs as $lang)
			{
				self::$lang_lookup[$lang->lang_code] = $lang->sef;
			}
		}
	}

	/**
	 * finditem
	 *
	 * @param   null  $needles  what we are searching for
	 *
	 * @return  int  menu itemid
	 *
	 * @throws Exception
	 * @deprecated 4.0
	 */
	protected static function _findItem($needles = null)
	{
		return null;
	}
}
