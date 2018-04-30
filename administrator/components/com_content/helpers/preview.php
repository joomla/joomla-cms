<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_content
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Preview Link Helper
 *
 * @since  4.0.0
 */
class ContentHelperPreview
{
	/**
	 * Get the article URL
	 *
	 * @param   object  $article  The article item object
	 *
	 * @return  string  The article URL
	 *
	 * @since   4.0
	 */
	public static function url($article)
	{
		$lang = '';
		$sef  = '';

		// Get the home Itemid for the language
		$db    = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__menu'))
			->where($db->quoteName('home') . '= 1')
			->where($db->quoteName('published') . '= 1')
			->where($db->quoteName('client_id') . '= 0');

		if (JLanguageMultilang::isEnabled())
		{
			$query->where($db->quoteName('language') . ' = ' . $db->quote($article->language));
		}
		else
		{
			$query->where($db->quoteName('language') . ' = ' . $db->quote('*'));
		}

		$db->setQuery($query);

		$Itemid = '&amp;Itemid=' . (int) $db->loadResult();

		if ($article->language && JLanguageMultilang::isEnabled())
		{
			// Get the sef prefix for the language
			$query->clear()
				->select('sef')
				->from($db->quoteName('#__languages'))
				->where($db->quoteName('published') . '= 1')
				->where($db->quoteName('lang_code') . ' = ' . $db->quote($article->language));
			$db->setQuery($query);

			$sef = $db->loadResult();

			if ($article->language != '*')
			{
				$lang = '&amp;lang=' . $sef;
			}
		}

		return JUri::root() . 'index.php?option=com_content&amp;view=article&amp;id=' . (int) $article->id
		. '&amp;catid=' . (int) $article->catid . $lang . $Itemid;
	}
}
