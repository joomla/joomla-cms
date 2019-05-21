<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.shortcuts
 *
 * @copyright   Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Mousetrap plugin to add keyboard shortcuts to the administrator template.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemShortcuts extends CMSPlugin
{
	/**
	 * If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  4.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Add the javascript for the shortcuts
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		$section         = (int) $this->params->get('section_shortcuts', 2);
		$current_section = 0;

		// Get the document object.
		$document = Factory::getDocument();

		try
		{
			$app = Factory::getApplication();

			if ($app->isClient('administrator'))
			{
				$current_section = 2;
			}
			elseif ($app->isClient('site'))
			{
				$current_section = 1;
			}
		}
		catch (Exception $exc)
		{
			$current_section = 0;
		}

		if (!($current_section & $section))
		{
			return false;
		}

		HTMLHelper::_('script', 'plg_system_shortcuts/shortcuts.js', array('version' => 'auto', 'relative' => true), ['defer' => true]);
	}
}
