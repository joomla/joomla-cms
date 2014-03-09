<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Content Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_content
 * @since       1.5
 * @deprecated  4.0
 */
abstract class ContentHelperRoute
{
	/**
	 * @param   integer  The route of the content item
	 */
	public static function getArticleRoute($id, $catid = 0, $language = 0)
	{
		$link = 'index.php?option=com_content&view=article&id='. $id;
		if ((int) $catid > 1)
		{
			$link .= '&catid='.$catid;
		}
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$link .= '&language=' . $language;
		}

		return $link;
	}

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

		$link = 'index.php?option=com_content&view=category&id='.$id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$link .= '&language=' . $language;
		}

		return $link;
	}

	public static function getFormRoute($id)
	{
		//Create the link
		if ($id)
		{
			$link = 'index.php?option=com_content&task=article.edit&a_id='. $id;
		}
		else
		{
			$link = 'index.php?option=com_content&task=article.edit&a_id=0';
		}

		return $link;
	}
}
