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
 * System plugin to add additional accessibility features to the administrator interface.
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
		* HELP Please on commented code
		* Reference  https://ranbuch.github.io/accessibility/
		*/
		// Factory::getDocument()->addScriptOptions(
		// 	var labels = {
		// 		menuTitle: PLG_SYSTEM_ACCESSIBILITY_MENU_TITLE,
		// 		increaseText: PLG_SYSTEM_ACCESSIBILITY_INCREASE_TEXT,
		// 		decreaseText: PLG_SYSTEM_ACCESSIBILITY_DECREASE_TEXT,
		// 		increaseTextSpacing: PLG_SYSTEM_ACCESSIBILITY_INCREASE_SPACING,
		// 		decreaseTextSpacing: PLG_SYSTEM_ACCESSIBILITY_DECREASE_SPACING,
		// 		invertColors: PLG_SYSTEM_ACCESSIBILITY_INVERT_COLORS,
		// 		grayHues: PLG_SYSTEM_ACCESSIBILITY_GREY,
		// 		underlineLinks: PLG_SYSTEM_ACCESSIBILITY_UNDERLINE,
		// 		bigCursor: PLG_SYSTEM_ACCESSIBILITY_CURSOR,
		// 		readingGuide: PLG_SYSTEM_ACCESSIBILITY_READING,
		// 		textToSpeech: PLG_SYSTEM_ACCESSIBILITY_TTS,
		// 		speechToText: PLG_SYSTEM_ACCESSIBILITY_STT

		// 	};
		// 	var options = { labels: labels };
		// 	);

		HTMLHelper::_('script', 'vendor/accessibility/accessibility.min.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
	//	$document->addScriptDeclaration("window.addEventListener('load', function() { new Accessibility(options); }, false);");
		$document->addScriptDeclaration("window.addEventListener('load', function() { new Accessibility(); }, false);");
		}
	}
}
