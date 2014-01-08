<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  view
 * @copyright   Copyright (C) 2010 - 2012 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// Protect from unauthorized access
defined('_JEXEC') or die;

JLoader::import('joomla.application.component.view');

/**
 * FrameworkOnFramework HTML output class. Together with PHP-based view tempalates
 * it will render your data into an HTML representation.
 *
 * @package  FrameworkOnFramework
 * @since    2.1
 */
class FOFViewHtml extends FOFViewRaw
{
	/**
	 * Class constructor
	 *
	 * @param   array  $config  Configuration parameters
	 */
	public function __construct($config = array())
	{
		// Make sure $config is an array
		if (is_object($config))
		{
			$config = (array) $config;
		}
		elseif (!is_array($config))
		{
			$config = array();
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
}
