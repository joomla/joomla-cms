<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010-2016 Nicholas K. Dionysopoulos / Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @note        This file has been modified by the Joomla! Project and no longer reflects the original work of its author.
 */

// Protect from unauthorized access
defined('FOF_INCLUDED') or die;

/**
 * FrameworkOnFramework HTML output class. Together with PHP-based view templates
 * it will render your data into an HTML representation.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFViewHtml extends FOFViewRaw
{
	/** @var bool Should I set the page title in the front-end of the site? */
	public $setFrontendPageTitle = false;

	/** @var string The translation key for the default page title */
	public $defaultPageTitle = null;

	/**
	 * Class constructor
	 *
	 * @param   array $config Configuration parameters
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array)$config;
		}
		elseif (!is_array($config))
		{
			$config = array();
		}

		if (isset($config['setFrontendPageTitle']))
		{
			$this->setFrontendPageTitle = (bool)$config['setFrontendPageTitle'];
		}

		if (isset($config['defaultPageTitle']))
		{
			$this->defaultPageTitle = $config['defaultPageTitle'];
		}

		parent::__construct($config);
	}

	/**
	 * Runs before rendering the view template, echoing HTML to put before the
	 * view template's generated HTML
	 *
	 * @return void
	 */
	protected function preRender()
	{
		$view = $this->input->getCmd('view', 'cpanel');
		$task = $this->getModel()->getState('task', 'browse');

		// Don't load the toolbar on CLI

		if (!FOFPlatform::getInstance()->isCli())
		{
			$toolbar = FOFToolbar::getAnInstance($this->input->getCmd('option', 'com_foobar'), $this->config);
			$toolbar->perms = $this->perms;
			$toolbar->renderToolbar($view, $task, $this->input);
		}

		if (FOFPlatform::getInstance()->isFrontend())
		{
			if ($this->setFrontendPageTitle)
			{
				$this->setPageTitle();
			}
		}

		$renderer = $this->getRenderer();
		$renderer->preRender($view, $task, $this->input, $this->config);
	}

	/**
	 * Runs after rendering the view template, echoing HTML to put after the
	 * view template's generated HTML
	 *
	 * @return  void
	 */
	protected function postRender()
	{
		$view = $this->input->getCmd('view', 'cpanel');
		$task = $this->getModel()->getState('task', 'browse');

		$renderer = $this->getRenderer();

		if ($renderer instanceof FOFRenderAbstract)
		{
			$renderer->postRender($view, $task, $this->input, $this->config);
		}
	}

	public function setPageTitle()
	{
		$document = JFactory::getDocument();
		$app = JFactory::getApplication();
		$menus = $app->getMenu();
		$menu = $menus->getActive();
		$title = null;

		// Get the option and view name
		$option = empty($this->option) ? $this->input->getCmd('option', 'com_foobar') : $this->option;
		$view = empty($this->view) ? $this->input->getCmd('view', $this->getName()) : $this->view;

		// Get the default page title translation key
		$default = empty($this->defaultPageTitle) ? $option . '_TITLE_' . $view : $this->defaultPageTitle;

		$params = $app->getPageParameters($option);

		// Set the default value for page_heading
		if ($menu)
		{
			$params->def('page_heading', $params->get('page_title', $menu->title));
		}
		else
		{
			$params->def('page_heading', JText::_($default));
		}

		// Set the document title
		$title = $params->get('page_title', '');
		$sitename = $app->getCfg('sitename');

		if ($title == $sitename)
		{
			$title = JText::_($default);
		}

		if (empty($title))
		{
			$title = $sitename;
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 1)
		{
			$title = JText::sprintf('JPAGETITLE', $app->getCfg('sitename'), $title);
		}
		elseif ($app->getCfg('sitename_pagetitles', 0) == 2)
		{
			$title = JText::sprintf('JPAGETITLE', $title, $app->getCfg('sitename'));
		}

		$document->setTitle($title);

		// Set meta
		if ($params->get('menu-meta_description'))
		{
			$document->setDescription($params->get('menu-meta_description'));
		}

		if ($params->get('menu-meta_keywords'))
		{
			$document->setMetadata('keywords', $params->get('menu-meta_keywords'));
		}

		if ($params->get('robots'))
		{
			$document->setMetadata('robots', $params->get('robots'));
		}

		return $title;
	}
}
