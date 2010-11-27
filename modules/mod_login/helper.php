<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	mod_login
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

class modLoginHelper
{
	static function getReturnURL($params, $type)
	{
		$url = null;
		if ($itemid =  $params->get($type))
		{
			$db		= JFactory::getDbo();
			$app	= JFactory::getApplication();
			$query	= $db->getQuery(true);

			$query->select($db->nameQuote('link'));
			$query->from($db->nameQuote('#__menu'));
			$query->where($db->nameQuote('published') . '=1');
			$query->where($db->nameQuote('id') . '=' . $db->quote($itemid));

			$db->setQuery($query);
			if ($link = $db->loadResult()) {
				$router = JSite::getRouter();
				if ($router->getMode() == JROUTER_MODE_SEF) {
					$url = JRoute::_('index.php?Itemid='.$itemid, false);
				}
				else {
					$url = JRoute::_($link.'&Itemid='.$itemid, false);
				}
			}
		}
		if (!$url)
		{
			// stay on the same page
			$uri = JFactory::getURI();
			$url = $uri->toString(array('path', 'query', 'fragment'));
		}

		return base64_encode($url);
	}
	
	static function getType()
	{
		$user = JFactory::getUser();
		return (!$user->get('guest')) ? 'logout' : 'login';
	}
}
