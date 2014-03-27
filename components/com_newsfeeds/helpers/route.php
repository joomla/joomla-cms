<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds Component Route Helper
 *
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 * @since       1.5
 * @deprecated  4.0
 */
abstract class NewsfeedsHelperRoute
{
	protected static $lang_lookup = array();

	/**
	 * @param   integer  The route of the newsfeed
	 */
	public static function getNewsfeedRoute($id, $catid, $language = 0)
	{
		//Create the link
		$link = 'index.php?option=com_newsfeeds&view=newsfeed&id='. $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid='.$catid;
		}

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			self::buildLanguageLookup();

			if (isset(self::$lang_lookup[$language]))
			{
				$link .= '&lang=' . self::$lang_lookup[$language];
				$link .= '&language=' . $language;
			}
		}

		return $link;
	}

	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
			$category = $catid;
		}
		else
		{
			$id = (int) $catid;
			$category = JCategories::getInstance('Newsfeeds')->get($id);
		}

		if ($id < 1 || !($category instanceof JCategoryNode))
		{
			$link = '';
		}
		else
		{
			// Create the link
			$link = 'index.php?option=com_newsfeeds&view=category&id='.$id;

			if ($language && $language != "*" && JLanguageMultilang::isEnabled())
			{
				self::buildLanguageLookup();

				if (isset(self::$lang_lookup[$language]))
				{
					$link .= '&lang=' . self::$lang_lookup[$language];
					$link .= '&language=' . $language;
				}
			}
		}

		return $link;
	}

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
}
