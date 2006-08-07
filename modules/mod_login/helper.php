<?php
/**
* @version $Id$
* @package Joomla
* @copyright Copyright (C) 2005 - 2006 Open Source Matters. All rights reserved.
* @license GNU/GPL, see LICENSE.php
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
	function getReturnURL()
	{
		// url of current page that user will be returned to after login
		$url = JArrayHelper::getValue($_SERVER, 'REQUEST_URI', null);

		// if return link does not contain https:// & http:// and to url
		if (strpos($url, 'http:') !== 0 && strpos($url, 'https:') !== 0)
		{
			$url = JArrayHelper::getValue($_SERVER, 'HTTP_HOST', null).$url;

			// check if link is https://
			if (isset ($_SERVER['HTTPS']) && (!empty ($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off')) {
				$return = 'https://'.$url;
			}
			else
			{
				// normal http:// link
				$return = 'http://'.$url;
			}
		}
		else
		{
			$return = $url;
		}

		// converts & to &amp; for xtml compliance
		$return = str_replace('&', '&amp;', $return);
	}

	function getType()
	{
		$user = & JFactory::getUser();
	    return ($user->get('id')) ? 'logout' : 'login';
	}
}