<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.skipto
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
 * Skipto plugin to add accessible keyboard navigation to the administrator template.
 *
 * @since  __DEPLOY_VERSION__
 */
class PlgSystemSkipto extends CMSPlugin
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
	 * Add the css and javascript for the skipto navigation menu
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
            // Add strings for translations in Javascript. See comments at end of file
            Factory::getDocument()->addScriptOptions(
                'settings', 
                array (
                    'buttonLabel:'      => 'PLG_SYSTEM_SKIPTO_SKIP_TO',
                    'buttonDivTitle:'   => 'PLG_SYSTEM_SKIPTO_SKIP_TO_KEYBOARD',
                    'menuLabel:'        => 'PLG_SYSTEM_SKIPTO_SKIP_TO_AND_PAGE_OUTLINE',
                    'landmarksLabel:'   => 'PLG_SYSTEM_SKIPTO_SKIP_TO',
                    'headingsLabel:'    => 'PLG_SYSTEM_SKIPTO_PAGE_OUTLINE',
                    'contentLabel:'     =>' PLG_SYSTEM_SKIPTO_CONTENT',
                )
             );
			HTMLHelper::_('script', 'vendor/skipto/js/skipTo.js', ['version' => 'auto', 'relative' => true], ['defer' => true]);
			HTMLHelper::_('stylesheet', 'vendor/skipto/css/SkipTo.css', ['version' => 'auto', 'relative' => true], ['defer' => true]);
		}
	}
}

// <script>
// var SkipToConfig =
// {
// 	"settings": {
// 		"skipTo": {
// 			"headings"     : "h1, h2, h3, h4",
// 			"main"         : "main, [role=main]",
// 			"landmarks"    : "[role=navigation], [role=search]",
// 			"sections"     : "nav",
// 			"ids"          : "#SkipToA1, #SkipToA2",
// 			"customClass"  : "MyClass",
// 			"accesskey"    : "0",
// 			"wrap"         : "true",
// 			"visibility"   : "onfocus",
// 			"attachElement": ".MyCustomClass" // or "attachElement": "#MyCustomId"
// 		}
// 	}
// };
// </script>
