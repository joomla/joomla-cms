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
			return $value;
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

			$pathToXml = JPATH_ADMINISTRATOR.'/help/helpsites-'.$jver[0].$jver[1].'.xml';

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
				;
			}

			if(substr ($value, 0, 4) == "http") {
				return '<a href="'.$value.'">'.$text.'</a>';
			}
			else {
				echo '<a href="http://'.$value.'">'.$text.'</a>';
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
				return $title;
			}
			else {
				return self::value('');
			}
		}
	}
}

