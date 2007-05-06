<?php
/**
* @version		$Id$
* @package		Joomla
* @copyright	Copyright (C) 2005 - 2007 Open Source Matters. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* Joomla! is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/

// no direct access
defined('_JEXEC') or die('Restricted access');

class modLoginHelper
{
	function getReturnURL($params, $type)
	{
		// url of current page that user will be returned to after login
		$url =  $params->get($type);

		if($url == ''){
			$uri =& JURI::getInstance();
		} else {
			$uri =& JURI::getInstance($url);
		}

		return $uri->toString();;
	}

	function getType()
	{
		$user = & JFactory::getUser();
	    return (!$user->get('guest')) ? 'logout' : 'login';
	}
}