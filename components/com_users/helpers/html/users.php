<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_users
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Users Html Helper
 *
 * @package		Joomla.Site
 * @subpackage	com_users
 * @since		1.6
 */

abstract class JHtmlUsers
{
	public static function value($value)
	{
		if (is_string($value)) {
			$value = trim($value);
		}
		if (empty($value)) {
			return JText::_('COM_USERS_PROFILE_VALUE_NOT_FOUND');
		}
		else {
			return htmlspecialchars($value);
		}
	}
	public static function spacer($value)
	{
		return '';
	}

	public static function helpsite($value)
	{
		if (empty($value))
		{
			return self::value($value);
		}
		else
		{
			$version = new JVersion();
			$jver = explode( '.', $version->getShortVersion() );

			$pathToXml = JPATH_ADMINISTRATOR.'/help/helpsites.xml';

			$text = $value;
			if (!empty($pathToXml) && $xml = JFactory::getXML($pathToXml))
			{
				foreach ($xml->sites->site as $site)
				{
					if ((string)$site->attributes()->url == $value)
					{
						$text = (string)$site;
						break;
					}
				}
			}

			$value = htmlspecialchars($value);
			if (substr ($value, 0, 4) == "http") {
				return '<a href="'.$value.'">'.$text.'</a>';
			}
			else {
				return '<a href="http://'.$value.'">'.$text.'</a>';
			}
		}
	}

	public static function templatestyle($value)
	{
		if (empty($value))
		{
			return self::value($value);
		}
		else
		{
			$db = JFactory::getDbo();
			$query = $db->getQuery(true);
			$query->select('title');
			$query->from('#__template_styles');
			$query->where('id = '.$db->quote($value));
			$db->setQuery($query);
			$title = $db->loadResult();
			if ($title) {
				return htmlspecialchars($title);
			}
			else {
				return self::value('');
			}
		}
	}

	public static function admin_language($value)
	{
		if (empty($value))
		{
			return self::value($value);
		}
		else
		{
			$path = JLanguage::getLanguagePath(JPATH_ADMINISTRATOR, $value);
			$file = "$value.xml";

			$result = null;
			if (is_file("$path/$file")) {
				$result = JLanguage::parseXMLLanguageFile("$path/$file");
			}

			if ($result) {
				return htmlspecialchars($result['name']);
			}
			else {
				return self::value('');
			}
		}
	}

	public static function language($value)
	{
		if (empty($value))
		{
			return self::value($value);
		}
		else
		{
			$path = JLanguage::getLanguagePath(JPATH_SITE, $value);
			$file = "$value.xml";

			$result = null;
			if (is_file("$path/$file")) {
				$result = JLanguage::parseXMLLanguageFile("$path/$file");
			}

			if ($result) {
				return htmlspecialchars($result['name']);
			}
			else {
				return self::value('');
			}
		}
	}

	public static function editor($value)
	{
		if (empty($value))
		{
			return self::value($value);
		}
		else
		{
			$db = JFactory::getDbo();
			$lang = JFactory::getLanguage();
			$query = $db->getQuery(true);
			$query->select('name');
			$query->from('#__extensions');
			$query->where('element = '.$db->quote($value));
			$query->where('folder = '.$db->quote('editors'));
			$db->setQuery($query);
			$title = $db->loadResult();
			if ($title)
			{
					$lang->load("plg_editors_$value.sys", JPATH_ADMINISTRATOR, null, false, false)
				||	$lang->load("plg_editors_$value.sys", JPATH_PLUGINS . '/editors/' . $value, null, false, false)
				||	$lang->load("plg_editors_$value.sys", JPATH_ADMINISTRATOR, $lang->getDefault(), false, false)
				||	$lang->load("plg_editors_$value.sys", JPATH_PLUGINS . '/editors/' . $value, $lang->getDefault(), false, false);
				$lang->load($title.'.sys');
				return JText::_($title);
			}
			else
			{
				return self::value('');
			}
		}
	}

}

