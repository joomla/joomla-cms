<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Contact Component Route Helper
 *
 * @static
 * @package     Joomla.Site
 * @subpackage  com_contact
 * @since       1.5
 * @deprecated  4.0
 */
abstract class ContactHelperRoute
{
	/**
	 * @param   integer  The route of the contact
	 */
	public static function getContactRoute($id, $catid, $language = 0)
	{
		//Create the link
		$link = 'index.php?option=com_contact&view=contact&id='. $id;
		if ($catid > 1)
		{
			$link .= '&catid='.$catid;
		}
		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang='.$language;
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

		// Create the link
		$link = 'index.php?option=com_contact&view=category&id=' . $id;

		if ($language && $language != "*" && JLanguageMultilang::isEnabled())
		{
			$link .= '&lang=' . $language;
		}

		return $link;
	}
}
