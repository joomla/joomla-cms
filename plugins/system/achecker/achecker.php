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
 * This plugin adds the ability to perform an accessibility check on your site.
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
	 * Add the javascript and css for the accessibility checker
	 *
	 * @return  void
	 *
	 * @since   __deploy_version__
	 */
	public function onBeforeCompileHead()
	{

		if ($this->app->isClient('site'))
		{
			return;
		}

		// Get the document object.
		$document = $this->app->getDocument();

		if ($document->getType() !== 'html')
		{
			return;
		}

		// Load language file.
		$this->loadLanguage();

		// Determine if it is an LTR or RTL language
		$direction = Factory::getLanguage()->isRTL() ? 'right' : 'left';

		$document->getWebAssetManager()
			->useScript('accessibility')
			->addInlineScript(
				'window.addEventListener("load", function() {'
				. 'new Accessibility(Joomla.getOptions("accessibility-options") || {});'
				. '});',
				['name' => 'inline.plg.system.accessibility'],
				['type' => 'module'],
				['accessibility']
			);
	}
}
