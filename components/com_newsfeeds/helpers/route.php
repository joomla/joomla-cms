<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_newsfeeds
 *
 * @copyright   (C) 2009 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Newsfeeds Component Route Helper
 *
 * @since  1.5
 */
abstract class NewsfeedsHelperRoute
{
	/**
	 * getNewsfeedRoute
	 *
	 * @param   int  $id        menu itemid
	 * @param   int  $catid     category id
	 * @param   int  $language  language
	 *
	 * @return string
	 */
	public static function getNewsfeedRoute($id, $catid, $language = 0)
	{
		// Create the link
		$link = 'index.php?option=com_newsfeeds&view=newsfeed&id=' . $id;

		if ((int) $catid > 1)
		{
			$link .= '&catid=' . $catid;
		}

		if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
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
	 */
	public static function getCategoryRoute($catid, $language = 0)
	{
		if ($catid instanceof JCategoryNode)
		{
			$id = $catid->id;
		}
		else
		{
			$id = (int) $catid;
		}

		if ($id < 1)
		{
			$link = '';
		}
		else
		{
			// Create the link
			$link = 'index.php?option=com_newsfeeds&view=category&id=' . $id;

			if ($language && $language !== '*' && JLanguageMultilang::isEnabled())
			{
				$link .= '&lang=' . $language;
			}
		}

		return $link;
	}
}
