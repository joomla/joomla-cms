<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.skipto
 *
 * @copyright   (C) 2019 Open Source Matters, Inc. <https://www.joomla.org>
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

		// Are we in a modal?
		if ($this->app->input->get('tmpl', '', 'cmd') === 'component')
		{
			return;
		}

		// Load language file.
		$this->loadLanguage();

		// Add strings for translations in JavaScript and other plugin settings.
		$document->addScriptOptions(
			'skipto-settings',
			[
				'settings' => [
					'skipTo' => [
						'colorTheme'                  => 'joomla',
						'displayOption'               => 'popup',
						'accesskey'                   => '9',
						'customClass'                 => 'joomla',
						'enableActions'               => false,
						'enableHeadingLevelShortcuts' => false,
						'headings'                    => 'h1, h2, h3',
						'landmarks'                   => 'main, nav, search, aside, header, footer, form',
						'accesskeyNotSupported'       => Text::_('PLG_SYSTEM_SKIPTO_ACCESS_KEY_NOT_SUPPORTED'),
						'asideLabel'                  => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_ASIDE'),
						'buttonLabel'                 => Text::_('PLG_SYSTEM_SKIPTO_SKIP_TO'),
						'buttonTitle'                 => Text::_('PLG_SYSTEM_SKIPTO_TITLE'),
						'buttonTitleWithAccesskey'    => Text::_('PLG_SYSTEM_SKIPTO_TITLE_WITH_ACCCESS_KEY'),
						'footerLabel'                 => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_FOOTER'),
						'formLabel'                   => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_FORM'),
						'headerLabel'                 => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_HEADER'),
						'headingImportantGroupLabel'  => Text::_('PLG_SYSTEM_SKIPTO_HEADING_IMP'),
						'headingLevelLabel'           => Text::_('PLG_SYSTEM_SKIPTO_HEADING_LEVEL'),
						'landmarkImportantGroupLabel' => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_IMP'),
						'mainLabel'                   => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_MAIN'),
						'msgNoHeadingsFound'          => Text::_('PLG_SYSTEM_SKIPTO_HEADING_NONE'),
						'msgNoLandmarksFound'         => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_NONE'),
						'navLabel'                    => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_NAV'),
						'searchLabel'                 => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_SEARCH'),
					]
				]
			]
		);

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $document->getWebAssetManager();
		$wa->useStyle('skipto')
			->useScript('skipto')
			->addInlineScript(
				'document.addEventListener(\'DOMContentLoaded\', function() {'
				. 'window.SkipToConfig = Joomla.getOptions(\'skipto-settings\');});',
				[],
				['type' => 'module'],
				['skipto']
			);
	}
}
