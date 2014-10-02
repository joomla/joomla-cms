<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 * @since       1.5
 */
class WrapperViewWrapper extends JViewLegacy
{
	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise a Error object.
	 *
	 * @since   1.5
	 */
	public function display($tpl = null)
	{
		$app    = JFactory::getApplication();
		$params = $app->getParams();

		// Because the application sets a default page title, we need to get it
		// right from the menu item itself
		$title = $params->get('page_title', '');

		if (empty($title))
		{
			$title = $app->get('sitename');
		}
		elseif ($app->get('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
		}
		elseif ($app->get('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
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

		$wrapper = new stdClass;

		// Auto height control
		if ($params->def('height_auto'))
		{
			$wrapper->load = 'onload="iFrameHeight()"';
		}
		else
		{
			$wrapper->load = '';
		}

		$url = $params->def('url', '');

		if ($params->def('add_scheme', 1))
		{
			// Adds 'http://' if none is set
			if (substr($url, 0, 1) == '/')
			{
				// Relative url in component. Use server http_host.
				$wrapper->url = 'http://' . $_SERVER['HTTP_HOST'] . $url;
			}
			elseif (!strstr($url, 'http') && !strstr($url, 'https'))
			{
				$wrapper->url = 'http://' . $url;
			}
			else
			{
				$wrapper->url = $url;
			}
		}
		else
		{
			$wrapper->url = $url;
		}

		// Escape strings for HTML output
		$this->pageclass_sfx = htmlspecialchars($params->get('pageclass_sfx'));
		$this->params        = &$params;
		$this->wrapper       = &$wrapper;

		parent::display($tpl);
	}
}
