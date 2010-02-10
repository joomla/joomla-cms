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
abstract class modLanguagesHelper
{
	public static function &getTag(&$params)
	{
		$tag = JFactory::getLanguage()->getTag();
		return $tag;
	}
	public static function &getList(&$params)
	{
		$selected = self::getTag($params);
		$result = JLanguageHelper::createLanguageList($selected, JPATH_SITE, true);
		return $result;
	}
}
