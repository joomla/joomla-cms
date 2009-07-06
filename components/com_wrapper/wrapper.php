<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	Wrapper
 * @copyright	Copyright (C) 2005 - 2009 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

/*
 * This is our main control structure for the component
 *
 * Each view is determined by the $task variable
 */
switch (JRequest::getCmd('task'))
{
	default:
		WrapperController::display();
		break;
}

/**
 * Static class to hold controller functions for the Wrapper component
 *
 * @static
 * @package		Joomla.Site
 * @subpackage	Wrapper
 * @since		1.5
 */
class WrapperController
{
	function display()
	{
		$app	= &JFactory::getApplication();
		$document = &JFactory::getDocument();

		$menus	= &JSite::getMenu();
		$menu	= $menus->getActive();

		// Get the page/component configuration
		$params = &$app->getParams();

		//set page title
		$document->setTitle($menu->name);

		$url = $params->def('url', '');

		$row = new stdClass();
		if ($params->def('add_scheme', 1))
		{
			// adds 'http://' if none is set
			if (substr($url, 0, 1) == '/')
			{
				// relative url in component. use server http_host.
				$row->url = 'http://'. $_SERVER['HTTP_HOST'] . $url;
			}
			elseif (!strstr($url, 'http') && !strstr($url, 'https')) {
				$row->url = 'http://'. $url;
			}
			else {
				$row->url = $url;
			}
		}
		else {
			$row->url = $url;
		}

		require_once (JPATH_COMPONENT.DS.'views'.DS.'wrapper'.DS.'view.php');
		$view = new WrapperViewWrapper();

		$view->assignRef('params'  , $params);
		$view->assignRef('wrapper' , $row);

		$view->display();
	}
}
?>