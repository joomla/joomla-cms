<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.skipto
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;

/**
 * Skipto plugin to add accessible keyboard navigation to the site and administrator templates.
 *
 * @since  4.0.0
 */
class PlgSystemSkipto extends CMSPlugin
{
	/**
	 * Application object.
	 *
	 * @var    CMSApplicationInterface
	 * @since  4.0.0
	 */
	protected $app;

	/**
	 * Add the CSS and JavaScript for the skipto navigation menu.
	 *
	 * @return  void
	 *
	 * @since   4.0.0
	 */
	public function onAfterDispatch()
	{
		$section = $this->params->get('section', 'administrator');

		if ($section !== 'both' && $this->app->isClient($section) !== true)
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

		// Add strings for translations in JavaScript.
		$document->addScriptOptions(
			'skipto-settings',
			[
				'settings' => [
					'skipTo' => [
						'buttonDivRole'  => 'navigation',
						'buttonDivLabel' => Text::_('PLG_SYSTEM_SKIPTO_SKIP_TO_KEYBOARD'),
						'buttonLabel'    => Text::_('PLG_SYSTEM_SKIPTO_SKIP_TO'),
						'buttonDivTitle' => '',
						'menuLabel'      => Text::_('PLG_SYSTEM_SKIPTO_SKIP_TO_AND_PAGE_OUTLINE'),
						'landmarksLabel' => Text::_('PLG_SYSTEM_SKIPTO_SKIP_TO'),
						'headingsLabel'	 => Text::_('PLG_SYSTEM_SKIPTO_PAGE_OUTLINE'),
						// The following string begins with a space
						'contentLabel'   => ' ' . Text::_('PLG_SYSTEM_SKIPTO_CONTENT'),
					]
				]
			]
		);

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $document->getWebAssetManager();
		$wa->useStyle('skipto')
			->useScript('skipto.dropmenu')
			->useScript('skipto')
			->addInlineScript(
				'document.addEventListener(\'DOMContentLoaded\', function() {'
				. 'window.SkipToConfig = Joomla.getOptions(\'skipto-settings\');'
				. 'window.skipToMenuInit();});',
				[],
				['type' => 'module'],
				['skipto']
			);
	}
}
