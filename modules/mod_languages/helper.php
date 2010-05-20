<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_languages
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

jimport('joomla.language.helper');
jimport('joomla.utilities.utility');

abstract class modLanguagesHelper
{
	public static function getList(&$params)
	{
		$languages	= JLanguageHelper::getLanguages();
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		$query->select('id');
		$query->select('language');
		$query->from($db->nameQuote('#__menu'));
		$query->where('home=1');
		$db->setQuery($query);
		$homes = $db->loadObjectList('language');

		foreach($languages as $i => &$language) {
			if (!JLanguage::exists($language->lang_code)) {
				unset($languages[$i]);
			}
			else {
				$language->id = isset($homes[$language->lang_code]) ? $homes[$language->lang_code]->id : $homes['*']->id;
			}
		}
		return $languages;
	}
}
