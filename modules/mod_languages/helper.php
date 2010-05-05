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
	/**
	 * Get the language from
	 * - the cookie session if the user is not logged in or
	 * - the user preference
	 */
	public static function getTag(&$params)
	{
		$user = JFactory::getUser();
		$tag = JRequest::getString(JUtility::getHash('language'), null ,'cookie');
		if(empty($tag) && $user->id) {
			$tag = $user->getParam('language');
		}
		return $tag;
	}
	public static function getList(&$params)
	{
		$useDefault = $params->get('default');
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);;
		$query->from($db->nameQuote('#__languages'));
		$query->select($db->nameQuote('lang_code'));
		$query->select($db->nameQuote('title'));
		$query->select($db->nameQuote('image'));
		$query->select($db->nameQuote('sef'));
		$query->where($db->nameQuote('published').'=1');
		$db->setQuery($query);
		$result = $db->loadObjectList('lang_code');
		$query->clear();
		$query->from($db->nameQuote('#__menu'));
		$query->select('id');
		$query->select('language');
		$query->where('home=1');
		$db->setQuery($query);
		$home = $db->loadObjectList('language');
		foreach($result as $i=>&$language) {
			if (!JLanguage::exists($language->lang_code)) {
				unset($result[$i]);
			}
			else {
				$language->id = array_key_exists($language->lang_code, $home) ? $home[$language->lang_code]->id : $home['*']->id;
			}
		}
		if (false && $useDefault && count($result)) {
			$option = array();
			$option['text'] = JText::_('MOD_LANGUAGES_OPTION_DEFAULT_LANGUAGE');
			$option['value'] = 'default';
			$option['image'] = 'default';
			$config =& JFactory::getConfig();
			$paramsLanguagues =  JComponentHelper::getParams('com_languages');
			$option['redirect']=$result[$paramsLanguagues->get('site', $config->get('language','en-GB'))]['redirect'];
			array_unshift($result, $option);
		}
		return $result;
	}
}
