<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.achecker
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * This plugin adds the ability to perform an accessibility check on a page of your site.
 *
 * @since  __deploy_version__
 */
class PlgSystemAchecker extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    JApplicationCms
	 * @since  __deploy_version__
	 */
	protected $app;

	/**
	 * Load the language file on instantiation.
	 *
	 * @var    boolean
	 * @since  __deploy_version__
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add the javascript and css for the accessibility checker
	 *
	 * @return  void
	 *
	 * @since   __deploy_version__
	 */
	public function onBeforeCompileHead()
	{
		// This plugin is for the site only
		if (!$this->app->isClient('site'))
		{
			return;
		}

		// Thiis plugin is for html only
		$document = $this->app->getDocument();

		if ($document->getType() !== 'html')
		{
			return;
		}

		$document->getWebAssetManager()
			->useScript('html_codesniffer')
			->useStyle('html_codesniffer')
			->addInlineScript(
				'window.addEventListener("load", function() {HTMLCSAuditor.run(\'WCAG2AA\', null);});',
				['name' => 'inline.plg.system.achecker'],
				['type' => 'module'],
				['html_codesniffer']
			);
	}
}
