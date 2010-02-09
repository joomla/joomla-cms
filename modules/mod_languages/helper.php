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
class modLanguagesHelper
{
	function &getSelected()
	{
		$user = JFactory::getUser();
		if (empty($user->id))
		{
			jimport('joomla.utilities.utility');
			$selected = JRequest::getString(JUtility::getHash('com_languages.tag'), null ,'cookie');
			if (empty($selected))
			{
				$config = &JFactory::getConfig();
				$selected = $config->getValue('config.language', 'en-GB');
			}
		}
		else
		{
			$selected = $user->getParam('language','en-GB');
		}
		return $selected;
	}
	function &getList(&$params)
	{
		$selected = self::getSelected($params);
		$result = JLanguageHelper::createLanguageList($selected , JPATH_SITE, true);
		return $result;
	}
}
