<?php
/**
 * @package     FrameworkOnFramework
 * @subpackage  render
 * @copyright   Copyright (C) 2010 - 2014 Akeeba Ltd. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('FOF_INCLUDED') or die;

/**
 * Joomla! 3 view renderer class
 *
 * @package  FrameworkOnFramework
 * @since    2.0
 */
class FOFRenderJoomla3 extends FOFRenderStrapper
{
	/**
	 * Public constructor. Determines the priority of this class and if it should be enabled
	 */
	public function __construct()
	{
		$this->priority	 = 55;
		$this->enabled	 = version_compare(JVERSION, '3.0', 'ge');
	}

	/**
	 * Echoes any HTML to show before the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function preRender($view, $task, $input, $config = array())
	{
		$format	 = $input->getCmd('format', 'html');

		if (empty($format))
		{
			$format	 = 'html';
		}

		if ($format != 'html')
		{
			return;
		}

		// Render the submenu and toolbar
		if ($input->getBool('render_toolbar', true))
		{
			$this->renderButtons($view, $task, $input, $config);
			$this->renderLinkbar($view, $task, $input, $config);
		}
	}

	/**
	 * Echoes any HTML to show after the view template
	 *
	 * @param   string    $view    The current view
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input array (request parameters)
	 * @param   array     $config  The view configuration array
	 *
	 * @return  void
	 */
	public function postRender($view, $task, $input, $config = array())
	{
		/*
		We don't need to do anything here, if we are running Joomla3,
		so overwrite the default with all the closing div's

		I added it here because I am not 100% sure if it would break BC
		when doing it in the default strapper
		*/
	}

	/**
	 * Renders the submenu (link bar)
	 *
	 * @param   string    $view    The active view name
	 * @param   string    $task    The current task
	 * @param   FOFInput  $input   The input object
	 * @param   array     $config  Extra configuration variables for the toolbar
	 *
	 * @return  void
	 */
	protected function renderLinkbar($view, $task, $input, $config = array())
	{
		$style = 'joomla';

		if (array_key_exists('linkbar_style', $config))
		{
			$style = $config['linkbar_style'];
		}

		switch ($style)
		{
			case 'joomla':
				$this->renderLinkbar_joomla($view, $task, $input);
				break;

			case 'classic':
			default:
				$this->renderLinkbar_classic($view, $task, $input);
				break;
		}
	}
}
