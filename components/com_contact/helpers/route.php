<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_contact
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
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
 * @deprecated 4.0 Simply write non-SEF URLs instead
 */
abstract class ContactHelperRoute
{
	protected static $lang_lookup = array();

	/**
	 * Get the URL route for a contact from a contact ID, contact category ID and language
	 *
	 * @param   integer  $id        The id of the contact
	 * @param   integer  $catid     The id of the contact's category
	 * @param   mixed    $language  The id of the language being used.
	 *
	 * @return  string  The link to the contact
	 *
	 * @since   1.5
	 * @deprecated 4.0 Simply write a static non-SEF URL instead
	 */
	public static function getContactRoute($id, $catid, $language = 0)
	{
		//Create the link
		$link = 'index.php?option=com_contact&view=contact&id=' . $id;

		if ($catid > 1)
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
	 * Get the URL route for a contact category from a contact category ID and language
	 *
	 * @param   mixed    $catid     The id of the contact's category either an integer id or a instance of JCategoryNode
	 * @param   mixed    $language  The id of the language being used.
	 *
	 * @return  string  The link to the contact
	 *
	 * @since   1.5
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
			$link = 'index.php?option=com_contact&view=category&id=' . $id;

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
	 * Was used to calculate the right Itemid
	 * 
	 * @deprecated 4.0
	 */
	protected static function _findItem($needles = null)
	{
		return null;
	}
}
