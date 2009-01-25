<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

abstract class modLoginHelper
{
	public static function getReturnURL($params, $type)
	{
		if($itemid =  $params->get($type))
		{
			$app = JFactory::getApplication();
			$menu =& $app->getMenu();
			$item = $menu->getItem($itemid);
			$url = $item->link;
		}
		else
		{
			// Redirect to login
			$uri = JFactory::getURI();
			$url = $uri->toString();
		}

		return base64_encode($url);
	}

	public static function getType()
	{
		$user = & JFactory::getUser();
		return (!$user->get('guest')) ? 'logout' : 'login';
	}
}
