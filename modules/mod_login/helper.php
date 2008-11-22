<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
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
