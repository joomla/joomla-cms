<?php
/**
 * @package     Joomla.Plugin
 * @subpackage  System.skipto
 *
 * @copyright   (C) 2020 Open Source Matters, Inc. <https://www.joomla.org>
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
	 * Add the skipto navigation menu.
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

		// Add plugin settings and strings for translations in JavaScript.
		$document->addScriptOptions(
			'skipto-settings',
			[
				'settings' => [
					'skipTo' => [
						// Feature switches
						'enableActions'               => false,
						'enableHeadingLevelShortcuts' => false,

						// Customization of button and menu
						'accesskey'     => '9',
						'displayOption' => 'popup',

						// Button labels and messages
						'buttonLabel'            => Text::_('PLG_SYSTEM_SKIPTO_TITLE'),
						'buttonTooltipAccesskey' => Text::_('PLG_SYSTEM_SKIPTO_ACCESS_KEY'),

						// Menu labels and messages
						'landmarkGroupLabel'  => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK'),
						'headingGroupLabel'   => Text::_('PLG_SYSTEM_SKIPTO_HEADING'),
						'mofnGroupLabel'      => Text::_('PLG_SYSTEM_SKIPTO_HEADING_MOFN'),
						'headingLevelLabel'   => Text::_('PLG_SYSTEM_SKIPTO_HEADING_LEVEL'),
						'mainLabel'           => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_MAIN'),
						'searchLabel'         => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_SEARCH'),
						'navLabel'            => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_NAV'),
						'regionLabel'         => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_REGION'),
						'asideLabel'          => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_ASIDE'),
						'footerLabel'         => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_FOOTER'),
						'headerLabel'         => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_HEADER'),
						'formLabel'           => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_FORM'),
						'msgNoLandmarksFound' => Text::_('PLG_SYSTEM_SKIPTO_LANDMARK_NONE'),
						'msgNoHeadingsFound'  => Text::_('PLG_SYSTEM_SKIPTO_HEADING_NONE'),

						// Selectors for landmark and headings sections
						'headings'  => 'h1, h2, h3',
						'landmarks' => 'main, nav, search, aside, header, footer, form',
					],
				],
			]
		);

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = $document->getWebAssetManager();
		$wa->useScript('skipto');
	}
}
