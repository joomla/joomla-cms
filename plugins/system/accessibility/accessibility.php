<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.accessibility
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
 * System plugin to add additional accessibility features to the administrator interface.".
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemAccessibility extends CMSPlugin
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
	 * Add the javascript for the accessibility menu
	 *
	 * @return  void
	 *
	 * @since   __DEPLOY_VERSION__
	 */
	public function onBeforeCompileHead()
	{
		// Get the document object.
		$document = Factory::getDocument();

		if ($this->app->isClient('administrator'))
		{
		/** 
		* Add strings for translations in Javascript.
	    *	Factory::getDocument()->addScriptOptions(
        * todo    
		*/
		
		HTMLHelper::_('script', 'vendor/accessibility/accessibility.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
		$document->addScriptDeclaration("window.addEventListener('load', function() { new Accessibility(); }, false);");
		}
	}
}