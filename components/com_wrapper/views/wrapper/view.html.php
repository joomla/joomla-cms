<?php
/**
 * @package     Joomla.Site
 * @subpackage  com_wrapper
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

/**
 * Wrapper view class.
 * 
 * @since  1.5
 */
class WrapperViewWrapper extends JViewLegacy
{
	/**
	 * The page class suffix
	 *
	 * @var    string
	 * @since  __DEPLOY_VERSION__
	 */
	protected $pageclass_sfx = '';

	/**
	 * The page parameters
	 *
	 * @var    \Joomla\Registry\Registry|null
	 * @since  __DEPLOY_VERSION__
	 */
	protected $params = null;

	/**
	 * The page parameters
	 *
	 * @var    stdClass
	 * @since  __DEPLOY_VERSION__
	 */
	protected $wrapper = null;

	/**
	 * Execute and display a template script.
	 *
	 * @param   string  $tpl  The name of the template file to parse; automatically searches through the template paths.
	 *
	 * @return  mixed  A string if successful, otherwise an Error object.
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
			$this->document->setMetaData('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$this->document->setMetaData('robots', $params->get('robots'));
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
			// Adds 'http://' or 'https://' if none is set
			if (substr($url, 0, 2) == '//')
			{
				// URL without scheme in component. Prepend current scheme.
				$wrapper->url = JUri::getInstance()->toString(array('scheme')) . substr($url, 2);
			}
			elseif (substr($url, 0, 1) == '/')
			{
				// Relative URL in component. Use scheme + host + port.
				$wrapper->url = JUri::getInstance()->toString(array('scheme', 'host', 'port')) . $url;
			}
			elseif (strpos($url, 'http://') !== 0 && strpos($url, 'https://') !== 0)
			{
				// URL doesn't start with either 'http://' or 'https://'. Add current scheme.
				$wrapper->url = JUri::getInstance()->toString(array('scheme')) . $url;
			}
			else
			{
				// URL starts with either 'http://' or 'https://'. Do not change it.
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
