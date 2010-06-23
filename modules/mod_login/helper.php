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
			$app	= JFactory::getApplication();
			$menu	= $app->getMenu();
			$item	= $menu->getItem($itemid);
			if ($item) {
				$url = JRoute::_($item->link.'&Itemid='.$itemid, false);
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