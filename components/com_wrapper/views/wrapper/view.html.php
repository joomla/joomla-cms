<?php
/**
 * @version		$Id$
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.view');

/**
 * @package		Joomla.Site
 * @subpackage	Wrapper
 */
class WrapperViewWrapper extends JView
{
	public function display($tpl = null)
	{
		$app		= JFactory::getApplication();
		$document	= JFactory::getDocument();

		$menus	= $app->getMenu();
		$menu	= $menus->getActive();

		$params = $app->getParams();

		// because the application sets a default page title, we need to get it
		// right from the menu item itself
		$title = $params->get('page_title', '');
		if (empty($title)) {
			$title = $app->getCfg('sitename');
		}
		elseif ($app->getCfg('sitename_pagetitles', 0)) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		$this->document->setTitle($title);

		$wrapper = new stdClass();
		// auto height control
		if ($params->def('height_auto')) {
			$wrapper->load = 'onload="iFrameHeight()"';
		} else {
			$wrapper->load = '';
		}

		$url = $params->def('url', '');
		if ($params->def('add_scheme', 1))
		{
			// adds 'http://' if none is set
			if (substr($url, 0, 1) == '/')
			{
				// relative url in component. use server http_host.
				$wrapper->url = 'http://'. $_SERVER['HTTP_HOST'] . $url;
			}
			elseif (!strstr($url, 'http') && !strstr($url, 'https')) {
				$wrapper->url = 'http://'. $url;
			}
			else {
				$wrapper->url = $url;
			}
		}
		else {
			$wrapper->url = $url;
		}

		//Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));

		$this->assignRef('params',	$params);
		$this->assignRef('wrapper', $wrapper);

		parent::display($tpl);
	}
}
