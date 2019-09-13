<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.cookieconsent
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
 * Cookie consent plugin to add simple cookie information.
 *
 * @since  4.0.0
 */
class PlgSystemCookieconsent extends CMSPlugin
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
	 * Add the javascript and css for the cookie consent
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		// Get the document object.
		$document = Factory::getDocument();
		if ($document->getType() !== 'html')

		{
			return;
		}

		HTMLHelper::_('script', 'vendor/cookieconsent/cookieconsent.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
		HTMLHelper::_('stylesheet', 'vendor/cookieconsent/cookieconsent.min.css', ['version' => 'auto', 'relative' => true]);

		// Initialise the script and apply configuration
		$document->addScriptDeclaration("document.addEventListener('DOMContentLoaded', function() {
			window.cookieconsent.initialise({
				'palette': {
					'popup': {
					  'background': '#000'
					},
					'button': {
					  'background': '#f1d600'
					}
				  }
			  });
			});
		");
	}
}


