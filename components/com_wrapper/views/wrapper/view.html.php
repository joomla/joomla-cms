<?php
/**
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 * @copyright	Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package		Joomla.Site
 * @subpackage	com_wrapper
 */
class WrapperViewWrapper extends JViewLegacy
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
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1) {
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2) {
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}
		$this->document->setTitle($title);

		if ($params->get('menu-meta_description'))
		{
			$this->document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$this->document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetadata('robots', $params->get('robots'));
		}

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
