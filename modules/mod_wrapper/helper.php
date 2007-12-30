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

class modWrapperHelper
{
	function getParams(&$params)
	{
		$params->def('url', '');
		$params->def('scrolling', 'auto');
		$params->def('height', '200');
		$params->def('height_auto', '0');
		$params->def('width', '100%');
		$params->def('add', '1');
		$params->def('name', 'wrapper');

		$url = $params->get('url');

		if ($params->get('add'))
		{
			// adds 'http://' if none is set
			if (substr($url, 0, 1) == '/') {
				// relative url in component. use server http_host.
				$url = 'http://'.$_SERVER['HTTP_HOST'].$url;
			}
			elseif (!strstr($url, 'http') && !strstr($url, 'https')) {
				$url = 'http://'.$url;
			}
			else {
				$url = $url;
			}
		}

		// auto height control
		if ($params->def('height_auto')) {
			$load = 'onload="iFrameHeight()"';
		}
		else {
			$load = '';
		}

		$params->set( 'load', $load );
		$params->set( 'url', $url );

		return $params;
	}
}
